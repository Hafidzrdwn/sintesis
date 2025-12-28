<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreTaskRequest
 * 
 * Validates task creation data for the PM module.
 */
class StoreTaskRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Only mentors and admins can create tasks
        return auth()->check() && in_array(auth()->user()->role, ['admin', 'mentor']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'min:3', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_date' => ['required', 'date', 'after_or_equal:today'],
            'priority' => ['required', 'in:low,medium,high,urgent'],
            'status' => ['nullable', 'in:pending,in_progress,review,completed,cancelled'],
            'intern_id' => ['required', 'uuid', 'exists:users,id'],
            'internship_id' => ['nullable', 'uuid', 'exists:internships,id'],
            'estimated_hours' => ['nullable', 'integer', 'min:1', 'max:1000'],
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
            'intern_id' => 'intern',
            'internship_id' => 'internship',
            'due_date' => 'due date',
            'estimated_hours' => 'estimated hours',
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
            'title.min' => 'Task title must be at least 3 characters.',
            'due_date.after_or_equal' => 'Due date cannot be in the past.',
            'intern_id.exists' => 'Selected intern does not exist.',
            'intern_id.uuid' => 'Invalid intern ID format.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set mentor_id from authenticated user if mentor
        if (auth()->user()->role === 'mentor') {
            $this->merge([
                'mentor_id' => auth()->id(),
            ]);
        }

        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge([
                'status' => 'pending',
            ]);
        }
    }
}
