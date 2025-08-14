<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetAvailabilityRequest;
use App\Services\AvailabilityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{
    public function __construct(
        private AvailabilityService $availabilityService
    ) {}

    /**
     * Get available time slots for a specific date.
     */
    public function getAvailableSlots(GetAvailabilityRequest $request): JsonResponse
    {
        try {
            $date = $request->input('date');
            $serviceId = $request->input('service_id');

            $availableSlots = $this->availabilityService->getAvailableSlots($date, $serviceId);

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'available_slots' => $availableSlots->values(),
                    'total_slots' => $availableSlots->count(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching availability',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a specific time slot is available.
     */
    public function checkSlotAvailability(Request $request): JsonResponse
    {
        try {
            $request->validate([
                'date' => 'required|date|after_or_equal:today',
                'time' => 'required|date_format:H:i',
                'service_id' => 'required|integer|exists:services,id',
            ]);

            $date = $request->input('date');
            $time = $request->input('time');
            $serviceId = $request->input('service_id');

            $isAvailable = $this->availabilityService->isSlotAvailable($date, $time, $serviceId);

            return response()->json([
                'success' => true,
                'data' => [
                    'date' => $date,
                    'time' => $time,
                    'service_id' => $serviceId,
                    'is_available' => $isAvailable,
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
                'message' => 'An error occurred while checking availability',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
