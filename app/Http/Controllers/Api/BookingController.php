<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Event;
use Illuminate\Http\Request;
use App\Http\Resources\BookingResource;
use Exception;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'event_id' => 'required|exists:events,id',
                'attendee_id' => 'required|exists:attendees,id',
            ]);

            $event = Event::findOrFail($validated['event_id']);

            // Check if attendee already booked the event
            $alreadyBooked = Booking::where('event_id', $event->id)
                ->where('attendee_id', $validated['attendee_id'])
                ->exists();

            if ($alreadyBooked) {
                return response()->json([
                    'success' => false,
                    'message' => 'Attendee has already booked this event.',
                    'data' => null
                ], 409);
            }

            // Check if capacity exceeded
            $currentBookings = Booking::where('event_id', $event->id)->count();

            if ($currentBookings >= $event->capacity) {
                return response()->json([
                    'success' => false,
                    'message' => 'Event is fully booked.',
                    'data' => null
                ], 403);
            }

            $booking = Booking::create($validated);

            return response()->json([
                'success' => true,
                'message' => 'Booking created successfully.',
                'data' => new BookingResource($booking)
            ], 201);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create booking.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
