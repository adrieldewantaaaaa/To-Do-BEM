<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Task extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'deadline', 'status', 'priority', 'position'];

    protected $casts = ['deadline' => 'date'];

    protected $appends = ['deadline_state'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignees(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'task_user')
            ->withTimestamps()
            ->orderBy('name');
    }

    public function attachments(): HasMany
    {
        return $this->hasMany(TaskAttachment::class);
    }

    public function getDeadlineStateAttribute(): string
    {
        if ($this->status === 'done' || ! $this->deadline) {
            return 'normal';
        }
        $today = now()->startOfDay();
        if ($this->deadline->lt($today)) {
            return 'overdue';
        }
        if ($this->deadline->isSameDay($today)) {
            return 'today';
        }
        if ($this->deadline->diffInDays($today, true) <= 7) {
            return 'upcoming';
        }

        return 'normal';
    }

    /** Personal workspace tasks only: owned by the user and NOT inside a room. */
    public function scopeForUser(Builder $query, User $user): Builder
    {
        return $query->whereHas('project', fn (Builder $q) => $q->where('user_id', $user->id)->whereNull('room_id'));
    }

    public function scopeApplyFilters(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where(fn (Builder $inner) => $inner->where('title', 'like', "%{$search}%")->orWhere('description', 'like', "%{$search}%")))
            ->when($filters['status'] ?? null, fn (Builder $q, string $status) => $status === 'all' ? $q : $q->where('status', $status))
            ->when($filters['priority'] ?? null, fn (Builder $q, string $priority) => $priority === 'all' ? $q : $q->where('priority', $priority))
            ->when($filters['deadline'] ?? null, function (Builder $q, string $deadline) {
                return match ($deadline) {
                    'today' => $q->whereDate('deadline', today()),
                    'week' => $q->whereBetween('deadline', [today(), today()->endOfWeek()]),
                    'month' => $q->whereBetween('deadline', [today(), today()->endOfMonth()]),
                    'overdue' => $q->whereDate('deadline', '<', today())->where('status', '!=', 'done'),
                    default => $q,
                };
            })
            ->when($filters['room'] ?? null, fn (Builder $q, $roomId) => $roomId === 'all' ? $q : $q->whereHas('project', fn (Builder $pq) => $pq->where('room_id', $roomId)));
    }

    /** Tasks assigned to the user (room projects) OR owned personally (no room). */
    public function scopeMyTasks(Builder $query, User $user): Builder
    {
        return $query->where(function (Builder $q) use ($user) {
            // Tasks assigned to the user in any room project
            $q->whereHas('assignees', fn (Builder $aq) => $aq->where('users.id', $user->id))
              // OR personal workspace tasks (not in a room, owned by user)
              ->orWhereHas('project', fn (Builder $pq) => $pq->where('user_id', $user->id)->whereNull('room_id'));
        });
    }
}
