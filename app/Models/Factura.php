<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Factura extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'facturas';

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'pedido_id',
        'declarante_id',
        'xml',
        'monto',
        'iva',
        'tipo',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'monto' => 'decimal:2',
        'iva' => 'decimal:2',
    ];

    /**
     * Relación con el modelo Pedido.
     * Una factura pertenece a un pedido.
     */
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * Relación con el modelo Declarante.
     * Una factura pertenece a un declarante.
     */
    public function declarante()
    {
        return $this->belongsTo(Declarante::class, 'declarante_id');
    }

    /**
     * Accessor para obtener el total de la factura (monto + iva).
     */
    public function getTotalAttribute()
    {
        return $this->monto + $this->iva;
    }

    /**
     * Scope para buscar facturas por pedido.
     */
    public function scopeByPedido($query, $pedidoId)
    {
        return $query->where('pedido_id', $pedidoId);
    }

    /**
     * Scope para buscar facturas por declarante.
     */
    public function scopeByDeclarante($query, $declaranteId)
    {
        return $query->where('declarante_id', $declaranteId);
    }
}
