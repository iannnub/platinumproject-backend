<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBookingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Public endpoint
    }

    public function rules(): array
    {
        return [
            'bride_names'     => ['required', 'string', 'min:5', 'max:255'],
            'initials'        => ['required', 'string', 'min:1', 'max:10'],
            'event_date'      => ['required', 'date', 'after:' . now()->addDays(7)->format('Y-m-d')],
            'event_type'      => ['required', 'in:Wedding,Birthday,Corporate,Other'],
            'decoration_type' => ['required', 'in:Dalam,Luar'],
            'phone'           => ['required', 'regex:/^(08|\+62)[0-9]{8,13}$/'],
            'address'         => ['required', 'string', 'min:10'],
            'lat'             => ['required', 'numeric', 'between:-90,90'],
            'lng'             => ['required', 'numeric', 'between:-180,180'],
            'package_type'    => ['required', 'string', 'min:1', 'max:100'],
            'dp_amount'       => ['required', 'numeric', 'min:0'],
            'notes'           => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'bride_names.required'     => 'Nama mempelai wajib diisi.',
            'bride_names.min'          => 'Nama minimal 5 karakter.',
            'initials.required'        => 'Initial nama wajib diisi.',
            'initials.max'             => 'Initial maksimal 10 karakter.',
            'event_date.required'      => 'Tanggal acara wajib diisi.',
            'event_date.after'         => 'Tanggal acara minimal 7 hari dari sekarang.',
            'event_type.required'      => 'Jenis acara wajib dipilih.',
            'event_type.in'            => 'Jenis acara tidak valid.',
            'decoration_type.required' => 'Jenis dekorasi akad wajib dipilih.',
            'decoration_type.in'       => 'Jenis dekorasi tidak valid (Dalam/Luar).',
            'phone.required'           => 'Nomor WhatsApp wajib diisi.',
            'phone.regex'              => 'Format nomor tidak valid. Gunakan format 08xx atau +62xx.',
            'address.required'         => 'Alamat wajib diisi.',
            'address.min'              => 'Alamat minimal 10 karakter.',
            'lat.required'             => 'Lokasi peta wajib dipilih.',
            'lat.between'              => 'Koordinat latitude tidak valid.',
            'lng.required'             => 'Lokasi peta wajib dipilih.',
            'lng.between'              => 'Koordinat longitude tidak valid.',
            'package_type.required'    => 'Jenis paket wajib dipilih.',
            'dp_amount.required'       => 'Jumlah DP wajib diisi.',
            'dp_amount.min'            => 'Jumlah DP tidak boleh negatif.',
        ];
    }
}
