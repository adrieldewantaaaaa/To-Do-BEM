<?php

namespace App\Services;

use App\Models\Task;
use App\Models\TaskAttachment;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttachmentService
{
    public function store(Task $task, UploadedFile $file): TaskAttachment
    {
        $extension = $file->guessExtension() ?: 'bin';
        $safeName = Str::uuid().'.'.$extension;
        $originalName = Str::limit(preg_replace('/[^\pL\pN._ -]+/u', '_', basename($file->getClientOriginalName())), 255, '');
        $directory = 'attachments/'.$task->project->user_id.'/'.$task->id;
        $path = $file->storeAs($directory, $safeName, 'local');

        return $task->attachments()->create([
            'original_name' => $originalName,
            'file_name' => $safeName,
            'file_path' => $path,
            'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
            'file_size' => $file->getSize(),
        ]);
    }

    public function delete(TaskAttachment $attachment): void
    {
        Storage::disk('local')->delete($attachment->file_path);
        $attachment->delete();
    }
}
