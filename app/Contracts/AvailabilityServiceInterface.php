<?php

namespace App\Contracts;

use Illuminate\Support\Collection;

interface AvailabilityServiceInterface
{
    /**
     * Get available time slots for a specific date.
     */
    public function getAvailableSlots(string $date, ?int $serviceId = null): Collection;

    /**
     * Check if a specific time slot is available for booking.
     */
    public function isSlotAvailable(string $date, string $time, int $serviceId): bool;
}
