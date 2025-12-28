<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * StoreApplicantRequest
 * 
 * Validates internship application data from public portal.
 */
class StoreApplicantRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Public portal - anyone can apply
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'full_name' => ['required', 'string', 'min:3', 'max:255'],
            'email' => [
                'required', 
                'email', 
                'max:255',
                Rule::unique('applicants', 'email')->whereNull('deleted_at'),
            ],
            'phone' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date', 'before:today'],
            'gender' => ['nullable', 'in:male,female'],
            'address' => ['nullable', 'string', 'max:1000'],
            'university' => ['nullable', 'string', 'max:255'],
            'major' => ['nullable', 'string', 'max:255'],
            'semester' => ['nullable', 'string', 'max:50'],
            'position_applied' => ['required', 'string', 'max:255'],
            'cv_path' => ['required', 'file', 'mimes:pdf,doc,docx', 'max:10240'], // Max 10MB
            'portfolio_url' => ['nullable', 'url', 'max:500'],
            'motivation' => ['nullable', 'string', 'max:5000'],
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'full_name' => 'full name',
            'birth_date' => 'date of birth',
            'position_applied' => 'position',
            'cv_path' => 'CV/Resume',
            'portfolio_url' => 'portfolio URL',
        ];
    }

    /**
     * Get the error messages for the defined validation rules.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'email.unique' => 'This email has already been used for an application.',
            'cv_path.required' => 'Please upload your CV/Resume.',
            'cv_path.mimes' => 'CV must be a PDF or Word document.',
            'cv_path.max' => 'CV file size cannot exceed 10MB.',
            'portfolio_url.url' => 'Please enter a valid portfolio URL.',
        ];
    }
}
