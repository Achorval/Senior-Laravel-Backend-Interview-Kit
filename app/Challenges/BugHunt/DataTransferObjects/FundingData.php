<?php

namespace App\Challenges\BugHunt\DataTransferObjects;

readonly class FundingData
{
    public function __construct(
        public int $walletId,
        public int $amount,
        public string $reference,
    ) {}

    public static function fromArray(array $input): self
    {
        return new self(
            walletId: (int) $input['wallet_id'],
            amount: (int) $input['amount'],
            reference: (string) $input['reference'],
        );
    }
}
