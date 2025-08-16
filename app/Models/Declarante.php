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
        'obligado_contabilidad',
        'secuencial'
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

    /**
     * Generar el siguiente secuencial
     */
    public function generarSiguienteSecuencial()
    {
        $ultimoSecuencial = (int) $this->secuencial;
        $nuevoSecuencial = $ultimoSecuencial + 1;
        return str_pad($nuevoSecuencial, 9, '0', STR_PAD_LEFT);
    }

    /**
     * Incrementar el secuencial y guardarlo
     */
    public function incrementarSecuencial()
    {
        $this->secuencial = $this->generarSiguienteSecuencial();
        $this->save();
        return $this->secuencial;
    }
}
