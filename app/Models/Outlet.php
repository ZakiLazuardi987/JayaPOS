<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    // Sesuaikan dengan nama tabel di database
    protected $table = 'outlet';
    
    // Sesuaikan primary key-nya
    protected $primaryKey = 'outlet_id';

    protected $fillable = [
        'name', 'address', 'latitude', 'longitude', 'phone', 'status'
    ];
}