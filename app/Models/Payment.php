<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Payment extends Model
{
    protected $table = 'payment';
    protected $primaryKey = 'payment_id';

    protected $fillable = [
        'order_id',
        'payment_method',
        'payment_gateway',
        'transaction_id',
        'payment_url',
        'payment_response',
        'status',
        'amount',
        'amount_paid',
        'change_amount',
        'paid_at',
        'expired_at',
    ];

    protected $casts = [
        'payment_response' => 'array',
        'amount'           => 'float',
        'paid_at'          => 'datetime',
        'expired_at'       => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id', 'order_id');
    }
}
