<?php

namespace App\Http\Controllers\Api;

use App\Contracts\BookingServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookAppointmentRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentController extends Controller
{
    public function __construct(
        private BookingServiceInterface $bookingService
    ) {}

    /**
     * Book a new appointment.
     */
    public function store(BookAppointmentRequest $request): JsonResponse
    {
        $appointment = $this->bookingService->bookAppointment($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Appointment booked successfully',
            'data' => [
                'appointment' => $appointment->load('service'),
            ],
        ], 201);
    }

    /**
     * Get appointment details.
     */
    public function show(int $id): JsonResponse
    {
        $appointment = $this->bookingService->getAppointment($id);

        return response()->json([
            'success' => true,
            'data' => [
                'appointment' => $appointment,
            ],
        ]);
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(int $id): JsonResponse
    {
        $this->bookingService->cancelAppointment($id);

        return response()->json([
            'success' => true,
            'message' => 'Appointment cancelled successfully',
        ]);
    }

    /**
     * Get appointments for a date range.
     */
    public function getAppointmentsForDateRange(Request $request): JsonResponse
    {
        $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        $appointments = $this->bookingService->getAppointmentsForDateRange($startDate, $endDate);

        return response()->json([
            'success' => true,
            'data' => [
                'appointments' => $appointments,
                'total' => $appointments->count(),
            ],
        ]);
    }
}
