<?php

namespace App\Challenges\Shared\Models;

use App\Challenges\AppCompletion\Models\WalletTransfer;
use App\Challenges\BugHunt\Models\WalletFunding;
use Database\Factories\WalletFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    public const STATUS_ACTIVE = 'active';

    public const STATUS_SUSPENDED = 'suspended';

    protected $fillable = [
        'owner_reference',
        'balance',
        'status',
    ];

    protected $casts = [
        'balance' => 'integer',
    ];

    public function outgoingTransfers(): HasMany
    {
        return $this->hasMany(WalletTransfer::class, 'from_wallet_id');
    }

    public function incomingTransfers(): HasMany
    {
        return $this->hasMany(WalletTransfer::class, 'to_wallet_id');
    }

    public function fundings(): HasMany
    {
        return $this->hasMany(WalletFunding::class);
    }

    public function isActive(): bool
    {
        return $this->status === self::STATUS_ACTIVE;
    }

    protected static function newFactory(): WalletFactory
    {
        return WalletFactory::new();
    }
}
