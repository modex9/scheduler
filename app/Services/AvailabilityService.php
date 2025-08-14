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
        $workingHours = WorkingHour::where('day_of_week', $dayOfWeek)
            ->where('is_active', true)
            ->get();

        if ($workingHours->isEmpty()) {
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
        $slots = $this->generateTimeSlots($workingHours, $services);

        // Mark slots as available or booked
        return $this->markSlotAvailability($slots, $existingAppointments);
    }

    /**
     * Generate time slots based on working hours and service durations.
     */
    private function generateTimeSlots(Collection $workingHours, Collection $services): Collection
    {
        $slots = collect();

        foreach ($workingHours as $workingHour) {
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
     * Mark slots as available, booked, or insufficient gap.
     */
    private function markSlotAvailability(Collection $slots, Collection $appointments): Collection
    {
        return $slots->map(function ($slot) use ($appointments) {
            $slotTime = Carbon::parse($slot['time']);
            $slotEnd = $slotTime->copy()->addMinutes($slot['duration_minutes']);

            // Check if this slot is exactly the start time of an existing appointment
            $isBooked = $appointments->contains(function ($appointment) use ($slotTime) {
                $appointmentStart = Carbon::parse($appointment->appointment_time);
                return $slotTime->format('H:i') === $appointmentStart->format('H:i');
            });

            // Check if this slot falls within the duration of any existing appointment
            $fallsWithinAppointment = $appointments->contains(function ($appointment) use ($slotTime) {
                $appointmentStart = Carbon::parse($appointment->appointment_time);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->service->duration_minutes);

                // Check if slot falls within appointment duration (but is not the exact start time)
                return $slotTime >= $appointmentStart && $slotTime < $appointmentEnd;
            });

            // Check if there's insufficient gap - slot would overlap with appointment when considering service duration
            $hasInsufficientGap = $appointments->contains(function ($appointment) use ($slotTime, $slotEnd) {
                $appointmentStart = Carbon::parse($appointment->appointment_time);
                $appointmentEnd = $appointmentStart->copy()->addMinutes($appointment->service->duration_minutes);

                // Check if slot would overlap with appointment when considering service duration
                $slotWouldOverlap = $slotTime < $appointmentEnd && $slotEnd > $appointmentStart;

                // But it's not the exact start time and doesn't fall within appointment
                // AND the slot doesn't start within the appointment duration
                return $slotWouldOverlap &&
                       $slotTime->format('H:i') !== $appointmentStart->format('H:i') &&
                       $slotTime < $appointmentStart; // Only insufficient gap if slot starts BEFORE appointment
            });

            $slot['is_available'] = !$isBooked && !$fallsWithinAppointment && !$hasInsufficientGap;
            $slot['is_booked'] = $isBooked || $fallsWithinAppointment; // Booked if exact start time OR falls within appointment
            $slot['insufficient_gap'] = $hasInsufficientGap; // Only insufficient gap if starts before appointment

            return $slot;
        });
    }

    /**
     * Check if a specific time slot is available for booking.
     */
    public function isSlotAvailable(string $date, string $time, int $serviceId): bool
    {
        $slots = $this->getAvailableSlots($date, $serviceId);

        $slot = $slots->first(function ($slot) use ($time, $serviceId) {
            return $slot['time'] === $time && $slot['service_id'] === $serviceId;
        });

        return $slot ? $slot['is_available'] : false;
    }
}
