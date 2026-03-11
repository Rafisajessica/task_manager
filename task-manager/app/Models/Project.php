<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Project extends Model
{
    protected $fillable = [
    'owner_id', 'name', 'description',
    'status', 'priority_level', 'start_date', 'end_date'
    ];
    protected $casts = [
        'start_date' => 'date',
        'end_date'   => 'date'
];

public function owner() {
    return $this->belongsTo(User::class, 'owner_id');
}
public function tasks() {
    return $this->hasMany(Task::class);
}
}
