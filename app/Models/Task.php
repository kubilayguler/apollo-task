<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    //
    protected $fillable = ['title', 'content', 'priority', 'status', 'due_date', 'project_id'];
    
    protected $casts = [
        'due_date' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function project() {
        return $this->belongsTo(Project::class);
    }
}
