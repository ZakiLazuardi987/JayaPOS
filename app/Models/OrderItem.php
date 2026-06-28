<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    protected $table = 'order_items';
    protected $primaryKey = 'order_item_id';
    public $timestamps = false;

    protected $fillable = [
        'order_id',
        'product_id',
        'quantity',
        'price_at_purchase',
        'points_earned_per_item',
        'parent_item_id',
        'created_at',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }

    public function modifiers()
    {
        return $this->belongsToMany(Modifier::class, 'order_item_modifier', 'order_item_id', 'modifier_id')
                    ->withPivot('price_added');
    }
}
