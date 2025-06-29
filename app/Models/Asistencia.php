<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Asistencia extends Model
{
    use HasFactory;

    /**
     * La tabla asociada con el modelo.
     *
     * @var string
     */
    protected $table = 'asistencias';

    /**
     * Los atributos que son asignables en masa.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'fecha_hora',
        'hora_entrada',
        'hora_salida',
        'estado',
    ];

    /**
     * Los atributos que deben ser convertidos a tipos nativos.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'fecha_hora' => 'datetime',
        'hora_entrada' => 'datetime',
        'hora_salida' => 'datetime',
    ];

    /**
     * Valores por defecto para los atributos del modelo.
     *
     * @var array
     */
    protected $attributes = [
        'estado' => 'presente',
    ];

    /**
     * Relación con el modelo User.
     * Una asistencia pertenece a un usuario.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Scope para filtrar asistencias por estado.
     */
    public function scopeByEstado($query, $estado)
    {
        return $query->where('estado', $estado);
    }

    /**
     * Scope para filtrar asistencias por fecha.
     */
    public function scopeByFecha($query, $fecha)
    {
        return $query->whereDate('fecha_hora', $fecha);
    }

    /**
     * Scope para filtrar asistencias por usuario.
     */
    public function scopeByUser($query, $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     * Accessor para obtener el estado formateado.
     */
    public function getEstadoFormateadoAttribute()
    {
        $estados = [
            'presente' => 'Presente',
            'ausente' => 'Ausente',
            'tardanza' => 'Tardanza'
        ];

        return $estados[$this->estado] ?? $this->estado;
    }

    /**
     * Verificar si el usuario llegó tarde.
     */
    public function esLlegadaTarde($horaLimite = '08:00:00')
    {
        if (!$this->hora_entrada) {
            return false;
        }

        return $this->hora_entrada->format('H:i:s') > $horaLimite;
    }

    /**
     * Calcular horas trabajadas.
     */
    public function getHorasTrabajadasAttribute()
    {
        if (!$this->hora_entrada || !$this->hora_salida) {
            return null;
        }

        $entrada = $this->hora_entrada;
        $salida = $this->hora_salida;

        return $entrada->diff($salida)->format('%H:%I');
    }
}
