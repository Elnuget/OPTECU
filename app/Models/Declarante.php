<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Declarante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'declarante';

    protected $fillable = [
        'nombre',
        'ruc',
        'firma',
        'direccion_matriz',
        'establecimiento',
        'punto_emision',
        'obligado_contabilidad'
    ];

    protected $casts = [
        'obligado_contabilidad' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Obtener el texto de obligado a llevar contabilidad
     */
    public function getObligadoContabilidadTextoAttribute()
    {
        return $this->obligado_contabilidad ? 'SI' : 'NO';
    }

    // Los métodos relacionados con el secuencial han sido eliminados
}
