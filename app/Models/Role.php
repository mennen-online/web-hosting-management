<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    use HasFactory;

    protected $fillable = [
        'name'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function scopeByName(Builder $builder, string $name) {
        return $builder->where('name', $name)->first();
    }

    public function permissions() {
        return $this->belongsToMany(Permission::class, 'roles_permissions');
    }
}
