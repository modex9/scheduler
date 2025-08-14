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
        try {
            $workingHours = WorkingHour::orderBy('day_of_week')->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'working_hours' => $workingHours,
                    'total' => $workingHours->count(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching working hours',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific working hour.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $workingHour = WorkingHour::findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'working_hour' => $workingHour,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Working hour not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create a new working hour.
     */
    public function store(CreateWorkingHourRequest $request): JsonResponse
    {
        try {
            $workingHour = WorkingHour::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Working hour created successfully',
                'data' => [
                    'working_hour' => $workingHour,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the working hour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a working hour.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
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

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while updating the working hour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a working hour.
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $workingHour = WorkingHour::findOrFail($id);
            $workingHour->delete();

            return response()->json([
                'success' => true,
                'message' => 'Working hour deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the working hour',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
