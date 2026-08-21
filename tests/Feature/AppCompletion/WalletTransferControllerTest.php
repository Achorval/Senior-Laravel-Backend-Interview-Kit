<?php

namespace Tests\Feature\AppCompletion;

use App\Challenges\Shared\Models\Wallet;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletTransferControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_returns_a_clean_transfer_response(): void
    {
        $from = Wallet::factory()->create(['balance' => 10000]);
        $to = Wallet::factory()->create(['balance' => 2500]);

        $response = $this->postJson('/api/transfers', [
            'from_wallet_id' => $from->id,
            'to_wallet_id' => $to->id,
            'amount' => 1500,
        ]);

        $response->assertStatus(201);
        $response->assertJson([
            'transfer' => [
                'from_wallet_id' => $from->id,
                'to_wallet_id' => $to->id,
                'amount' => 1500,
                'balances' => [
                    'from_wallet' => 8500,
                    'to_wallet' => 4000,
                ],
            ],
        ]);
    }

    public function test_returns_a_meaningful_error_response(): void
    {
        $from = Wallet::factory()->create();
        $to = Wallet::factory()->create();

        $response = $this->postJson('/api/transfers', [
            'from_wallet_id' => $from->id,
            'to_wallet_id' => $to->id,
            'amount' => 0,
        ]);

        $response->assertStatus(400);
        $response->assertJson([
            'error' => ['code' => 'INVALID_AMOUNT'],
        ]);
    }
}
