<?php

namespace App\Models;

use App\Services\Forge\Endpoints\ServersEndpoint;
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

    protected static function boot() {
        parent::boot();

        self::creating(function(Server $server){
            $server->forge_id = app()->make(ServersEndpoint::class)->create()->server->id;
        });
    }

    public function customerProduct() {
        return $this->hasMany(CustomerProduct::class);
    }
}
