<?php

namespace App\Http\Controllers\Api;

use App\Contracts\AvailabilityServiceInterface;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\GetAvailabilityRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class AvailabilityController extends Controller
{
    public function __construct(
        private AvailabilityServiceInterface $availabilityService
    ) {}

    /**
     * Get available time slots for a specific date.
     */
    public function getAvailableSlots(GetAvailabilityRequest $request): JsonResponse
    {
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
    }

    /**
     * Check if a specific time slot is available.
     */
    public function checkSlotAvailability(Request $request): JsonResponse
    {
        $request->validate([
            'date' => 'required|date',
            'time' => 'required|date_format:H:i',
            'service_id' => 'required|integer|exists:services,id',
        ]);

        $date = $request->input('date');
        $time = $request->input('time');
        $serviceId = $request->input('service_id');

        // Custom validation for past date/time
        $appointmentDateTime = \Carbon\Carbon::createFromFormat(
            'Y-m-d H:i',
            $date . ' ' . $time,
            config('app.timezone')
        );

        $now = now();

        if ($appointmentDateTime->lt($now)) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => [
                    'date' => ['Cannot check availability for past dates and times.']
                ]
            ], 422);
        }

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
    }
}
