<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modifier extends Model
{
    protected $table = 'modifier';
    protected $primaryKey = 'modifier_id';

    protected $fillable = [
        'name',
        'group_name',
        'selection_type',
        'extra_price',
        'type',
        'is_active',
    ];

    protected $casts = [
        'extra_price' => 'decimal:2',
        'is_active'   => 'boolean',
    ];

    /**
     * Produk-produk yang menggunakan modifier ini.
     */
    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_modifier', 'modifier_id', 'product_id');
    }
}
