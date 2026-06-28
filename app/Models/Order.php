<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $table = 'orders';
    protected $primaryKey = 'order_id';
    
    // Disable Laravel default timestamps if not in the DB, 
    // but the DB has created_at without updated_at.
    // Let's check: DESCRIBE orders has created_at but no updated_at.
    // Let's set public $timestamps = false to avoid errors.
    public $timestamps = false;

    protected $fillable = [
        'member_id',
        'customer_id',
        'staff_id',
        'outlet_id',
        'source',
        'order_type',
        'table_number',
        'table_id',
        'pax',
        'waiter_id',
        'status',
        'pickup_code',
        'subtotal',
        'tax_id',
        'service_charge_id',
        'discount_id',
        'discount_amount',
        'total_final',
        'points_earned',
        'created_at',
    ];

    public function items()
    {
        return $this->hasMany(OrderItem::class, 'order_id', 'order_id');
    }

    public function table()
    {
        return $this->belongsTo(Table::class, 'table_id', 'table_id');
    }

    public function waiter()
    {
        return $this->belongsTo(Staff::class, 'waiter_id', 'staff_id');
    }

    public function payment()
    {
        return $this->hasOne(Payment::class, 'order_id', 'order_id');
    }

    public function tax()
    {
        return $this->belongsTo(Tax::class, 'tax_id', 'tax_id');
    }

    public function serviceCharge()
    {
        return $this->belongsTo(ServiceCharge::class, 'service_charge_id', 'service_charge_id');
    }
}
