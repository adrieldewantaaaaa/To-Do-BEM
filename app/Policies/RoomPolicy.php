<?php

namespace App\Policies;

use App\Models\Room;
use App\Models\User;

class RoomPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Room $room): bool
    {
        return $room->hasMember($user);
    }

    public function create(User $user): bool
    {
        return true;
    }

    public function update(User $user, Room $room): bool
    {
        return $room->isOwner($user);
    }

    public function delete(User $user, Room $room): bool
    {
        return $room->isOwner($user);
    }

    public function removeMember(User $user, Room $room): bool
    {
        return $room->isOwner($user);
    }

    public function leave(User $user, Room $room): bool
    {
        // Owner cannot leave their own room (must delete it instead).
        return $room->hasMember($user) && ! $room->isOwner($user);
    }
}
