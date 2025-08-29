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
        'xml_firmado',
        'xml_autorizado',
        'monto',
        'iva',
        'tipo',
        'estado',
        'estado_sri',
        'numero_autorizacion',
        'fecha_autorizacion',
        'mensajes_sri',
        'fecha_firma',
        'fecha_envio_sri',
        'clave_acceso',
        'observaciones'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'monto' => 'float',
        'iva' => 'float',
        'fecha_autorizacion' => 'datetime',
        'fecha_firma' => 'datetime',
        'fecha_envio_sri' => 'datetime',
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
     * Accessor para la fecha de autorización - solo devolver si está realmente autorizada
     */
    public function getFechaAutorizacionAttribute($value)
    {
        // Solo devolver la fecha si la factura está realmente autorizada
        if ($this->estado === 'AUTORIZADA' && $value) {
            return $this->asDateTime($value);
        }
        return null;
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

    /**
     * Obtener el XML más apropiado según el estado de la factura
     * Prioridad: XML Autorizado > XML Firmado > XML Original
     * MODIFICADO: Mostrar XML autorizado cuando esté disponible
     */
    public function getXmlContent()
    {
        // Prioridad 1: Si hay XML autorizado, usar siempre
        if (!empty($this->xml_autorizado)) {
            return $this->xml_autorizado;
        }
        
        // Prioridad 2: Si hay XML firmado
        if (!empty($this->xml_firmado)) {
            return $this->xml_firmado;
        }
        
        // Prioridad 3: Por defecto, usar XML original
        return $this->xml;
    }

    /**
     * Obtener el tipo de XML que se está mostrando
     * MODIFICADO: Mostrar tipo autorizado cuando esté disponible
     */
    public function getXmlType()
    {
        // Prioridad 1: Si hay XML autorizado, mostrar como autorizado
        if (!empty($this->xml_autorizado)) {
            return 'autorizado';
        }
        
        // Prioridad 2: Si hay XML firmado, mostrar como firmado
        if (!empty($this->xml_firmado)) {
            return 'firmado';
        }
        
        // Prioridad 3: Por defecto, original
        return 'original';
    }

    /**
     * Guardar XML firmado en la base de datos
     */
    public function guardarXmlFirmado($xmlContent)
    {
        $this->xml_firmado = $xmlContent;
        $this->fecha_firma = now();
        
        // Actualizar estado si aún está en CREADA
        if ($this->estado === 'CREADA') {
            $this->estado = 'FIRMADA';
        }
        
        return $this->save();
    }

    /**
     * Guardar XML autorizado en la base de datos
     */
    public function guardarXmlAutorizado($xmlContent)
    {
        $this->xml_autorizado = $xmlContent;
        $this->fecha_autorizacion = now();
        $this->estado = 'AUTORIZADA';
        
        return $this->save();
    }
}
