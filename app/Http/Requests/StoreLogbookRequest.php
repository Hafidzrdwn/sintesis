<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StoreLogbookRequest
 * 
 * Validates logbook/daily activity entries.
 */
class StoreLogbookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check();
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'task_id' => ['nullable', 'uuid', 'exists:tasks,id'],
            'internship_id' => ['nullable', 'uuid', 'exists:internships,id'],
            'date' => ['required', 'date', 'before_or_equal:today'],
            'activity' => ['required', 'string', 'min:5', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'duration_hours' => ['required', 'numeric', 'min:0.25', 'max:24'],
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
            'task_id' => 'task',
            'internship_id' => 'internship',
            'duration_hours' => 'duration',
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
            'activity.min' => 'Activity description must be at least 5 characters.',
            'date.before_or_equal' => 'Cannot log activities for future dates.',
            'duration_hours.min' => 'Duration must be at least 15 minutes (0.25 hours).',
            'duration_hours.max' => 'Duration cannot exceed 24 hours.',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set user_id from authenticated user
        $this->merge([
            'user_id' => auth()->id(),
        ]);

        // Set default status if not provided
        if (!$this->has('status')) {
            $this->merge([
                'status' => 'draft',
            ]);
        }
    }
}
