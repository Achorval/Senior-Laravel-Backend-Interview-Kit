<?php

namespace App\Challenges\BugHunt\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class FundingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'wallet_id' => ['required', 'integer'],
            'amount' => ['required', 'integer'],
            'reference' => ['required', 'string'],
        ];
    }
}
