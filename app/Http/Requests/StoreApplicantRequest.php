<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreApplicantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Job selection
            'job_id' => ['required', 'uuid', 'exists:job_listings,id'],
            
            // Step 1: Identitas
            'full_name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => [
                'required', 
                'email', 
                'max:255',
            ],
            'phone' => ['required', 'string', 'max:20'],
            'institution_id' => ['required', 'uuid', 'exists:institutions,id'],
            'referral' => ['nullable', 'string', 'max:255'],
            
            // Step 2: Kompetensi
            'skills' => ['nullable', 'array'],
            'skills.*' => ['string'],
            'other_skills' => ['nullable', 'string', 'max:500'],
            'databases' => ['nullable', 'array'],
            'databases.*' => ['string'],
            'other_databases' => ['nullable', 'string', 'max:500'],
            'operating_systems' => ['nullable', 'array'],
            'operating_systems.*' => ['string'],
            'other_os' => ['nullable', 'string', 'max:500'],
            
            // Step 3: Minat
            'other_interest' => ['nullable', 'string', 'max:500'],
            'demo_required' => ['nullable', 'boolean'],
            'self_description' => ['required', 'string', 'min:20', 'max:5000'],
            'portfolio_url' => ['nullable', 'url', 'max:500'],
            
            // Step 4: Dokumen
            'start_date' => ['required', 'date', 'after_or_equal:today'],
            'end_date' => ['required', 'date', 'after:start_date'],
            'letter_file' => ['required', 'file', 'mimes:pdf', 'max:2048'],
            'id_card_file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:2048'],
            'cv_file' => ['required', 'file', 'mimes:pdf', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'job_id.required' => 'Silakan pilih posisi yang ingin dilamar.',
            'job_id.exists' => 'Posisi yang dipilih tidak valid.',
            
            'full_name.required' => 'Nama lengkap wajib diisi.',
            'full_name.min' => 'Nama lengkap minimal 3 karakter.',
            
            'email.required' => 'Email wajib diisi.',
            'email.email' => 'Format email tidak valid.',
            'email.unique' => 'Email ini sudah digunakan untuk melamar sebelumnya.',
            
            'phone.required' => 'Nomor WhatsApp wajib diisi.',
            
            'institution_id.required' => 'Nama sekolah/universitas wajib dipilih/diisi.',
            'institution_id.exists' => 'Nama sekolah/universitas tidak valid.',
            
            'self_description.required' => 'Deskripsi diri wajib diisi.',
            'self_description.min' => 'Deskripsi diri minimal 20 karakter.',
            
            'start_date.required' => 'Tanggal mulai magang wajib diisi.',
            'start_date.after_or_equal' => 'Tanggal mulai harus hari ini atau setelahnya.',
            'end_date.required' => 'Tanggal berakhir magang wajib diisi.',
            'end_date.after' => 'Tanggal berakhir harus setelah tanggal mulai.',
            
            'letter_file.required' => 'Surat izin magang wajib diunggah.',
            'letter_file.mimes' => 'Surat izin harus dalam format PDF.',
            'letter_file.max' => 'Ukuran surat izin maksimal 2MB.',
            
            'id_card_file.required' => 'Kartu pelajar/KTM wajib diunggah.',
            'id_card_file.mimes' => 'Kartu pelajar harus dalam format PDF, JPG, atau PNG.',
            'id_card_file.max' => 'Ukuran kartu pelajar maksimal 2MB.',
            
            'cv_file.required' => 'CV/Resume wajib diunggah.',
            'cv_file.mimes' => 'CV harus dalam format PDF.',
            'cv_file.max' => 'Ukuran CV maksimal 2MB.',
            
            'portfolio_url.url' => 'Format URL portofolio tidak valid.',
        ];
    }
}
