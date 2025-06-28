<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class PedidoLuna extends Model
{
    use SoftDeletes;

    protected $table = 'pedido_lunas';
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'pedido_id',
        'l_medida',
        'l_detalle',
        'l_precio',
        'tipo_lente',
        'material',
        'filtro',
        'l_precio_descuento',
        'foto'
    ];

    protected $casts = [
        'l_precio' => 'float',
        'l_precio_descuento' => 'float'
    ];
    
    public function pedido()
    {
        return $this->belongsTo(Pedido::class);
    }
}
