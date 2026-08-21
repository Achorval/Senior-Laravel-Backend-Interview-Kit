<?php

namespace App\Challenges\AppCompletion\DataTransferObjects;

readonly class TransferData
{
    public function __construct(
        public int $fromWalletId,
        public int $toWalletId,
        public int $amount,
    ) {}

    public static function fromArray(array $input): self
    {
        return new self(
            fromWalletId: (int) $input['from_wallet_id'],
            toWalletId: (int) $input['to_wallet_id'],
            amount: (int) $input['amount'],
        );
    }
}
