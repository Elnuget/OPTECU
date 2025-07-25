<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Receta extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'recetas';
    
    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    protected $fillable = [
        'historial_clinico_id',
        'od_esfera',
        'od_cilindro',
        'od_eje',
        'od_adicion',
        'oi_esfera',
        'oi_cilindro',
        'oi_eje',
        'oi_adicion',
        'dp',
        'observaciones',
        'tipo'
    ];

    /**
     * Obtiene el historial clínico asociado a esta receta
     */
    public function historialClinico()
    {
        return $this->belongsTo(HistorialClinico::class, 'historial_clinico_id');
    }
}
