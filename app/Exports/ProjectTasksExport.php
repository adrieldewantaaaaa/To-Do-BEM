<?php

namespace App\Exports;

use App\Models\Project;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class ProjectTasksExport implements FromCollection, WithHeadings, WithMapping
{
    public function __construct(private Project $project) {}

    public function collection(): Collection
    {
        return $this->project->tasks()->orderBy('status')->orderBy('position')->get();
    }

    public function headings(): array
    {
        return ['Project', 'Task', 'Description', 'Status', 'Priority', 'Deadline', 'Created At', 'Updated At'];
    }

    public function map($task): array
    {
        return [$this->project->name, $task->title, $task->description, $task->status, $task->priority, $task->deadline->toDateString(), $task->created_at, $task->updated_at];
    }
}
