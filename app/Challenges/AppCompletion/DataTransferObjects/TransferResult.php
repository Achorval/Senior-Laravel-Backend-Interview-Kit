<?php

namespace App\Challenges\AppCompletion\DataTransferObjects;

use App\Challenges\AppCompletion\Models\WalletTransfer;
use App\Challenges\Shared\Models\Wallet;

readonly class TransferResult
{
    public function __construct(
        public int $transferId,
        public int $fromWalletId,
        public int $toWalletId,
        public int $amount,
        public int $fromWalletBalance,
        public int $toWalletBalance,
    ) {}

    public static function fromModels(WalletTransfer $transfer, Wallet $fromWallet, Wallet $toWallet): self
    {
        return new self(
            transferId: $transfer->id,
            fromWalletId: $transfer->from_wallet_id,
            toWalletId: $transfer->to_wallet_id,
            amount: $transfer->amount,
            fromWalletBalance: $fromWallet->balance,
            toWalletBalance: $toWallet->balance,
        );
    }

    public function toArray(): array
    {
        return [
            'transfer_id' => $this->transferId,
            'from_wallet_id' => $this->fromWalletId,
            'to_wallet_id' => $this->toWalletId,
            'amount' => $this->amount,
            'balances' => [
                'from_wallet' => $this->fromWalletBalance,
                'to_wallet' => $this->toWalletBalance,
            ],
        ];
    }
}
