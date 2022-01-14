<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Session;

/**
 * @property CustomerProduct $customerProduct
 */
class Domain extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * @var string[]
     */
    protected $fillable = [
        'registrar_id',
        'name'
    ];

    /**
     * @var string[]
     */
    protected $dates = [
        'created_at',
        'updated_at'
    ];

    /**
     * @return void
     */
    protected static function boot() {
        parent::boot();

        self::creating(function(Domain $domain) {
            unset($domain->user_id, $domain->ComputedField, $domain->price_confirmed);
        });


    }

    /**
     * @return BelongsTo
     */
    public function user() {
        if($this->customer) {
            return $this->customer()->first()->user();
        }
        return $this->belongsTo(User::class);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOneThrough
     */
    public function customer() {
        return $this->hasOneThrough( Customer::class, CustomerProduct::class,
            'domain_id', 'id', 'id', 'customer_id');
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function customerProduct() {
        return $this->hasOne(CustomerProduct::class);
    }
}
