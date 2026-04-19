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

    // app/Http/Requests/ExpenseRequest.php
    public function rules(): array
    {
        $uid = $this->user()->id;

        return [
            'category_id' => ['required', Rule::exists('categories','id')->where(fn($q)=>$q->where('user_id',$uid))],
            'account_id'  => ['required', Rule::exists('accounts','id')->where(fn($q)=>$q->where('user_id',$uid)->where('archived', false))],
            'title'       => ['required','string','max:255'],
            'amount'      => ['required','numeric','min:0.01','max:100000000'],
            'spent_at'    => ['required','date'],
            'notes'       => ['nullable','string','max:2000'],

            // recurring fields ...
            'make_recurring'         => ['sometimes','boolean'],
            'recurrence_cadence'     => ['required_if:make_recurring,1', Rule::in(['daily','weekly','monthly','quarterly','yearly'])],
            'recurrence_interval'    => ['required_if:make_recurring,1','integer','min:1','max:365'],
            'recurrence_next_run_on' => ['required_if:make_recurring,1','date'],

            // attachments
            'files.*' => ['nullable','file','max:5120','mimes:jpg,jpeg,png,webp,pdf'], // 5MB
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'make_recurring' => filter_var($this->input('make_recurring', false), FILTER_VALIDATE_BOOLEAN),
        ]);
    }

    public function messages(): array
    {
        return [
            'category_id.exists' => 'Select one of your own categories.',
            'account_id.exists'  => 'Select one of your own active accounts.',
        ];
    }
}
