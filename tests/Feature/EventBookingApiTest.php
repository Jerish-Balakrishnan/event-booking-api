<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\Event;
use App\Models\Attendee;
use App\Models\Booking;

class EventBookingApiTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_events_with_pagination()
    {
        Event::factory()->count(15)->create();

        $response = $this->getJson('/api/events?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /** @test */
    public function it_can_create_an_event()
    {
        $data = [
            'title' => 'Test Event',
            'description' => 'Test Description',
            'date' => now()->addDays(1)->toDateString(),
            'country' => 'Test Country',
            'capacity' => 100,
        ];

        $response = $this->postJson('/api/events', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['title' => 'Test Event']);
    }

    /** @test */
    public function it_can_show_an_event()
    {
        $event = Event::factory()->create();

        $response = $this->getJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $event->id]);
    }

    /** @test */
    public function it_can_update_an_event()
    {
        $event = Event::factory()->create();

        $response = $this->putJson("/api/events/{$event->id}", [
            'title' => 'Updated Title'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['title' => 'Updated Title']);
    }

    /** @test */
    public function it_can_delete_an_event()
    {
        $event = Event::factory()->create();

        $response = $this->deleteJson("/api/events/{$event->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Event deleted successfully.']);
    }

    /** @test */
    public function it_can_list_attendees_with_pagination()
    {
        Attendee::factory()->count(15)->create();

        $response = $this->getJson('/api/attendees?per_page=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'success',
                'message',
                'data',
                'meta' => [
                    'current_page',
                    'last_page',
                    'per_page',
                    'total',
                ],
            ]);
    }

    /** @test */
    public function it_can_create_an_attendee()
    {
        $data = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'phone' => '1234567890',
        ];

        $response = $this->postJson('/api/attendees', $data);

        $response->assertStatus(201)
            ->assertJsonFragment(['email' => 'john@example.com']);
    }

    /** @test */
    public function it_can_show_an_attendee()
    {
        $attendee = Attendee::factory()->create();

        $response = $this->getJson("/api/attendees/{$attendee->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['id' => $attendee->id]);
    }

    /** @test */
    public function it_can_update_an_attendee()
    {
        $attendee = Attendee::factory()->create();

        $response = $this->putJson("/api/attendees/{$attendee->id}", [
            'name' => 'Jane Doe'
        ]);

        $response->assertStatus(200)
            ->assertJsonFragment(['name' => 'Jane Doe']);
    }

    /** @test */
    public function it_can_delete_an_attendee()
    {
        $attendee = Attendee::factory()->create();

        $response = $this->deleteJson("/api/attendees/{$attendee->id}");

        $response->assertStatus(200)
            ->assertJsonFragment(['message' => 'Attendee deleted successfully.']);
    }

    /** @test */
    public function it_can_create_a_booking()
    {
        $event = Event::factory()->create(['capacity' => 2]);
        $attendee = Attendee::factory()->create();

        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendee->id,
        ]);

        $response->assertStatus(201)
            ->assertJsonFragment(['event_id' => $event->id, 'attendee_id' => $attendee->id]);
    }

    /** @test */
    public function it_prevents_duplicate_booking()
    {
        $event = Event::factory()->create(['capacity' => 2]);
        $attendee = Attendee::factory()->create();

        Booking::create([
            'event_id' => $event->id,
            'attendee_id' => $attendee->id,
        ]);

        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendee->id,
        ]);

        $response->assertStatus(409)
            ->assertJsonFragment(['message' => 'Attendee has already booked this event.']);
    }

    /** @test */
    public function it_prevents_overbooking()
    {
        $event = Event::factory()->create(['capacity' => 1]);
        $attendee1 = Attendee::factory()->create();
        $attendee2 = Attendee::factory()->create();

        Booking::create([
            'event_id' => $event->id,
            'attendee_id' => $attendee1->id,
        ]);

        $response = $this->postJson('/api/bookings', [
            'event_id' => $event->id,
            'attendee_id' => $attendee2->id,
        ]);

        $response->assertStatus(403)
            ->assertJsonFragment(['message' => 'Event is fully booked.']);
    }
}
