<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Carbon;

class BudgetRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Policy handles object-level auth
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        return [
            'category_id' => [
                'required',
                Rule::exists('categories','id')->where(fn($q) => $q->where('user_id', $userId)),
            ],
            // Browser posts "YYYY-MM" from <input type="month">
            'period' => ['required', 'date_format:Y-m'],
            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000000'],
        ];
    }

    // ✅ Normalize AFTER validation succeeds so the Y-m rule passes
    protected function passedValidation(): void
    {
        $this->merge([
            'period' => Carbon::createFromFormat('Y-m', $this->input('period'))
                ->startOfMonth()
                ->toDateString(), // e.g., "2025-09-01"
        ]);
    }

    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            $normalized = null;
            if (preg_match('/^\d{4}-\d{2}$/', (string) $this->input('period'))) {
                $normalized = \Illuminate\Support\Carbon::createFromFormat('Y-m', $this->input('period'))
                    ->startOfMonth()->toDateString();
            }

            $exists = \App\Models\Budget::where('user_id', $this->user()->id)
                ->where('category_id', $this->input('category_id'))
                ->when($normalized, fn($q) => $q->whereDate('period', $normalized)) // << change
                ->when($this->route('budget'), fn($q) => $q->where('id', '!=', $this->route('budget')->id))
                ->exists();

            if ($exists) {
                $v->errors()->add('period', 'You already set a budget for this category and month.');
            }
        });
    }
}
