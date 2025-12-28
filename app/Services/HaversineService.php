<?php

namespace App\Services;

/**
 * HaversineService
 * 
 * Calculate the distance between two points on Earth using the Haversine formula.
 * Used for GPS-based attendance validation (KSI - GPS Integrity).
 */
class HaversineService
{
    /**
     * Earth's radius in meters
     */
    private const EARTH_RADIUS_METERS = 6371000;

    /**
     * Earth's radius in kilometers
     */
    private const EARTH_RADIUS_KM = 6371;

    /**
     * Office location coordinates (Inosoft HQ)
     * This should be configurable in production
     */
    private float $officeLatitude;
    private float $officeLongitude;
    private float $maxAllowedRadius;

    public function __construct()
    {
        // Load from config or use defaults
        $this->officeLatitude = config('attendance.office_latitude', -6.2088);
        $this->officeLongitude = config('attendance.office_longitude', 106.8456);
        $this->maxAllowedRadius = config('attendance.max_radius_meters', 100);
    }

    /**
     * Calculate distance between two points using Haversine formula
     *
     * @param float $lat1 Latitude of point 1 in degrees
     * @param float $lon1 Longitude of point 1 in degrees
     * @param float $lat2 Latitude of point 2 in degrees
     * @param float $lon2 Longitude of point 2 in degrees
     * @return float Distance in meters
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        // Convert degrees to radians
        $lat1Rad = deg2rad($lat1);
        $lat2Rad = deg2rad($lat2);
        $deltaLat = deg2rad($lat2 - $lat1);
        $deltaLon = deg2rad($lon2 - $lon1);

        // Haversine formula
        $a = sin($deltaLat / 2) * sin($deltaLat / 2) +
             cos($lat1Rad) * cos($lat2Rad) *
             sin($deltaLon / 2) * sin($deltaLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return self::EARTH_RADIUS_METERS * $c;
    }

    /**
     * Calculate distance from office
     *
     * @param float $latitude User's current latitude
     * @param float $longitude User's current longitude
     * @return float Distance from office in meters
     */
    public function calculateDistanceFromOffice(float $latitude, float $longitude): float
    {
        return $this->calculateDistance(
            $this->officeLatitude,
            $this->officeLongitude,
            $latitude,
            $longitude
        );
    }

    /**
     * Check if location is within allowed radius
     *
     * @param float $latitude User's current latitude
     * @param float $longitude User's current longitude
     * @param float|null $maxRadius Maximum allowed radius in meters (optional)
     * @return bool True if within allowed radius
     */
    public function isWithinAllowedRadius(float $latitude, float $longitude, ?float $maxRadius = null): bool
    {
        $distance = $this->calculateDistanceFromOffice($latitude, $longitude);
        $allowedRadius = $maxRadius ?? $this->maxAllowedRadius;

        return $distance <= $allowedRadius;
    }

    /**
     * Validate GPS coordinates for check-in
     * Returns validation result with distance and status
     *
     * @param float $latitude User's current latitude
     * @param float $longitude User's current longitude
     * @param string $mode Attendance mode (WFO/WFH)
     * @return array Validation result
     */
    public function validateCheckIn(float $latitude, float $longitude, string $mode = 'WFO'): array
    {
        $distance = $this->calculateDistanceFromOffice($latitude, $longitude);
        
        // For WFH mode, skip location validation
        if ($mode === 'WFH') {
            return [
                'valid' => true,
                'distance_meters' => $distance,
                'message' => 'Work From Home mode - location validation skipped',
                'mode' => 'WFH',
            ];
        }

        $isValid = $distance <= $this->maxAllowedRadius;

        return [
            'valid' => $isValid,
            'distance_meters' => round($distance, 2),
            'max_allowed_meters' => $this->maxAllowedRadius,
            'message' => $isValid 
                ? 'Location verified - within office radius'
                : 'Location invalid - outside allowed radius',
            'mode' => 'WFO',
        ];
    }

    /**
     * Set custom office location
     *
     * @param float $latitude Office latitude
     * @param float $longitude Office longitude
     * @return self
     */
    public function setOfficeLocation(float $latitude, float $longitude): self
    {
        $this->officeLatitude = $latitude;
        $this->officeLongitude = $longitude;

        return $this;
    }

    /**
     * Set maximum allowed radius
     *
     * @param float $radiusMeters Maximum radius in meters
     * @return self
     */
    public function setMaxAllowedRadius(float $radiusMeters): self
    {
        $this->maxAllowedRadius = $radiusMeters;

        return $this;
    }

    /**
     * Get current office coordinates
     *
     * @return array Office coordinates
     */
    public function getOfficeCoordinates(): array
    {
        return [
            'latitude' => $this->officeLatitude,
            'longitude' => $this->officeLongitude,
        ];
    }

    /**
     * Get maximum allowed radius
     *
     * @return float Maximum radius in meters
     */
    public function getMaxAllowedRadius(): float
    {
        return $this->maxAllowedRadius;
    }

    /**
     * Format distance for display
     *
     * @param float $distanceMeters Distance in meters
     * @return string Formatted distance
     */
    public function formatDistance(float $distanceMeters): string
    {
        if ($distanceMeters < 1000) {
            return round($distanceMeters) . ' m';
        }

        return round($distanceMeters / 1000, 2) . ' km';
    }
}
