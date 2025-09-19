<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RecurrenceRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'category_id' => [
                'required',
                Rule::exists('categories','id')->where(fn($q) => $q->where('user_id', $userId)),
            ],
            'title'      => ['required','string','max:255'],
            'amount'     => ['required','numeric','min:0.01','max:100000000'],
            'cadence'    => ['required', Rule::in(['daily','weekly','monthly','quarterly','yearly'])],
            'interval'   => ['nullable','integer','min:1','max:365'],
            'next_run_on'=> ['required','date'],
            'ends_on'    => ['nullable','date','after_or_equal:next_run_on'],
            'notes'      => ['nullable','string','max:2000'],
            'active'     => ['sometimes','boolean'],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'interval' => $this->input('interval', 1),
            'active'   => filter_var($this->input('active', true), FILTER_VALIDATE_BOOLEAN),
        ]);
    }
}
