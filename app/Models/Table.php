<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Table extends Model
{
    use HasFactory;

    protected $table = 'tables';
    protected $primaryKey = 'table_id';

    protected $fillable = [
        'area_id',
        'name',
        'capacity',
        'status',
    ];

    public function area()
    {
        return $this->belongsTo(Area::class, 'area_id', 'area_id');
    }
}
