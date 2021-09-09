<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    use HasFactory;

    protected $fillable = [
        'forge_id'
    ];

    protected $dates = [
        'created_at',
        'updated_at'
    ];

    public function customerProduct() {
        return $this->hasMany(CustomerProduct::class);
    }
}
