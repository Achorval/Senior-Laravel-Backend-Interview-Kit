<?php

namespace App\Challenges\AppCompletion\Models;

use App\Challenges\Shared\Models\Wallet;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WalletTransfer extends Model
{
    protected $fillable = [
        'from_wallet_id',
        'to_wallet_id',
        'amount',
    ];

    protected $casts = [
        'amount' => 'integer',
    ];

    public function fromWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'from_wallet_id');
    }

    public function toWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'to_wallet_id');
    }
}
