<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $table = 'products';
    protected $primaryKey = 'product_id';

    protected $fillable = [
        'category_id',
        'name',
        'description',
        'base_price',
        'img_url',
        'earning_points',
        'is_available',
    ];

    protected $casts = [
        'base_price'   => 'decimal:2',
        'is_available' => 'boolean',
    ];

    /**
     * Modifier yang dimiliki produk ini (via tabel pivot product_modifier).
     */
    public function modifiers()
    {
        return $this->belongsToMany(Modifier::class, 'product_modifier', 'product_id', 'modifier_id')
                    ->where('modifier.is_active', true)
                    ->orderBy('modifier.group_name')
                    ->orderBy('modifier.modifier_id');
    }
}