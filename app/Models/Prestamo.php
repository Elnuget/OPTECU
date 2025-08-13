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
        'empresa_id',
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

    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    public function pagos()
    {
        return $this->hasMany(PagoPrestamo::class);
    }

    // Scopes
    public function scopePorEmpresa($query, $empresaId)
    {
        return $query->where('empresa_id', $empresaId);
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
    
    public function getValorCuotaAttribute()
    {
        if ($this->cuotas <= 0) {
            return $this->valor_neto;
        }
        
        // Cálculo básico: valor neto dividido por número de cuotas
        $valorCuotaBase = $this->valor_neto / $this->cuotas;
        
        // Para la última cuota, devolver el saldo pendiente exacto
        $cuotasPendientes = $this->cuotas_pendientes;
        
        if ($cuotasPendientes == 1) {
            return $this->saldo_pendiente;
        }
        
        return round($valorCuotaBase, 2);
    }
} 