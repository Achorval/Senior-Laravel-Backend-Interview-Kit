<?php

namespace App\Challenges\AppCompletion\Services;

use App\Challenges\AppCompletion\DataTransferObjects\TransferData;
use App\Challenges\AppCompletion\DataTransferObjects\TransferResult;
use App\Challenges\AppCompletion\Models\WalletTransfer;
use App\Challenges\Shared\Exceptions\DomainException;
use App\Challenges\Shared\Models\Wallet;

class WalletTransferService
{
    public function transfer(TransferData $input): TransferResult
    {
        // TODO: Implement this method.
        //
        // Expected behavior:
        // - Reject an amount that is not a positive integer.
        // - Reject a transfer where the source and destination wallet are the same.
        // - Reject a transfer where the source or destination wallet does not exist.
        // - Reject a transfer from a wallet whose status is not "active".
        // - Reject a transfer where the source wallet has insufficient balance.
        // - Debit the source wallet and credit the destination wallet atomically.
        // - Lock both wallet rows for the duration of the balance check and update,
        //   in a deterministic order, so two transfers moving money in opposite
        //   directions between the same pair of wallets cannot deadlock or race.
        // - Record a WalletTransfer and return a TransferResult built from it.
        throw new DomainException(
            'TRANSFER_NOT_IMPLEMENTED',
            'Transfer logic has not been implemented yet.',
            500,
        );
    }
}
