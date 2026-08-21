<?php

namespace Database\Factories;

use App\Challenges\Shared\Models\Wallet;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Wallet>
 */
class WalletFactory extends Factory
{
    protected $model = Wallet::class;

    public function definition(): array
    {
        return [
            'owner_reference' => 'user-'.$this->faker->unique()->numberBetween(1, 100000),
            'balance' => 10000,
            'status' => Wallet::STATUS_ACTIVE,
        ];
    }

    public function suspended(): static
    {
        return $this->state(fn () => ['status' => Wallet::STATUS_SUSPENDED]);
    }
}
