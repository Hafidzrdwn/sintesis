<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

/**
 * StorePresenceRequest
 * 
 * Validates attendance/presence data including GPS coordinates.
 * Implements KSI requirements for GPS integrity validation.
 */
class StorePresenceRequest extends FormRequest
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
            'date' => ['required', 'date', 'before_or_equal:today'],
            'check_in_time' => ['nullable', 'date_format:H:i:s'],
            'check_out_time' => ['nullable', 'date_format:H:i:s', 'after:check_in_time'],
            
            // GPS Coordinates - required for WFO mode
            'check_in_latitude' => ['required_if:attendance_mode,WFO', 'nullable', 'numeric', 'between:-90,90'],
            'check_in_longitude' => ['required_if:attendance_mode,WFO', 'nullable', 'numeric', 'between:-180,180'],
            'check_out_latitude' => ['nullable', 'numeric', 'between:-90,90'],
            'check_out_longitude' => ['nullable', 'numeric', 'between:-180,180'],
            
            // Attendance mode and status
            'attendance_mode' => ['required', 'in:WFO,WFH'],
            'status' => ['nullable', 'in:present,late,absent,leave,sick,permit'],
            
            // Photo proofs
            'check_in_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'], // Max 5MB
            'check_out_photo' => ['nullable', 'image', 'mimes:jpeg,png,jpg', 'max:5120'],
            
            // Notes
            'notes' => ['nullable', 'string', 'max:1000'],
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
            'check_in_latitude' => 'check-in latitude',
            'check_in_longitude' => 'check-in longitude',
            'check_out_latitude' => 'check-out latitude',
            'check_out_longitude' => 'check-out longitude',
            'attendance_mode' => 'attendance mode',
            'check_in_photo' => 'check-in photo',
            'check_out_photo' => 'check-out photo',
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
            'check_in_latitude.required_if' => 'GPS location is required for Work From Office mode.',
            'check_in_longitude.required_if' => 'GPS location is required for Work From Office mode.',
            'check_in_latitude.between' => 'Invalid latitude value. Must be between -90 and 90.',
            'check_in_longitude.between' => 'Invalid longitude value. Must be between -180 and 180.',
            'check_out_time.after' => 'Check-out time must be after check-in time.',
            'date.before_or_equal' => 'Cannot submit attendance for future dates.',
        ];
    }
}
