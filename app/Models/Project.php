<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Project extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'deadline', 'status', 'room_id'];

    protected $casts = ['deadline' => 'date'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function room(): BelongsTo
    {
        return $this->belongsTo(Room::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    public function scopeOwnedBy(Builder $query, User $user): Builder
    {
        return $query->where('user_id', $user->id);
    }

    /** Personal projects only (not attached to a room) — preserves the original To-Do behaviour. */
    public function scopePersonal(Builder $query): Builder
    {
        return $query->whereNull('room_id');
    }

    /** Can this user see the project? Personal → owner only; room → any room member. */
    public function accessibleBy(User $user): bool
    {
        return $this->room_id
            ? $this->room->hasMember($user)
            : $this->user_id === $user->id;
    }

    /** Can this user edit/delete the project itself? Personal → owner; room → creator or room owner. */
    public function manageableBy(User $user): bool
    {
        if (! $this->room_id) {
            return $this->user_id === $user->id;
        }

        return $this->user_id === $user->id || $this->room->isOwner($user);
    }
}
