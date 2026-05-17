<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Favorite extends Model
{
    protected $table = 'favorite';
    protected $primaryKey = 'favorite_id';
    public $timestamps = false; // Karena hanya pakai created_at di tabel

    protected $fillable = ['outlet_id', 'product_id', 'created_at'];

    // Relasi: 1 Favorit punya 1 Produk
    public function product()
    {
        return $this->belongsTo(Product::class, 'product_id', 'product_id');
    }
}