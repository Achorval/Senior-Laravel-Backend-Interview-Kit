<?php

namespace App\Challenges\BugHunt\Services;

use App\Challenges\BugHunt\DataTransferObjects\FundingData;
use App\Challenges\BugHunt\DataTransferObjects\FundingResult;
use App\Challenges\BugHunt\Jobs\SendFundingReceipt;
use App\Challenges\BugHunt\Models\WalletFunding;
use App\Challenges\Shared\Exceptions\DomainException;
use App\Challenges\Shared\Models\Wallet;
use Illuminate\Database\UniqueConstraintViolationException;
use Illuminate\Support\Facades\DB;

class WalletFundingService
{
    public function fund(FundingData $input): FundingResult
    {
        if ($input->amount <= 0) {
            throw new DomainException('INVALID_AMOUNT', 'Funding amount must be positive.');
        }

        $wallet = Wallet::find($input->walletId);

        if (! $wallet) {
            throw new DomainException('WALLET_NOT_FOUND', 'Wallet was not found.', 404);
        }

        $existing = WalletFunding::where('reference', $input->reference)->first();

        if ($existing) {
            return FundingResult::fromModels($existing, $wallet);
        }

        try {
            return DB::transaction(function () use ($input, $wallet) {
                $wallet->increment('balance', $input->amount);

                $funding = WalletFunding::create([
                    'wallet_id' => $wallet->id,
                    'amount' => $input->amount,
                    'reference' => $input->reference,
                ]);

                SendFundingReceipt::dispatch($wallet->id, $funding->id);

                return FundingResult::fromModels($funding, $wallet->fresh());
            });
        } catch (UniqueConstraintViolationException) {
            // Another request won the race to insert this reference first.
            // Our increment was rolled back with the transaction; return the
            // winner's result instead of a raw database error.
            $funding = WalletFunding::where('reference', $input->reference)->firstOrFail();

            return FundingResult::fromModels($funding, $wallet->fresh());
        }
    }
}
