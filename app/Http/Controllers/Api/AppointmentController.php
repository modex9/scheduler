<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\BookAppointmentRequest;
use App\Services\BookingService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AppointmentController extends Controller
{
    public function __construct(
        private BookingService $bookingService
    ) {}

    /**
     * Book a new appointment.
     */
    public function store(BookAppointmentRequest $request): JsonResponse
    {
        try {
            $appointment = $this->bookingService->bookAppointment($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Appointment booked successfully',
                'data' => [
                    'appointment' => $appointment->load('service'),
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while booking the appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get appointment details.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $appointment = $this->bookingService->getAppointment($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'appointment' => $appointment,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Appointment not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Cancel an appointment.
     */
    public function cancel(int $id): JsonResponse
    {
        try {
            $this->bookingService->cancelAppointment($id);

            return response()->json([
                'success' => true,
                'message' => 'Appointment cancelled successfully',
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while cancelling the appointment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get appointments for a date range.
     */
    public function getAppointmentsForDateRange(Request $request): JsonResponse
    {
        try {
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

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching appointments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
