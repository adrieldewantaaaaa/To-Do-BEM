<?php

namespace Tests\Feature;

use App\Models\Project;
use App\Models\Task;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AttachmentValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_valid_attachment_is_stored_with_a_safe_name(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $file = UploadedFile::fake()->create('client brief.pdf', 250, 'application/pdf');
        $this->actingAs($user)->post(route('projects.tasks.store', $project), ['title' => 'Review brief', 'description' => null, 'deadline' => '2026-09-20', 'status' => 'todo', 'priority' => 'medium', 'attachments' => [$file]])->assertSessionHasNoErrors();
        $task = Task::firstOrFail();
        $attachment = $task->attachments()->firstOrFail();
        $this->assertSame('client brief.pdf', $attachment->original_name);
        $this->assertNotSame('client brief.pdf', $attachment->file_name);
        Storage::disk('local')->assertExists($attachment->file_path);
    }

    public function test_attachment_larger_than_ten_mb_is_rejected(): void
    {
        Storage::fake('local');
        $user = User::factory()->create();
        $project = Project::factory()->for($user)->create();
        $file = UploadedFile::fake()->create('huge.pdf', 10241, 'application/pdf');
        $this->actingAs($user)->post(route('projects.tasks.store', $project), ['title' => 'Too large', 'deadline' => '2026-09-20', 'status' => 'todo', 'priority' => 'medium', 'attachments' => [$file]])->assertSessionHasErrors('attachments.0');
    }
}
