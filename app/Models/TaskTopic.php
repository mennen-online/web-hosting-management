<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TaskTopic extends Model
{
    use HasFactory;

    protected $fillable = [
        'title'
    ];

    public function task() {
        return $this->hasMany(Task::class);
    }
}
