<?php

namespace App\Http\Requests;

use App\Models\Budget;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Carbon;
use Illuminate\Validation\Rule;

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
                Rule::exists('categories', 'id')->where(fn ($q) => $q->where('user_id', $userId)),
            ],

            'account_id' => [
                'required',
                Rule::exists('accounts', 'id')->where(fn ($q) => $q->where('user_id', $userId)),
                ],

            // Browser posts "YYYY-MM" from <input type="month">
            'period' => ['required', 'date_format:Y-m'],

            'amount' => ['required', 'numeric', 'min:0.01', 'max:100000000'],

            // ---- Alert settings (optional) ----
            // Keep these optional; when 'alerts_enabled' is on ("1"), they are required.
            'alerts_enabled' => ['sometimes', 'boolean'],
            'warn_threshold' => ['required_if:alerts_enabled,1', 'integer', 'min:1', 'max:300'],
            'at_threshold'   => ['required_if:alerts_enabled,1', 'integer', 'min:1', 'max:300'],
            'over_threshold' => ['required_if:alerts_enabled,1', 'integer', 'min:1', 'max:300'],
        ];
    }

    /**
     * Normalize AFTER validation succeeds so the Y-m rule passes:
     * convert period to first day of month (YYYY-MM-01) for DB storage/uniqueness.
     */
    protected function passedValidation(): void
    {
        $this->merge([
            'period' => Carbon::createFromFormat('Y-m', $this->input('period'))
                ->startOfMonth()
                ->toDateString(), // e.g., "2025-09-01"
        ]);
    }

    /**
     * Extra validations:
     * - Ensure (user_id, category_id, period) is unique (using normalized YYYY-MM-01).
     * - Ensure thresholds are in ascending order: warn ≤ at ≤ over (when provided/enabled).
     */
    public function withValidator($validator)
    {
        $validator->after(function ($v) {
            // Compute the normalized date from the raw 'period' (still "Y-m" at this phase)
            $normalized = null;
            if (preg_match('/^\d{4}-\d{2}$/', (string) $this->input('period'))) {
                $normalized = Carbon::createFromFormat('Y-m', $this->input('period'))
                    ->startOfMonth()
                    ->toDateString();
            }

            // Uniqueness (skip current model on update)
            $exists = Budget::where('user_id', $this->user()->id)
                ->where('category_id', $this->input('category_id'))
                ->where('account_id', $this->input('account_id'))
                ->when($normalized, fn ($q) => $q->whereDate('period', $normalized))
                ->when($this->route('budget'), fn ($q) => $q->where('id', '!=', $this->route('budget')->id))
                ->exists();

            if ($exists) {
                $v->errors()->add('period', 'You already set a budget for this category and month.');
            }

            // Threshold ordering (only if alerts are enabled or thresholds are present)
            $alertsEnabledInput = $this->input('alerts_enabled', null); // may be "1", "on", true, or null
            $hasAnyThreshold = $this->filled('warn_threshold') || $this->filled('at_threshold') || $this->filled('over_threshold');

            $isEnabled = in_array($alertsEnabledInput, [1, '1', true, 'on'], true);

            if ($isEnabled || $hasAnyThreshold) {
                $w = (int) $this->input('warn_threshold', 80);
                $a = (int) $this->input('at_threshold', 100);
                $o = (int) $this->input('over_threshold', 110);

                if (!($w <= $a && $a <= $o)) {
                    $v->errors()->add('warn_threshold', 'Thresholds must be in ascending order (warn ≤ at ≤ over).');
                }
            }
        });
    }
}
