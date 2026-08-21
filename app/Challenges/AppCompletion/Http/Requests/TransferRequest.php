<?php

namespace App\Challenges\AppCompletion\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'from_wallet_id' => ['required', 'integer'],
            'to_wallet_id' => ['required', 'integer'],
            'amount' => ['required', 'integer'],
        ];
    }
}
