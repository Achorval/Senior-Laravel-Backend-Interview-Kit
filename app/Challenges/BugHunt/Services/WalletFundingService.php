<?php

namespace App\Challenges\BugHunt\Services;

use App\Challenges\BugHunt\DataTransferObjects\FundingData;
use App\Challenges\BugHunt\DataTransferObjects\FundingResult;
use App\Challenges\BugHunt\Jobs\SendFundingReceipt;
use App\Challenges\BugHunt\Models\WalletFunding;
use App\Challenges\Shared\Exceptions\DomainException;
use App\Challenges\Shared\Models\Wallet;

class WalletFundingService
{
    public function fund(FundingData $input): FundingResult
    {
        $wallet = Wallet::find($input->walletId);

        if (! $wallet) {
            throw new DomainException('WALLET_NOT_FOUND', 'Wallet was not found.', 404);
        }

        if ($input->amount < 0) {
            throw new DomainException('INVALID_AMOUNT', 'Funding amount must be positive.');
        }

        $wallet->increment('balance', $input->amount);

        $existing = WalletFunding::where('reference', $input->reference)->first();

        if ($existing) {
            return FundingResult::fromModels($existing, $wallet->fresh());
        }

        $funding = WalletFunding::create([
            'wallet_id' => $wallet->id,
            'amount' => $input->amount,
            'reference' => $input->reference,
        ]);

        SendFundingReceipt::dispatch($wallet->id, $funding->id);

        return FundingResult::fromModels($funding, $wallet->fresh());
    }
}
