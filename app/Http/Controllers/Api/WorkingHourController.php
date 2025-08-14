<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateWorkingHourRequest;
use App\Models\WorkingHour;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class WorkingHourController extends Controller
{
    /**
     * Get all working hours.
     */
    public function index(): JsonResponse
    {
        $workingHours = WorkingHour::orderBy('day_of_week')->get();

        return response()->json([
            'success' => true,
            'data' => [
                'working_hours' => $workingHours,
                'total' => $workingHours->count(),
            ],
        ]);
    }

    /**
     * Get a specific working hour.
     */
    public function show(int $id): JsonResponse
    {
        $workingHour = WorkingHour::findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'working_hour' => $workingHour,
            ],
        ]);
    }

    /**
     * Create a new working hour.
     */
    public function store(CreateWorkingHourRequest $request): JsonResponse
    {
        $workingHour = WorkingHour::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Working hour created successfully',
            'data' => [
                'working_hour' => $workingHour,
            ],
        ], 201);
    }

    /**
     * Update a working hour.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'day_of_week' => 'sometimes|required|integer|between:0,6',
            'start_time' => 'sometimes|required|date_format:H:i',
            'end_time' => 'sometimes|required|date_format:H:i|after:start_time',
            'is_active' => 'boolean',
        ]);

        $workingHour = WorkingHour::findOrFail($id);
        $workingHour->update($request->all());

        return response()->json([
            'success' => true,
            'message' => 'Working hour updated successfully',
            'data' => [
                'working_hour' => $workingHour,
            ],
        ]);
    }

    /**
     * Delete a working hour.
     */
    public function destroy(int $id): JsonResponse
    {
        $workingHour = WorkingHour::findOrFail($id);
        $workingHour->delete();

        return response()->json([
            'success' => true,
            'message' => 'Working hour deleted successfully',
        ]);
    }
}
