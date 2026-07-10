<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Person;
use App\Services\PersonActivityService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PersonActivityController extends Controller
{
    public function __construct(
        protected PersonActivityService $service
    ) {}

    public function index(Person $person): JsonResponse
    {
        try {
            $activities = $this->service->getByPerson($person);

            return response()->json([
                'success' => true,
                'data' => $activities,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }

    public function store(Request $request, Person $person): JsonResponse
    {
        try {
            $validated = $request->validate([
                'description' => 'required|string|max:1000',
                'can_parent_view' => 'boolean',
            ]);

            $validated['created_by'] = $request->user()?->id;

            $activity = $this->service->store($person, $validated);

            return response()->json([
                'success' => true,
                'message' => 'Aktivitas berhasil dicatat',
                'data' => $activity,
            ], 201);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors(),
            ], 422);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: '.$e->getMessage(),
            ], 500);
        }
    }
}
