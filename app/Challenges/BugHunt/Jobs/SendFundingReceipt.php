<?php

namespace App\Challenges\BugHunt\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendFundingReceipt implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public readonly int $walletId,
        public readonly int $fundingId,
    ) {}

    public function handle(): void
    {
        // In production this would email or push a receipt to the wallet owner.
        // Left as a no-op here so the challenge can run without external services.
    }
}
