<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MensajesEnviados extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'mensajes_enviados';

    protected $fillable = [
        'historial_id',
        'pedido_id',
        'tipo',
        'tipo_mensaje',
        'mensaje',
        'fecha_envio',
        'usuario_id',
        'empresa_id'
    ];

    protected $dates = [
        'fecha_envio',
        'created_at',
        'updated_at',
        'deleted_at'
    ];

    // Relación con HistorialClinico
    public function historialClinico()
    {
        return $this->belongsTo(HistorialClinico::class, 'historial_id');
    }

    // Relación con Pedido
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    // Relación con Usuario
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    // Relación con Empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
} 