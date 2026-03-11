<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'project_id', 'created_by', 'assigned_to',
        'title', 'description', 'status',
        'complexity', 'priority_score', 'deadline'
    ];

    protected $casts = [
        'deadline'       => 'datetime',
        'priority_score' => 'float'
    ];

    public function project() {
        return $this->belongsTo(Project::class);
    }

    public function creator() {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function assignee() {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function categories() {
        return $this->belongsToMany(Category::class, 'task_category');
    }

    public function comments()
    {
    return $this->hasMany(Comment::class)->orderBy('created_at', 'asc');
    }

    public function priorityLogs() {
        return $this->hasMany(PriorityLog::class);
    }
}