<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Inventario extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'id',
        'fecha',
        'lugar',
        // 'numero_lugar', // removed
        'columna', // renamed from fila
        'numero',
        'codigo',
        'valor',
        'cantidad',
        'orden',
        'empresa_id'
    ];

    protected $dates = ['deleted_at'];

    public $timestamps = true;

    // Relación con el modelo Pedido para 'a_inventario'
    public function pedidosA()
    {
        return $this->hasMany(Pedido::class, 'a_inventario_id');
    }

    // Relación con el modelo Pedido para 'd_inventario'
    public function pedidosD()
    {
        return $this->hasMany(Pedido::class, 'd_inventario_id');
    }

    public function pedidos()
    {
        return $this->belongsToMany(Pedido::class, 'pedido_inventario')
                    ->using(PedidoInventario::class)
                    ->withPivot(['precio', 'descuento'])
                    ->withTimestamps();
    }
    
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
