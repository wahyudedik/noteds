<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBrandRegistrationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Company Information
            'company_name' => ['required', 'string', 'min:2', 'max:255'],
            'business_type' => ['required', 'string', 'max:255', 'in:E-commerce,SaaS,Agency,Retail,Manufacturing,Service,Influencer,Content Creator,Other'],
            'business_registration_number' => ['nullable', 'string', 'max:100'],
            'description' => ['required', 'string', 'min:20', 'max:1000'],
            
            // Address Information
            'address' => ['required', 'string', 'min:5', 'max:255'],
            'city' => ['required', 'string', 'min:2', 'max:100'],
            'province' => ['required', 'string', 'min:2', 'max:100'],
            'postal_code' => ['required', 'string', 'regex:/^[0-9]{5}$/', 'max:10'],
            
            // Contact Information
            'phone' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{9,13}$/', 'max:20'],
            'email' => ['required', 'email', 'max:255'],
            'website' => ['nullable', 'url', 'max:255'],
            
            // Contact Person
            'contact_person_name' => ['required', 'string', 'min:2', 'max:255'],
            'contact_person_position' => ['required', 'string', 'min:2', 'max:100'],
            'contact_person_email' => ['required', 'email', 'max:255'],
            'contact_person_phone' => ['required', 'string', 'regex:/^(\+62|62|0)[0-9]{9,13}$/', 'max:20'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'company_name.required' => 'Nama perusahaan/brand wajib diisi.',
            'company_name.min' => 'Nama perusahaan/brand minimal 2 karakter.',
            'company_name.max' => 'Nama perusahaan/brand maksimal 255 karakter.',
            
            'business_type.required' => 'Tipe bisnis wajib dipilih.',
            'business_type.in' => 'Tipe bisnis yang dipilih tidak valid.',
            
            'description.required' => 'Deskripsi perusahaan wajib diisi.',
            'description.min' => 'Deskripsi perusahaan minimal 20 karakter.',
            'description.max' => 'Deskripsi perusahaan maksimal 1000 karakter.',
            
            'address.required' => 'Alamat jalan wajib diisi.',
            'address.min' => 'Alamat jalan minimal 5 karakter.',
            
            'city.required' => 'Kota wajib diisi.',
            'city.min' => 'Nama kota minimal 2 karakter.',
            
            'province.required' => 'Provinsi wajib diisi.',
            'province.min' => 'Nama provinsi minimal 2 karakter.',
            
            'postal_code.required' => 'Kode pos wajib diisi.',
            'postal_code.regex' => 'Kode pos harus berupa 5 digit angka.',
            
            'phone.required' => 'Nomor telepon wajib diisi.',
            'phone.regex' => 'Format nomor telepon tidak valid. Gunakan format: +62xxx atau 0xxx.',
            
            'email.required' => 'Email perusahaan wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            
            'website.url' => 'Format website tidak valid. Gunakan format: https://example.com',
            
            'contact_person_name.required' => 'Nama kontak person wajib diisi.',
            'contact_person_name.min' => 'Nama kontak person minimal 2 karakter.',
            
            'contact_person_position.required' => 'Posisi kontak person wajib diisi.',
            'contact_person_position.min' => 'Posisi kontak person minimal 2 karakter.',
            
            'contact_person_email.required' => 'Email kontak person wajib diisi.',
            'contact_person_email.email' => 'Format email kontak person tidak valid.',
            
            'contact_person_phone.required' => 'Nomor telepon kontak person wajib diisi.',
            'contact_person_phone.regex' => 'Format nomor telepon kontak person tidak valid. Gunakan format: +62xxx atau 0xxx.',
        ];
    }
}

