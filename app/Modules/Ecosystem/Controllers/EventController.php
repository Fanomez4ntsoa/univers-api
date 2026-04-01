<?php

namespace App\Modules\Ecosystem\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Modules\Ecosystem\Requests\StoreEventRequest;
use App\Modules\Ecosystem\Requests\UpdateEventRequest;
use App\Modules\Ecosystem\Services\EventService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(private EventService $eventService) {}

    public function index(Request $request): JsonResponse
    {
        return response()->json(
            $this->eventService->listActive(
                $request->query('event_type'),
                $request->query('city'),
            )
        );
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->eventService->create($user->id, $request->validated()),
            201
        );
    }

    public function show(int $id): JsonResponse
    {
        return response()->json($this->eventService->find($id));
    }

    public function update(UpdateEventRequest $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json(
            $this->eventService->update($user->id, $id, $request->validated())
        );
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');
        $this->eventService->delete($user->id, $id);

        return response()->json(['message' => 'Événement supprimé.']);
    }

    public function toggleAttend(Request $request, int $id): JsonResponse
    {
        /** @var User $user */
        $user = $request->attributes->get('auth_user');

        return response()->json($this->eventService->toggleAttend($user->id, $id));
    }
}
