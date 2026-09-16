<?php

namespace Tests\Feature;

use App\Models\Room;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoomTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_create_room_and_becomes_owner(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->post(route('rooms.store'), [
            'name' => 'Kelompok Mobile',
            'description' => 'Tugas akhir',
        ])->assertRedirect();

        $room = Room::first();
        $this->assertNotNull($room);
        $this->assertSame($user->id, $room->owner_id);
        $this->assertNotEmpty($room->invite_code);
        $this->assertTrue($room->isOwner($user));
        $this->assertSame('owner', $room->members()->find($user->id)->pivot->role);
    }

    public function test_user_can_join_room_with_invite_code(): void
    {
        $owner = User::factory()->create();
        $room = Room::create(['name' => 'R', 'owner_id' => $owner->id, 'invite_code' => Room::generateInviteCode()]);
        $room->members()->attach($owner->id, ['role' => 'owner', 'joined_at' => now()]);

        $joiner = User::factory()->create();
        $this->actingAs($joiner)->post(route('rooms.join'), ['invite_code' => $room->invite_code])
            ->assertRedirect(route('rooms.show', $room));

        $this->assertTrue($room->fresh()->hasMember($joiner));
    }

    public function test_invalid_invite_code_is_rejected(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user)->post(route('rooms.join'), ['invite_code' => 'NOPE1234'])
            ->assertSessionHas('error');
        $this->assertSame(0, $user->rooms()->count());
    }

    public function test_non_member_cannot_view_room(): void
    {
        $owner = User::factory()->create();
        $room = Room::create(['name' => 'R', 'owner_id' => $owner->id, 'invite_code' => Room::generateInviteCode()]);
        $room->members()->attach($owner->id, ['role' => 'owner']);

        $outsider = User::factory()->create();
        $this->actingAs($outsider)->get(route('rooms.show', $room))->assertForbidden();
        $this->actingAs($owner)->get(route('rooms.show', $room))->assertOk();
    }

    public function test_owner_can_remove_member_but_not_owner(): void
    {
        $owner = User::factory()->create();
        $room = Room::create(['name' => 'R', 'owner_id' => $owner->id, 'invite_code' => Room::generateInviteCode()]);
        $room->members()->attach($owner->id, ['role' => 'owner']);
        $member = User::factory()->create();
        $room->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($owner)->delete(route('rooms.members.remove', [$room, $member]))->assertRedirect();
        $this->assertFalse($room->fresh()->hasMember($member));

        // A member cannot remove others.
        $other = User::factory()->create();
        $room->members()->attach($other->id, ['role' => 'member']);
        $this->actingAs($other)->delete(route('rooms.members.remove', [$room, $owner]))->assertForbidden();
    }

    public function test_member_can_leave_but_owner_cannot(): void
    {
        $owner = User::factory()->create();
        $room = Room::create(['name' => 'R', 'owner_id' => $owner->id, 'invite_code' => Room::generateInviteCode()]);
        $room->members()->attach($owner->id, ['role' => 'owner']);
        $member = User::factory()->create();
        $room->members()->attach($member->id, ['role' => 'member']);

        $this->actingAs($member)->delete(route('rooms.leave', $room))->assertRedirect();
        $this->assertFalse($room->fresh()->hasMember($member));

        $this->actingAs($owner)->delete(route('rooms.leave', $room))->assertForbidden();
    }
}
