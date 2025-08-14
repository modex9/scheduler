<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\WorkingHour;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class AvailabilityService
{
    /**
     * Get available time slots for a specific date.
     */
    public function getAvailableSlots(string $date, ?int $serviceId = null): Collection
    {
        $dateCarbon = Carbon::parse($date);
        $dayOfWeek = $dateCarbon->dayOfWeek;

        // Get working hours for this day
        $workingHour = WorkingHour::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->first();

        if (!$workingHour) {
            return collect();
        }

        // Get services (filtered by serviceId if provided)
        $services = $serviceId
            ? Service::where('id', $serviceId)->where('is_active', true)->get()
            : Service::where('is_active', true)->get();

        if ($services->isEmpty()) {
            return collect();
        }

        // Get existing appointments for this date
        $existingAppointments = Appointment::forDate($date)->confirmed()->get();

        // Generate time slots
        $slots = $this->generateTimeSlots($workingHour, $services);

        // Filter out booked slots
        return $this->filterBookedSlots($slots, $existingAppointments);
    }

    /**
     * Generate time slots based on working hours and service durations.
     */
    private function generateTimeSlots(WorkingHour $workingHour, Collection $services): Collection
    {
        $slots = collect();
        $startTime = Carbon::parse($workingHour->start_time);
        $endTime = Carbon::parse($workingHour->end_time);

        // Use 30-minute intervals for slot generation
        $slotInterval = 30;

        while ($startTime->copy()->addMinutes($slotInterval) <= $endTime) {
            $slotEnd = $startTime->copy()->addMinutes($slotInterval);

            // Check if any service can fit in this slot
            foreach ($services as $service) {
                if ($this->canServiceFitInSlot($service, $startTime, $slotEnd, $endTime)) {
                    $slots->push([
                        'time' => $startTime->format('H:i'),
                        'service_id' => $service->id,
                        'service_name' => $service->name,
                        'duration_minutes' => $service->duration_minutes,
                        'price' => $service->price,
                    ]);
                }
            }

            $startTime->addMinutes($slotInterval);
        }

        return $slots;
    }

    /**
     * Check if a service can fit in a given time slot.
     */
    private function canServiceFitInSlot(Service $service, Carbon $slotStart, Carbon $slotEnd, Carbon $workingEnd): bool
    {
        $serviceEnd = $slotStart->copy()->addMinutes($service->duration_minutes);
        return $serviceEnd <= $workingEnd;
    }

    /**
     * Filter out slots that are already booked.
     */
    private function filterBookedSlots(Collection $slots, Collection $appointments): Collection
    {
        $bookedTimes = $appointments->pluck('appointment_time')->map(function ($time) {
            return Carbon::parse($time)->format('H:i');
        });

        return $slots->filter(function ($slot) use ($bookedTimes) {
            return !$bookedTimes->contains($slot['time']);
        });
    }

    /**
     * Check if a specific time slot is available for booking.
     */
    public function isSlotAvailable(string $date, string $time, int $serviceId): bool
    {
        $availableSlots = $this->getAvailableSlots($date, $serviceId);

        return $availableSlots->contains(function ($slot) use ($time, $serviceId) {
            return $slot['time'] === $time && $slot['service_id'] === $serviceId;
        });
    }
}
