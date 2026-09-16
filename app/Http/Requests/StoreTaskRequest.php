<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:200'],
            'description' => ['nullable', 'string', 'max:5000'],
            'deadline' => ['required', 'date'],
            'status' => ['required', Rule::in(['todo', 'in_progress', 'done'])],
            'priority' => ['required', Rule::in(['low', 'medium', 'high'])],
            'attachments' => ['nullable', 'array', 'max:10'],
            'attachments.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,png,jpg,jpeg,zip'],
            'assignees' => ['nullable', 'array'],
            'assignees.*' => ['integer', 'exists:users,id'],
        ];
    }

    /**
     * Additional validation: every assignee must be a member of the project's room.
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $assignees = $this->input('assignees', []);
            if (empty($assignees)) {
                return;
            }

            // Resolve the project from the route (store uses {project}, update uses {task})
            $project = $this->route('project') ?? $this->route('task')?->project;

            if (! $project || ! $project->room_id) {
                // Personal project — silently ignore assignees (do not fail)
                return;
            }

            $room = $project->room;
            foreach ($assignees as $index => $userId) {
                $user = \App\Models\User::find($userId);
                if (! $user || ! $room->hasMember($user)) {
                    $validator->errors()->add(
                        "assignees.{$index}",
                        "User is not a member of this room."
                    );
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'attachments.*.max' => 'Each attachment may not exceed 10 MB.',
            'attachments.*.mimes' => 'Attachments must be PDF, Office, image, or ZIP files.',
        ];
    }
}
