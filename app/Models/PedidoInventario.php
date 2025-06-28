<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoInventario extends Pivot
{
    use SoftDeletes;

    protected $table = 'pedido_inventario';
    
    protected $fillable = [
        'pedido_id',
        'inventario_id',
        'precio',
        'descuento',
        'foto'
    ];
    
    protected $dates = ['deleted_at'];

    public $timestamps = true;
} 