<?php

namespace App\Challenges\BugHunt\Http\Controllers;

use App\Challenges\BugHunt\DataTransferObjects\FundingData;
use App\Challenges\BugHunt\Http\Requests\FundingRequest;
use App\Challenges\BugHunt\Services\WalletFundingService;
use Illuminate\Http\JsonResponse;

class WalletFundingController
{
    public function __construct(private readonly WalletFundingService $service) {}

    public function store(FundingRequest $request): JsonResponse
    {
        $result = $this->service->fund(FundingData::fromArray($request->validated()));

        return response()->json(['funding' => $result->toArray()], 201);
    }
}
