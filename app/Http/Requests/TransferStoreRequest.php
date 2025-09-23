<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TransferStoreRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'from_account_id' => ['required','integer','exists:accounts,id'],
            'to_account_id'   => ['required','integer','different:from_account_id','exists:accounts,id'],
            'transferred_at'  => ['required','date'],
            'amount'          => ['required','numeric','min:0.01','max:100000000'],
            'memo'            => ['nullable','string','max:255'],
        ];
    }
}
