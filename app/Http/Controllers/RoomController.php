<?php

namespace App\Http\Controllers;

use App\Http\Requests\JoinRoomRequest;
use App\Models\Task;
use App\Http\Requests\StoreProjectRequest;
use App\Http\Requests\StoreRoomRequest;
use App\Models\Room;
use App\Models\User;
use App\Services\ProjectProgressService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    public function index(): Response
    {
        $this->authorize('viewAny', Room::class);

        $rooms = auth()->user()->rooms()
            ->withCount('members')
            ->withCount('projects')
            ->get()
            ->map(fn (Room $room) => [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'invite_code' => $room->invite_code,
                'members_count' => $room->members_count,
                'projects_count' => $room->projects_count,
                'role' => $room->pivot->role,
                'is_owner' => $room->owner_id === auth()->id(),
            ]);

        return Inertia::render('Rooms/Index', ['rooms' => $rooms]);
    }

    public function store(StoreRoomRequest $request): RedirectResponse
    {
        $this->authorize('create', Room::class);

        $room = DB::transaction(function () use ($request) {
            $room = Room::create([
                ...$request->validated(),
                'owner_id' => $request->user()->id,
                'invite_code' => Room::generateInviteCode(),
            ]);
            $room->members()->attach($request->user()->id, ['role' => 'owner', 'joined_at' => now()]);

            return $room;
        });

        return redirect()->route('rooms.show', $room)->with('success', 'Room created. Share the invite code to add members.');
    }

    public function show(Room $room, ProjectProgressService $progress): Response
    {
        $this->authorize('view', $room);

        $room->load(['owner:id,name', 'members:id,name,email']);

        $projects = $room->projects()
            ->withCount(['tasks', 'tasks as done_tasks_count' => fn (Builder $q) => $q->where('status', 'done')])
            ->latest()
            ->get()
            ->map(function ($project) use ($progress) {
                $project->setAttribute('progress', $progress->summary($project));

                return $project;
            });

        return Inertia::render('Rooms/Show', [
            'room' => [
                'id' => $room->id,
                'name' => $room->name,
                'description' => $room->description,
                'invite_code' => $room->invite_code,
                'owner' => $room->owner,
                'created_at' => $room->created_at,
                'members' => $room->members->map(fn (User $m) => [
                    'id' => $m->id,
                    'name' => $m->name,
                    'email' => $m->email,
                    'role' => $m->pivot->role,
                ]),
            ],
            'projects' => $projects,
            'canManage' => $room->isOwner(auth()->user()),
        ]);
    }

    public function storeProject(StoreProjectRequest $request, Room $room): RedirectResponse
    {
        // Any room member may create a project inside the room.
        $this->authorize('view', $room);

        $project = $room->projects()->make($request->validated());
        $project->user_id = $request->user()->id;
        $project->save();

        return redirect()->route('projects.show', $project)->with('success', 'Project created in room.');
    }

    public function join(JoinRoomRequest $request): RedirectResponse
    {
        $room = Room::where('invite_code', $request->validated('invite_code'))->first();

        if (! $room) {
            return back()->with('error', 'Invalid invite code. Double-check and try again.');
        }

        if ($room->hasMember($request->user())) {
            return redirect()->route('rooms.show', $room)->with('success', 'You are already a member of this room.');
        }

        $room->members()->attach($request->user()->id, ['role' => 'member', 'joined_at' => now()]);

        return redirect()->route('rooms.show', $room)->with('success', "You joined \"{$room->name}\".");
    }

    public function leave(Room $room): RedirectResponse
    {
        $this->authorize('leave', $room);

        $this->detachUserAssignments($room, auth()->id());
        $room->members()->detach(auth()->id());

        return redirect()->route('rooms.index')->with('success', "You left \"{$room->name}\".");
    }

    public function removeMember(Room $room, User $user): RedirectResponse
    {
        $this->authorize('removeMember', $room);

        if ($room->isOwner($user)) {
            return back()->with('error', 'The room owner cannot be removed.');
        }

        $this->detachUserAssignments($room, $user->id);
        $room->members()->detach($user->id);

        return back()->with('success', "{$user->name} was removed from the room.");
    }

    public function destroy(Room $room): RedirectResponse
    {
        $this->authorize('delete', $room);

        $room->delete();

        return redirect()->route('rooms.index')->with('success', 'Room deleted.');
    }

    /**
     * Remove all task assignments for a user across every project in the room.
     */
    private function detachUserAssignments(Room $room, int $userId): void
    {
        $taskIds = Task::whereHas('project', fn ($q) => $q->where('room_id', $room->id))->pluck('id');

        if ($taskIds->isNotEmpty()) {
            DB::table('task_user')
                ->whereIn('task_id', $taskIds)
                ->where('user_id', $userId)
                ->delete();
        }
    }
}
