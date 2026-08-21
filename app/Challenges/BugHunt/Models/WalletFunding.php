<?php

namespace App\Challenges\BugHunt\Models;

use App\Challenges\Shared\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletFunding extends Model
{
    protected $fillable = [
        'wallet_id',
        'amount',
        'reference',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class);
    }
}
