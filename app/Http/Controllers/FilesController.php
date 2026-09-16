<?php

namespace App\Http\Controllers;

use App\Models\TaskAttachment;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class FilesController extends Controller
{
    public function __invoke(Request $request): Response
    {
        $filters = $request->validate(['search' => ['nullable', 'string', 'max:120']]);
        $files = TaskAttachment::query()->with(['task:id,project_id,title', 'task.project:id,name'])->whereHas('task.project', fn (Builder $q) => $q->where('user_id', $request->user()->id)->whereNull('room_id'))->when($filters['search'] ?? null, fn (Builder $q, string $search) => $q->where('original_name', 'like', "%{$search}%"))->latest()->paginate(20)->withQueryString();

        return Inertia::render('Files/Index', compact('files', 'filters'));
    }
}
