<?php

namespace App\Modules\Ecosystem\Services;

use App\Models\Event;
use App\Models\EventAttendee;
use Illuminate\Database\Eloquent\Collection;

class EventService
{
    /**
     * List events (all except cancelled), ordered by start_date ASC.
     */
    public function listActive(?string $eventType = null, ?string $city = null): Collection
    {
        $query = Event::where('status', '!=', 'cancelled')
            ->with('user:id,username,display_name,avatar_url');

        if ($eventType) {
            $query->where('event_type', $eventType);
        }
        if ($city) {
            $query->where('city', 'like', "%{$city}%");
        }

        return $query->orderBy('start_date')->get();
    }

    public function create(int $userId, array $data): Event
    {
        $data['user_id'] = $userId;
        $data['status'] = 'upcoming';

        if (isset($data['price']) && $data['price'] > 0) {
            $data['is_free'] = false;
        }

        return Event::create($data)->load('user:id,username,display_name,avatar_url');
    }

    public function find(int $id): Event
    {
        return Event::with('user:id,username,display_name,avatar_url')
            ->findOrFail($id);
    }

    public function update(int $userId, int $id, array $data): Event
    {
        $event = Event::findOrFail($id);

        if ($event->user_id !== $userId) {
            abort(403, 'Vous ne pouvez modifier que vos propres événements.');
        }

        $event->update($data);

        return $event->fresh()->load('user:id,username,display_name,avatar_url');
    }

    public function delete(int $userId, int $id): void
    {
        $event = Event::findOrFail($id);

        if ($event->user_id !== $userId) {
            abort(403, 'Vous ne pouvez supprimer que vos propres événements.');
        }

        $event->delete();
    }

    /**
     * Toggle attendance for an event.
     * Blocked if max_attendees reached (on registration, not on unregistration).
     */
    public function toggleAttend(int $userId, int $eventId): array
    {
        $event = Event::findOrFail($eventId);

        $existing = EventAttendee::where('event_id', $eventId)
            ->where('user_id', $userId)
            ->first();

        if ($existing) {
            $existing->delete();
            $event->decrement('attendees_count');

            return ['attending' => false, 'attendees_count' => $event->fresh()->attendees_count];
        }

        // Check capacity
        if ($event->max_attendees && $event->attendees_count >= $event->max_attendees) {
            abort(422, 'Cet événement est complet.');
        }

        EventAttendee::create([
            'event_id' => $eventId,
            'user_id'  => $userId,
        ]);
        $event->increment('attendees_count');

        return ['attending' => true, 'attendees_count' => $event->fresh()->attendees_count];
    }
}
