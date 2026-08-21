<?php

namespace App\Challenges\AppCompletion\Services;

use App\Challenges\AppCompletion\DataTransferObjects\TransferData;
use App\Challenges\AppCompletion\DataTransferObjects\TransferResult;
use App\Challenges\AppCompletion\Models\WalletTransfer;
use App\Challenges\Shared\Exceptions\DomainException;
use App\Challenges\Shared\Models\Wallet;
use Illuminate\Support\Facades\DB;

class WalletTransferService
{
    public function transfer(TransferData $input): TransferResult
    {
        if ($input->amount <= 0) {
            throw new DomainException('INVALID_AMOUNT', 'Transfer amount must be a positive integer.');
        }

        if ($input->fromWalletId === $input->toWalletId) {
            throw new DomainException('SAME_WALLET_TRANSFER', 'Source and destination wallet must be different.');
        }

        return DB::transaction(function () use ($input) {
            // Lock rows in a fixed, ID-ascending order (not request order) so two
            // transfers moving money in opposite directions between the same pair
            // of wallets always acquire their locks in the same sequence. That
            // makes one of them wait for the other instead of deadlocking.
            [$firstId, $secondId] = $input->fromWalletId < $input->toWalletId
                ? [$input->fromWalletId, $input->toWalletId]
                : [$input->toWalletId, $input->fromWalletId];

            $first = Wallet::query()->lockForUpdate()->find($firstId);
            $second = Wallet::query()->lockForUpdate()->find($secondId);

            $fromWallet = $input->fromWalletId === $firstId ? $first : $second;
            $toWallet = $input->toWalletId === $firstId ? $first : $second;

            if (! $fromWallet || ! $toWallet) {
                throw new DomainException('WALLET_NOT_FOUND', 'Source or destination wallet does not exist.', 404);
            }

            if (! $fromWallet->isActive()) {
                throw new DomainException('WALLET_NOT_ACTIVE', 'Source wallet is not active.');
            }

            if ($fromWallet->balance < $input->amount) {
                throw new DomainException('INSUFFICIENT_BALANCE', 'Source wallet has insufficient balance.');
            }

            $fromWallet->decrement('balance', $input->amount);
            $toWallet->increment('balance', $input->amount);

            $transfer = WalletTransfer::create([
                'from_wallet_id' => $fromWallet->id,
                'to_wallet_id' => $toWallet->id,
                'amount' => $input->amount,
            ]);

            return TransferResult::fromModels($transfer, $fromWallet->fresh(), $toWallet->fresh());
        });
    }
}
