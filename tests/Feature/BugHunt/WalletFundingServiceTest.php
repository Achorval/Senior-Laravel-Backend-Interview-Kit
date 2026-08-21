<?php

namespace Tests\Feature\BugHunt;

use App\Challenges\BugHunt\DataTransferObjects\FundingData;
use App\Challenges\BugHunt\Jobs\SendFundingReceipt;
use App\Challenges\BugHunt\Models\WalletFunding;
use App\Challenges\BugHunt\Services\WalletFundingService;
use App\Challenges\Shared\Exceptions\DomainException;
use App\Challenges\Shared\Models\Wallet;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use RuntimeException;
use Tests\TestCase;
use Throwable;

class WalletFundingServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): WalletFundingService
    {
        return app(WalletFundingService::class);
    }

    public function test_does_not_double_credit_a_duplicate_request(): void
    {
        $wallet = Wallet::factory()->create(['balance' => 1000]);

        $this->service()->fund(new FundingData($wallet->id, 500, 'ref-001'));
        $this->service()->fund(new FundingData($wallet->id, 500, 'ref-001'));

        $this->assertSame(1500, $wallet->fresh()->balance);
        $this->assertSame(1, WalletFunding::query()->count());
    }

    public function test_duplicate_reference_with_an_existing_record_does_not_credit_again(): void
    {
        $wallet = Wallet::factory()->create(['balance' => 1000]);

        WalletFunding::create([
            'wallet_id' => $wallet->id,
            'amount' => 500,
            'reference' => 'ref-recorded',
        ]);

        $result = $this->service()->fund(new FundingData($wallet->id, 500, 'ref-recorded'));

        $this->assertSame(1000, $wallet->fresh()->balance);
        $this->assertSame(1, WalletFunding::query()->count());
        $this->assertSame('ref-recorded', $result->reference);
    }

    public function test_rejects_a_zero_funding_amount(): void
    {
        $wallet = Wallet::factory()->create();

        $this->expectException(DomainException::class);

        $this->service()->fund(new FundingData($wallet->id, 0, 'ref-invalid'));
    }

    public function test_rejects_a_negative_funding_amount(): void
    {
        $wallet = Wallet::factory()->create();

        $this->expectException(DomainException::class);

        $this->service()->fund(new FundingData($wallet->id, -500, 'ref-negative'));
    }

    public function test_rejects_an_unknown_wallet(): void
    {
        $this->expectException(DomainException::class);

        $this->service()->fund(new FundingData(999999, 500, 'ref-missing'));
    }

    public function test_updates_the_balance_once_for_a_successful_request(): void
    {
        $wallet = Wallet::factory()->create(['balance' => 1000]);

        $result = $this->service()->fund(new FundingData($wallet->id, 700, 'ref-002'));

        $this->assertSame(1700, $result->balance);
        $this->assertSame(1700, $wallet->fresh()->balance);
    }

    public function test_creates_exactly_one_funding_record_for_a_successful_request(): void
    {
        $wallet = Wallet::factory()->create(['balance' => 1000]);

        $this->service()->fund(new FundingData($wallet->id, 300, 'ref-003'));

        $this->assertSame(1, WalletFunding::query()->count());
        $this->assertDatabaseHas('wallet_fundings', [
            'wallet_id' => $wallet->id,
            'amount' => 300,
            'reference' => 'ref-003',
        ]);
    }

    public function test_dispatches_exactly_one_receipt_job_for_a_duplicate_request(): void
    {
        Bus::fake();

        $wallet = Wallet::factory()->create(['balance' => 1000]);

        $this->service()->fund(new FundingData($wallet->id, 500, 'ref-004'));
        $this->service()->fund(new FundingData($wallet->id, 500, 'ref-004'));

        Bus::assertDispatchedTimes(SendFundingReceipt::class, 1);
    }

    public function test_enforces_a_unique_database_constraint_on_reference(): void
    {
        $wallet = Wallet::factory()->create();

        WalletFunding::create([
            'wallet_id' => $wallet->id,
            'amount' => 500,
            'reference' => 'dup-ref',
        ]);

        $this->expectException(QueryException::class);

        WalletFunding::create([
            'wallet_id' => $wallet->id,
            'amount' => 500,
            'reference' => 'dup-ref',
        ]);
    }

    public function test_rolls_back_the_wallet_balance_if_the_funding_record_fails_to_persist(): void
    {
        $wallet = Wallet::factory()->create(['balance' => 1000]);

        WalletFunding::creating(function (WalletFunding $funding) {
            if ($funding->reference === 'force-failure') {
                throw new RuntimeException('Simulated persistence failure.');
            }
        });

        try {
            $this->service()->fund(new FundingData($wallet->id, 500, 'force-failure'));
            $this->fail('Expected an exception to be thrown.');
        } catch (Throwable) {
            // expected
        }

        $this->assertSame(1000, $wallet->fresh()->balance);
        $this->assertSame(0, WalletFunding::query()->count());
    }
}
