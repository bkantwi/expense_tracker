<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Policy protects resources; this request is safe to authorize.
        return true;
    }

    public function rules(): array
    {
        $userId = $this->user()->id;

        // When updating, ignore the current category's id in unique rule
        $categoryId = $this->route('category')?->id;

        return [
            'name' => [
                'required', 'string', 'max:60',
                Rule::unique('categories', 'name')
                    ->where('user_id', $userId)
                    ->ignore($categoryId),
            ],
            'description' => ['nullable', 'string', 'max:500'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'You already have a category with this name.',
        ];
    }
}
