<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pedido extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pedidos';

    // Especifica los campos que pueden ser asignados masivamente
    protected $fillable = [
        'empresa_id',
        'fecha',
        'numero_orden',
        'fact',
        'examen_visual',
        'cliente',
        'cedula',     // Agregar este campo
        'paciente', // New field
        'celular',
        'correo_electronico',
        'direccion',
        // Remover estos campos ya que ahora van en la tabla pedido_lunas
        // 'l_detalle',
        // 'l_medida',
        // 'l_precio',
        'd_inventario_id',
        'd_precio',
        'total',
        'saldo',
        // Nuevos campos
        'tipo_lente',
        'material',
        'filtro',
        'valor_compra',
        'motivo_compra',
        'usuario', // ...added usuario...
        'calificacion',
        'comentario_calificacion'
    ];

    protected $dates = ['deleted_at', 'fecha'];

    protected $casts = [
        'fecha' => 'datetime',
    ];

    // Define si tu modelo debe usar timestamps (created_at y updated_at)
    public $timestamps = true;

    // Relación con el modelo Empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Relación con el modelo Inventario para 'a_inventario'
    public function aInventario()
    {
        return $this->belongsTo(Inventario::class, 'a_inventario_id');
    }

    // Relación con el modelo Inventario para 'd_inventario'
    public function dInventario()
    {
        return $this->belongsTo(Inventario::class, 'd_inventario_id');
    }

    public function inventarios()
    {
        return $this->belongsToMany(Inventario::class, 'pedido_inventario')
                    ->using(PedidoInventario::class)
                    ->withPivot(['precio', 'descuento', 'foto'])
                    ->withTimestamps();
    }

    // Add this relationship to the existing Pedido model
    public function lunas()
    {
        return $this->hasMany(PedidoLuna::class);
    }

    public function pagos()
    {
        return $this->hasMany(Pago::class);
    }

    protected static function boot()
    {
        parent::boot();

        // Antes de eliminar el pedido, eliminar los pagos asociados
        static::deleting(function($pedido) {
            $pedido->pagos()->delete();
        });
    }
}