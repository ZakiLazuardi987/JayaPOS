<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Area extends Model
{
    use HasFactory;

    protected $table = 'areas';
    protected $primaryKey = 'area_id';

    protected $fillable = [
        'outlet_id',
        'name',
    ];

    public function tables()
    {
        return $this->hasMany(Table::class, 'area_id', 'area_id');
    }
}
