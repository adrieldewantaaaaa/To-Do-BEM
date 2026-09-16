<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomProjectTest extends TestCase
{
    use RefreshDatabase;

    private function roomWithOwner(): array
    {
        $owner = User::factory()->create();
        $room = Room::create(['name' => 'R', 'owner_id' => $owner->id, 'invite_code' => Room::generateInviteCode()]);
        $room->members()->attach($owner->id, ['role' => 'owner', 'joined_at' => now()]);

        return [$room, $owner];
    }

    public function test_member_can_create_project_in_room_but_outsider_cannot(): void
    {
        [$room, $owner] = $this->roomWithOwner();
        $member = User::factory()->create();
        $room->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        $this->actingAs($member)->post(route('rooms.projects.store', $room), [
            'name' => 'Aplikasi To-Do',
            'description' => 'Kolaboratif',
            'deadline' => now()->addWeek()->toDateString(),
            'status' => 'active',
        ])->assertRedirect();

        $project = Project::where('room_id', $room->id)->first();
        $this->assertNotNull($project);
        $this->assertSame($member->id, $project->user_id);

        $outsider = User::factory()->create();
        $this->actingAs($outsider)->post(route('rooms.projects.store', $room), [
            'name' => 'X', 'deadline' => now()->addWeek()->toDateString(), 'status' => 'active',
        ])->assertForbidden();
    }

    public function test_room_project_is_visible_to_members_only(): void
    {
        [$room, $owner] = $this->roomWithOwner();
        $member = User::factory()->create();
        $room->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);
        $project = Project::factory()->for($owner)->create(['room_id' => $room->id]);

        $this->actingAs($member)->get(route('projects.show', $project))->assertOk();
        $this->actingAs($owner)->get(route('projects.show', $project))->assertOk();

        $outsider = User::factory()->create();
        $this->actingAs($outsider)->get(route('projects.show', $project))->assertForbidden();
    }

    public function test_personal_projects_list_excludes_room_projects(): void
    {
        [$room, $owner] = $this->roomWithOwner();
        Project::factory()->for($owner)->create(['room_id' => $room->id, 'name' => 'Room Project']);
        Project::factory()->for($owner)->create(['room_id' => null, 'name' => 'Personal Project']);

        $this->actingAs($owner)->get(route('projects.index'))->assertInertia(
            fn ($page) => $page->component('Projects/Index')->has('projects.data', 1)
        );
    }
}
