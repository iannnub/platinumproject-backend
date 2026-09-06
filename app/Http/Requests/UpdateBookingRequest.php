<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Protected by admin middleware
    }

    public function rules(): array
    {
        return [
            'status'         => ['sometimes', 'in:pending,confirmed,completed,cancelled'],
            'payment_status' => ['sometimes', 'in:pending,dp_paid,paid'],
            'total_amount'   => ['sometimes', 'nullable', 'numeric', 'min:0'],
            'notes'          => ['sometimes', 'nullable', 'string', 'max:1000'],
            'dp_amount'      => ['sometimes', 'numeric', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'status.in'         => 'Status booking tidak valid.',
            'payment_status.in' => 'Status pembayaran tidak valid.',
            'total_amount.min'  => 'Total harga tidak boleh negatif.',
        ];
    }
}
