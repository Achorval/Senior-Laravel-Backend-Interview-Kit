<?php

namespace App\Challenges\AppCompletion\Http\Controllers;

use App\Challenges\AppCompletion\DataTransferObjects\TransferData;
use App\Challenges\AppCompletion\Http\Requests\TransferRequest;
use App\Challenges\AppCompletion\Services\WalletTransferService;
use App\Challenges\Shared\Exceptions\DomainException;
use App\Challenges\Shared\Models\Wallet;
use Illuminate\Http\JsonResponse;

class WalletTransferController
{
    public function __construct(private readonly WalletTransferService $service) {}

    public function show(int $walletId): JsonResponse
    {
        $wallet = Wallet::find($walletId);

        if (! $wallet) {
            throw new DomainException('WALLET_NOT_FOUND', 'Wallet was not found.', 404);
        }

        return response()->json(['wallet' => $wallet]);
    }

    public function store(TransferRequest $request): JsonResponse
    {
        $result = $this->service->transfer(TransferData::fromArray($request->validated()));

        return response()->json(['transfer' => $result->toArray()], 201);
    }
}
