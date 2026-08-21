<?php

namespace Tests\Feature\AppCompletion;

use App\Challenges\AppCompletion\DataTransferObjects\TransferData;
use App\Challenges\AppCompletion\Models\WalletTransfer;
use App\Challenges\AppCompletion\Services\WalletTransferService;
use App\Challenges\Shared\Exceptions\DomainException;
use App\Challenges\Shared\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTransferServiceTest extends TestCase
{
    use RefreshDatabase;

    private function service(): WalletTransferService
    {
        return app(WalletTransferService::class);
    }

    /**
     * Asserts that the callback throws a DomainException with the given error
     * code, instead of just any DomainException. This matters here because the
     * starter implementation throws a single "not implemented" DomainException
     * for every call, which would make a bare exception-class assertion pass
     * vacuously before the candidate writes any code.
     */
    private function assertRejectedWithCode(string $expectedCode, callable $callback): void
    {
        try {
            $callback();
            $this->fail("Expected a DomainException with code [{$expectedCode}] to be thrown.");
        } catch (DomainException $exception) {
            $this->assertSame($expectedCode, $exception->errorCode);
        }
    }

    public function test_transfers_money_between_two_wallets(): void
    {
        $from = Wallet::factory()->create(['balance' => 10000]);
        $to = Wallet::factory()->create(['balance' => 2500]);

        $result = $this->service()->transfer(new TransferData($from->id, $to->id, 1500));

        $this->assertSame(1500, $result->amount);
        $this->assertSame(8500, $result->fromWalletBalance);
        $this->assertSame(4000, $result->toWalletBalance);
        $this->assertSame(8500, $from->fresh()->balance);
        $this->assertSame(4000, $to->fresh()->balance);
    }

    public function test_rejects_transfer_with_insufficient_balance(): void
    {
        $from = Wallet::factory()->create(['balance' => 1000]);
        $to = Wallet::factory()->create(['balance' => 2500]);

        $this->assertRejectedWithCode(
            'INSUFFICIENT_BALANCE',
            fn () => $this->service()->transfer(new TransferData($from->id, $to->id, 3000))
        );
    }

    public function test_rejects_zero_amount(): void
    {
        $from = Wallet::factory()->create();
        $to = Wallet::factory()->create();

        $this->assertRejectedWithCode(
            'INVALID_AMOUNT',
            fn () => $this->service()->transfer(new TransferData($from->id, $to->id, 0))
        );
    }

    public function test_rejects_negative_amount(): void
    {
        $from = Wallet::factory()->create();
        $to = Wallet::factory()->create();

        $this->assertRejectedWithCode(
            'INVALID_AMOUNT',
            fn () => $this->service()->transfer(new TransferData($from->id, $to->id, -500))
        );
    }

    public function test_rejects_unknown_source_wallet(): void
    {
        $to = Wallet::factory()->create();

        $this->assertRejectedWithCode(
            'WALLET_NOT_FOUND',
            fn () => $this->service()->transfer(new TransferData(999999, $to->id, 1000))
        );
    }

    public function test_rejects_unknown_destination_wallet(): void
    {
        $from = Wallet::factory()->create();

        $this->assertRejectedWithCode(
            'WALLET_NOT_FOUND',
            fn () => $this->service()->transfer(new TransferData($from->id, 999999, 1000))
        );
    }

    public function test_rejects_transfer_to_the_same_wallet(): void
    {
        $wallet = Wallet::factory()->create();

        $this->assertRejectedWithCode(
            'SAME_WALLET_TRANSFER',
            fn () => $this->service()->transfer(new TransferData($wallet->id, $wallet->id, 1000))
        );
    }

    public function test_rejects_transfer_from_a_suspended_wallet(): void
    {
        $from = Wallet::factory()->suspended()->create(['balance' => 10000]);
        $to = Wallet::factory()->create();

        $this->assertRejectedWithCode(
            'WALLET_NOT_ACTIVE',
            fn () => $this->service()->transfer(new TransferData($from->id, $to->id, 1000))
        );
    }

    public function test_records_exactly_one_transfer_for_a_successful_transfer(): void
    {
        $from = Wallet::factory()->create(['balance' => 10000]);
        $to = Wallet::factory()->create(['balance' => 2500]);

        $this->service()->transfer(new TransferData($from->id, $to->id, 1200));

        $this->assertSame(1, WalletTransfer::query()->count());
        $this->assertDatabaseHas('wallet_transfers', [
            'from_wallet_id' => $from->id,
            'to_wallet_id' => $to->id,
            'amount' => 1200,
        ]);
    }
}
