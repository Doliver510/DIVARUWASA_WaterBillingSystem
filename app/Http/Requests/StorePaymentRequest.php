<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePaymentRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Only Admin and Cashier can process payments.
     */
    public function authorize(): bool
    {
        $role = $this->user()?->role?->slug;

        return in_array($role, ['admin', 'cashier']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $bill = $this->route('bill');
        $maxAmount = $bill ? $bill->balance : 0;

        return [
            'amount' => ['required', 'numeric', 'min:0.01', 'max:'.$maxAmount],
            'remarks' => ['nullable', 'string', 'max:500'],
        ];
    }

    /**
     * Get custom error messages for validation.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'amount.required' => 'Payment amount is required.',
            'amount.numeric' => 'Payment amount must be a valid number.',
            'amount.min' => 'Payment amount must be at least ₱0.01.',
            'amount.max' => 'Payment amount cannot exceed the remaining balance.',
        ];
    }
}
