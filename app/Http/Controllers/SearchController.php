<?php

namespace App\Http\Controllers;

use App\Http\Requests\SearchRequest;
use App\Models\Project;
use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Database\Eloquent\Builder;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request): Response
    {
        $q = trim((string) $request->validated('q', ''));
        $projects = $q === '' ? collect() : Project::ownedBy($request->user())->personal()->where(fn (Builder $x) => $x->where('name', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%"))->limit(8)->get();
        $tasks = $q === '' ? collect() : Task::forUser($request->user())->with('project:id,name')->where(fn (Builder $x) => $x->where('title', 'like', "%{$q}%")->orWhere('description', 'like', "%{$q}%"))->limit(12)->get();
        $files = $q === '' ? collect() : TaskAttachment::with('task.project:id,name')->where('original_name', 'like', "%{$q}%")->whereHas('task.project', fn (Builder $x) => $x->where('user_id', $request->user()->id)->whereNull('room_id'))->limit(8)->get();

        return Inertia::render('Search/Index', compact('q', 'projects', 'tasks', 'files'));
    }
}
