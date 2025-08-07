<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prestamo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'valor',
        'valor_neto',
        'cuotas',
        'motivo'
    ];

    protected $casts = [
        'valor' => 'decimal:2',
        'valor_neto' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function pagos()
    {
        return $this->hasMany(PagoPrestamo::class);
    }

    // Métodos de cálculo
    public function getTotalPagadoAttribute()
    {
        return $this->pagos()->pagados()->sum('valor');
    }

    public function getSaldoPendienteAttribute()
    {
        return $this->valor_neto - $this->total_pagado;
    }

    public function getCuotasPagadasAttribute()
    {
        return $this->pagos()->pagados()->count();
    }

    public function getCuotasPendientesAttribute()
    {
        return max(0, $this->cuotas - $this->cuotas_pagadas);
    }

    public function getEstadoPrestamoAttribute()
    {
        $totalPagado = $this->total_pagado;
        
        if ($totalPagado >= $this->valor_neto) {
            return 'pagado';
        } elseif ($totalPagado > 0) {
            return 'parcial';
        } else {
            return 'pendiente';
        }
    }
} 