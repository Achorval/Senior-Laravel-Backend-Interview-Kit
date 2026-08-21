<?php

use App\Challenges\AppCompletion\Http\Controllers\WalletTransferController;
use App\Challenges\BugHunt\Http\Controllers\WalletFundingController;
use Illuminate\Support\Facades\Route;

Route::get('/wallets/{walletId}', [WalletTransferController::class, 'show']);
Route::post('/transfers', [WalletTransferController::class, 'store']);

Route::post('/fundings', [WalletFundingController::class, 'store']);
