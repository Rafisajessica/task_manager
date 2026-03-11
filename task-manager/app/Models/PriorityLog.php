<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PriorityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'task_id', 'old_score', 'new_score', 'reason'
    ];

    protected $casts = [
        'reason'    => 'array',
        'old_score' => 'float',
        'new_score' => 'float'
    ];

    public function task() {
        return $this->belongsTo(Task::class);
    }
}