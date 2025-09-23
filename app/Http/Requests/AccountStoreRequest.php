<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AccountStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'name' => ['required','string','max:100'],
            'type' => ['required','in:cash,momo,bank,card'],
            'currency' => ['required','string','size:3'],
            'starting_balance' => ['nullable','numeric','min:0','max:100000000'],
        ];
    }
}
