<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ExpenseRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy handles object-level auth
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'title' => ['required','string','max:120'],
            'amount' => ['required','numeric','min:0.01','max:100000000'],
            'spent_at' => ['required','date'],
            'category_id' => [
                'required',
                Rule::exists('categories','id')->where(function ($q) use ($userId) {
                    $q->where('user_id', $userId);
                }),
            ],
            'notes' => ['nullable','string','max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Select one of your own categories.',
        ];
    }
}
