<?php

namespace App\Challenges\BugHunt\DataTransferObjects;

use App\Challenges\BugHunt\Models\WalletFunding;
use App\Challenges\Shared\Models\Wallet;

readonly class FundingResult
{
    public function __construct(
        public int $fundingId,
        public int $walletId,
        public int $amount,
        public string $reference,
        public int $balance,
    ) {}

    public static function fromModels(WalletFunding $funding, Wallet $wallet): self
    {
        return new self(
            fundingId: $funding->id,
            walletId: $wallet->id,
            amount: $funding->amount,
            reference: $funding->reference,
            balance: $wallet->balance,
        );
    }

    public function toArray(): array
    {
        return [
            'funding_id' => $this->fundingId,
            'wallet_id' => $this->walletId,
            'amount' => $this->amount,
            'reference' => $this->reference,
            'balance' => $this->balance,
        ];
    }
}
