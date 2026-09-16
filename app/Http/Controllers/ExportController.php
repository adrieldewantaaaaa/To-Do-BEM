<?php

namespace App\Http\Controllers;

use App\Exports\ProjectTasksExport;
use App\Models\Project;
use App\Services\ProjectProgressService;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;

class ExportController extends Controller
{
    public function pdf(Project $project, ProjectProgressService $service): Response
    {
        $this->authorize('view', $project);
        $project->load('tasks');

        return Pdf::loadView('exports.project', ['project' => $project, 'progress' => $service->summary($project)])->download('tdb-'.$project->id.'-report.pdf');
    }

    public function excel(Project $project): BinaryFileResponse
    {
        $this->authorize('view', $project);

        return Excel::download(new ProjectTasksExport($project), 'tdb-'.$project->id.'-tasks.xlsx');
    }
}
