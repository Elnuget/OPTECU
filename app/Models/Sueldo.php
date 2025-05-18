<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sueldo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'sueldos';
    
    protected $fillable = [
        'fecha',
        'descripcion',
        'valor'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2'
    ];
} 