<?php

namespace App\Contracts;

use App\Models\Appointment;
use Illuminate\Database\Eloquent\Collection;

interface BookingServiceInterface
{
    /**
     * Book an appointment.
     */
    public function bookAppointment(array $data): Appointment;

    /**
     * Cancel an appointment.
     */
    public function cancelAppointment(int $appointmentId): bool;

    /**
     * Get appointment details.
     */
    public function getAppointment(int $appointmentId): Appointment;

    /**
     * Get appointments for a date range.
     */
    public function getAppointmentsForDateRange(string $startDate, string $endDate): Collection;
}
