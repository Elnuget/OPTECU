<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleSueldo extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'detalles_sueldos';

    protected $fillable = [
        'user_id',
        'mes',
        'ano',
        'descripcion',
        'valor'
    ];

    protected $casts = [
        'mes' => 'string',
        'ano' => 'integer',
        'valor' => 'decimal:2'
    ];

    /**
     * Obtiene el usuario asociado al detalle de sueldo
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 