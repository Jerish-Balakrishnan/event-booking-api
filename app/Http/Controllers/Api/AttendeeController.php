<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Attendee;
use Illuminate\Http\Request;
use App\Http\Resources\AttendeeResource;
use Exception;

class AttendeeController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $attendees = Attendee::paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Attendees fetched successfully.',
                'data' => AttendeeResource::collection($attendees),
                'meta' => [
                    'current_page' => $attendees->currentPage(),
                    'last_page' => $attendees->lastPage(),
                    'per_page' => $attendees->perPage(),
                    'total' => $attendees->total(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendees.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => 'required|string|max:255',
                'email' => 'required|email|unique:attendees,email',
                'phone' => 'nullable|string|max:20',
            ]);

            $attendee = Attendee::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Attendee created successfully.',
                'data' => new AttendeeResource($attendee)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create attendee.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Attendee $attendee)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Attendee fetched successfully.',
                'data' => new AttendeeResource($attendee)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch attendee.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Attendee $attendee)
    {
        try {
            $validated = $request->validate([
                'name' => 'sometimes|string|max:255',
                'email' => 'sometimes|email|unique:attendees,email,' . $attendee->id,
                'phone' => 'nullable|string|max:20',
            ]);

            $attendee->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Attendee updated successfully.',
                'data' => new AttendeeResource($attendee)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update attendee.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Attendee $attendee)
    {
        try {
            $attendee->delete();

            return response()->json([
                'success' => true,
                'message' => 'Attendee deleted successfully.',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete attendee.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
