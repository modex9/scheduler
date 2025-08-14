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
        $services = Service::active()->get();

        return response()->json([
            'success' => true,
            'data' => [
                'services' => $services,
                'total' => $services->count(),
            ],
        ]);
    }

    /**
     * Get a specific service.
     */
    public function show(int $id): JsonResponse
    {
        $service = Service::active()->findOrFail($id);

        return response()->json([
            'success' => true,
            'data' => [
                'service' => $service,
            ],
        ]);
    }

    /**
     * Create a new service (admin only).
     */
    public function store(CreateServiceRequest $request): JsonResponse
    {
        $service = Service::create($request->validated());

        return response()->json([
            'success' => true,
            'message' => 'Service created successfully',
            'data' => [
                'service' => $service,
            ],
        ], 201);
    }

    /**
     * Update a service (admin only).
     */
    public function update(Request $request, int $id): JsonResponse
    {
                    $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'duration_minutes' => 'sometimes|required|integer|min:15|max:480',
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
    }

    /**
     * Delete a service (admin only).
     */
    public function destroy(int $id): JsonResponse
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return response()->json([
            'success' => true,
            'message' => 'Service deleted successfully',
        ]);
    }
}
