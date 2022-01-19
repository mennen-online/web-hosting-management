<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'task_topic_id',
        'taskable_type',
        'taskable_id',
        'title',
        'content',
        'to_do_by'
    ];

    public function topic() {
        return $this->belongsTo(TaskTopic::class);
    }

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function taskable() {
        return $this->morphTo();
    }
}
