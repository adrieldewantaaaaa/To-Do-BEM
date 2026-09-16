<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class JoinRoomRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'invite_code' => ['required', 'string', 'max:20'],
        ];
    }

    public function prepareForValidation(): void
    {
        $this->merge([
            'invite_code' => strtoupper(trim((string) $this->input('invite_code'))),
        ]);
    }
}
