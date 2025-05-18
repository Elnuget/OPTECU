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
        'valor',
        'user_id'
    ];

    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2'
    ];

    /**
     * Obtiene el usuario que registró el sueldo.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 