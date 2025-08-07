<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PagoPrestamo extends Model
{
    use HasFactory;

    protected $table = 'pago_prestamos';

    protected $fillable = [
        'prestamo_id',
        'empresa_id',
        'user_id',
        'valor',
        'fecha_pago',
        'motivo',
        'observaciones',
        'estado'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'fecha_pago' => 'date'
    ];

    // Relaciones
    public function prestamo()
    {
        return $this->belongsTo(Prestamo::class);
    }

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Scopes
    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
    }

    public function scopePorFecha($query, $fecha)
    {
        return $query->whereDate('fecha_pago', $fecha);
    }

    public function scopePorPeriodo($query, $ano, $mes = null)
    {
        $query->whereYear('fecha_pago', $ano);
        
        if ($mes) {
            $query->whereMonth('fecha_pago', $mes);
        }
        
        return $query;
    }

    public function scopePagados($query)
    {
        return $query->where('estado', 'pagado');
    }
}
