<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $currency
 */
class DomainProduct extends Model
{
    use HasFactory;

    /**
     * @var string[]
     */
    protected $fillable = [
        'tld',
        'currency',
        'promo',
        'promo_price',
        'promo_type',
        'period',
        'reg_price',
        'renewal_price',
        'update_price',
        'restore_price',
        'transfer_price',
        'trade_price',
        'whois_protection_price'
    ];

    /**
     * @var string[]
     */
    protected $casts = [
        'promo' => 'boolean',
        'reg_price' => 'double',
        'renewal_price' => 'double',
        'update_price' => 'double',
        'restore_price' => 'double',
        'transfer_price' => 'double',
        'trade_price' => 'double'
    ];
}
