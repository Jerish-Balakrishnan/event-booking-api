<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\EventResource;
use Exception;

class EventController extends Controller
{
    public function index(Request $request)
    {
        try {
            $perPage = $request->query('per_page', 10);
            $events = Event::paginate($perPage);

            return response()->json([
                'success' => true,
                'message' => 'Events fetched successfully.',
                'data' => EventResource::collection($events),
                'meta' => [
                    'current_page' => $events->currentPage(),
                    'last_page' => $events->lastPage(),
                    'per_page' => $events->perPage(),
                    'total' => $events->total(),
                ]
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch events.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'date' => 'required|date|after_or_equal:today',
                'country' => 'required|string|max:255',
                'capacity' => 'required|integer|min:1',
            ]);

            $event = Event::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event created successfully.',
                'data' => new EventResource($event)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function show(Event $event)
    {
        try {
            return response()->json([
                'success' => true,
                'message' => 'Event fetched successfully.',
                'data' => new EventResource($event)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function update(Request $request, Event $event)
    {
        try {
            $validated = $request->validate([
                'title' => 'sometimes|string|max:255',
                'description' => 'nullable|string',
                'date' => 'sometimes|date|after_or_equal:today',
                'country' => 'sometimes|string|max:255',
                'capacity' => 'sometimes|integer|min:1',
            ]);

            $event->update($validated);

            return response()->json([
                'success' => true,
                'message' => 'Event updated successfully.',
                'data' => new EventResource($event)
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Event $event)
    {
        try {
            $event->delete();

            return response()->json([
                'success' => true,
                'message' => 'Event deleted successfully.',
                'data' => null
            ], 200);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete event.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
