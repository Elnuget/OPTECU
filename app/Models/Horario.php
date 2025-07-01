<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Horario extends Model
{
    use HasFactory;

    protected $table = 'horarios';

    protected $fillable = [
        'hora_entrada',
        'hora_salida',
        'empresa_id',
    ];

    protected $casts = [
        'hora_entrada' => 'datetime:H:i',
        'hora_salida' => 'datetime:H:i',
    ];

    /**
     * Relación con la empresa
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Scope para filtrar por empresa
     */
    public function scopeByEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    /**
     * Accessor para obtener la duración del horario
     */
    public function getDuracionAttribute()
    {
        $entrada = \Carbon\Carbon::parse($this->hora_entrada);
        $salida = \Carbon\Carbon::parse($this->hora_salida);
        
        return $entrada->diffInHours($salida);
    }

    /**
     * Verificar si el horario está activo en este momento
     */
    public function isActivoAhora()
    {
        $horaActual = now()->format('H:i:s');
        return $horaActual >= $this->hora_entrada && $horaActual <= $this->hora_salida;
    }
}
