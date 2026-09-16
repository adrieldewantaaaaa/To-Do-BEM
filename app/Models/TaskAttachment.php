<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskAttachment extends Model
{
    use HasFactory;

    protected $fillable = ['original_name', 'file_name', 'file_path', 'mime_type', 'file_size'];

    protected $appends = ['human_size'];

    public function task(): BelongsTo
    {
        return $this->belongsTo(Task::class);
    }

    public function getHumanSizeAttribute(): string
    {
        return $this->file_size >= 1048576 ? number_format($this->file_size / 1048576, 1).' MB' : number_format($this->file_size / 1024, 0).' KB';
    }
}
