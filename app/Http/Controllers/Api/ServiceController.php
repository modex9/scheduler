<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ServiceController extends Controller
{
    /**
     * Get all active services.
     */
    public function index(): JsonResponse
    {
        try {
            $services = Service::active()->get();

            return response()->json([
                'success' => true,
                'data' => [
                    'services' => $services,
                    'total' => $services->count(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while fetching services',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Get a specific service.
     */
    public function show(int $id): JsonResponse
    {
        try {
            $service = Service::active()->findOrFail($id);

            return response()->json([
                'success' => true,
                'data' => [
                    'service' => $service,
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Service not found',
                'error' => $e->getMessage(),
            ], 404);
        }
    }

    /**
     * Create a new service (admin only).
     */
    public function store(CreateServiceRequest $request): JsonResponse
    {
        try {
            $service = Service::create($request->validated());

            return response()->json([
                'success' => true,
                'message' => 'Service created successfully',
                'data' => [
                    'service' => $service,
                ],
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while creating the service',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update a service (admin only).
     */
    public function update(Request $request, int $id): JsonResponse
    {
        try {
            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'duration_minutes' => 'sometimes|required|integer|min:15|max:480',
                'price' => 'sometimes|required|numeric|min:0',
                'description' => 'nullable|string',
                'is_active' => 'boolean',
            ]);

            $service = Service::findOrFail($id);
            $service->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Service updated successfully',
                'data' => [
                    'service' => $service,
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
                'message' => 'An error occurred while updating the service',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a service (admin only).
     */
    public function destroy(int $id): JsonResponse
    {
        try {
            $service = Service::findOrFail($id);
            $service->delete();

            return response()->json([
                'success' => true,
                'message' => 'Service deleted successfully',
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'An error occurred while deleting the service',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
