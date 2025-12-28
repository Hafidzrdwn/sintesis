<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Attendance Configuration
    |--------------------------------------------------------------------------
    |
    | Configuration for GPS-based attendance validation.
    | Used by HaversineService for location verification.
    |
    */

    /*
    |--------------------------------------------------------------------------
    | Office Location
    |--------------------------------------------------------------------------
    |
    | The GPS coordinates of the office location.
    | These are used to calculate distance for WFO attendance.
    |
    */
    'office_latitude' => env('OFFICE_LATITUDE', -6.2088),
    'office_longitude' => env('OFFICE_LONGITUDE', 106.8456),

    /*
    |--------------------------------------------------------------------------
    | Maximum Allowed Radius
    |--------------------------------------------------------------------------
    |
    | The maximum distance (in meters) an intern can be from the office
    | and still have their attendance validated for WFO mode.
    | Default: 100 meters
    |
    */
    'max_radius_meters' => env('ATTENDANCE_MAX_RADIUS', 100),

    /*
    |--------------------------------------------------------------------------
    | Working Hours
    |--------------------------------------------------------------------------
    |
    | Official working hours for the company.
    | Used to determine late arrivals.
    |
    */
    'work_start_time' => env('WORK_START_TIME', '08:00'),
    'work_end_time' => env('WORK_END_TIME', '17:00'),
    'late_tolerance_minutes' => env('LATE_TOLERANCE_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Photo Settings
    |--------------------------------------------------------------------------
    |
    | Configuration for attendance photo uploads.
    |
    */
    'photo_max_size_kb' => 2048, // 2MB
    'photo_allowed_mimes' => ['jpeg', 'png', 'jpg'],
    'photo_storage_path' => 'attendance-photos',

    /*
    |--------------------------------------------------------------------------
    | Attendance Modes
    |--------------------------------------------------------------------------
    |
    | Available attendance modes.
    |
    */
    'modes' => [
        'WFO' => 'Work From Office',
        'WFH' => 'Work From Home',
    ],

    /*
    |--------------------------------------------------------------------------
    | Status Types
    |--------------------------------------------------------------------------
    |
    | Available attendance status types.
    |
    */
    'statuses' => [
        'present' => 'Present',
        'late' => 'Late',
        'absent' => 'Absent',
        'leave' => 'Leave',
        'sick' => 'Sick',
        'permit' => 'Permit',
    ],

];
