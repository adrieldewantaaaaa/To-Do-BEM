<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttachmentRequest;
use App\Models\Task;
use App\Models\TaskAttachment;
use App\Services\AttachmentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class AttachmentController extends Controller
{
    public function store(StoreAttachmentRequest $request, Task $task, AttachmentService $service): RedirectResponse
    {
        $this->authorize('create', [TaskAttachment::class, $task]);
        foreach ($request->file('attachments') as $file) {
            $service->store($task, $file);
        }

        return back()->with('success', 'Attachments uploaded.');
    }

    public function download(TaskAttachment $attachment): StreamedResponse
    {
        $this->authorize('view', $attachment);
        abort_unless(Storage::disk('local')->exists($attachment->file_path), 404);

        return Storage::disk('local')->download($attachment->file_path, $attachment->original_name);
    }

    public function destroy(TaskAttachment $attachment, AttachmentService $service): RedirectResponse
    {
        $this->authorize('delete', $attachment);
        $service->delete($attachment);

        return back()->with('success', 'Attachment deleted.');
    }
}
