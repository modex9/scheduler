<?php

namespace App\Services;

use App\Models\Appointment;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingService
{
    public function __construct(
        private AvailabilityService $availabilityService
    ) {}

    /**
     * Book an appointment.
     */
    public function bookAppointment(array $data): Appointment
    {
        // Validate the booking data
        $this->validateBookingData($data);

        // Check if the slot is available
        if (!$this->availabilityService->isSlotAvailable(
            $data['appointment_date'],
            $data['appointment_time'],
            $data['service_id']
        )) {
            throw ValidationException::withMessages([
                'appointment_time' => 'The selected time slot is not available.'
            ]);
        }

        // Create the appointment within a transaction
        return DB::transaction(function () use ($data) {
            return Appointment::create([
                'appointment_date' => $data['appointment_date'],
                'appointment_time' => $data['appointment_time'],
                'service_id' => $data['service_id'],
                'client_email' => $data['client_email'],
                'client_name' => $data['client_name'],
                'notes' => $data['notes'] ?? null,
                'status' => 'confirmed',
            ]);
        });
    }

    /**
     * Validate booking data.
     */
    private function validateBookingData(array $data): void
    {
        // Check if date is in the future
        $appointmentDate = Carbon::parse($data['appointment_date']);
        if ($appointmentDate->isPast()) {
            throw ValidationException::withMessages([
                'appointment_date' => 'Appointments cannot be booked for past dates.'
            ]);
        }

        // Check if service exists and is active
        $service = Service::where('id', $data['service_id'])
            ->where('is_active', true)
            ->first();

        if (!$service) {
            throw ValidationException::withMessages([
                'service_id' => 'The selected service is not available.'
            ]);
        }

        // Validate email format
        if (!filter_var($data['client_email'], FILTER_VALIDATE_EMAIL)) {
            throw ValidationException::withMessages([
                'client_email' => 'Please provide a valid email address.'
            ]);
        }

        // Validate required fields
        $requiredFields = ['appointment_date', 'appointment_time', 'service_id', 'client_email', 'client_name'];
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                throw ValidationException::withMessages([
                    $field => "The {$field} field is required."
                ]);
            }
        }
    }

    /**
     * Cancel an appointment.
     */
    public function cancelAppointment(int $appointmentId): bool
    {
        $appointment = Appointment::findOrFail($appointmentId);

        if ($appointment->status === 'cancelled') {
            throw ValidationException::withMessages([
                'appointment' => 'This appointment is already cancelled.'
            ]);
        }

        $appointment->update(['status' => 'cancelled']);

        return true;
    }

    /**
     * Get appointment details.
     */
    public function getAppointment(int $appointmentId): Appointment
    {
        return Appointment::with('service')->findOrFail($appointmentId);
    }

    /**
     * Get appointments for a date range.
     */
    public function getAppointmentsForDateRange(string $startDate, string $endDate): \Illuminate\Database\Eloquent\Collection
    {
        return Appointment::with('service')
            ->whereBetween('appointment_date', [$startDate, $endDate])
            ->where('status', 'confirmed')
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();
    }
}
