<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SecuencialSri extends Model
{
    use HasFactory;
    
    protected $table = 'secuenciales_sri';
    
    protected $fillable = [
        'secuencial',
        'clave_acceso',
        'establecimiento',
        'punto_emision',
        'ruc',
        'estado',
        'factura_id',
        'fecha_emision',
        'metadata'
    ];
    
    protected $casts = [
        'fecha_emision' => 'date',
        'metadata' => 'array'
    ];
    
    /**
     * Relación con la factura
     */
    public function factura()
    {
        return $this->belongsTo(Factura::class);
    }
    
    /**
     * Verificar si un secuencial ya está usado
     */
    public static function secuencialEnUso($secuencial, $ruc, $establecimiento, $puntoEmision)
    {
        return self::where('secuencial', $secuencial)
                   ->where('ruc', $ruc)
                   ->where('establecimiento', $establecimiento)
                   ->where('punto_emision', $puntoEmision)
                   ->exists();
    }
    
    /**
     * Generar próximo secuencial disponible
     */
    public static function generarProximoSecuencial($ruc, $establecimiento, $puntoEmision)
    {
        DB::beginTransaction();
        try {
            // Obtener el último secuencial usado para este punto de emisión
            $ultimoSecuencial = self::where('ruc', $ruc)
                                  ->where('establecimiento', $establecimiento)
                                  ->where('punto_emision', $puntoEmision)
                                  ->orderBy('secuencial', 'desc')
                                  ->first();
            
            if ($ultimoSecuencial) {
                $proximoNumero = intval($ultimoSecuencial->secuencial) + 1;
            } else {
                $proximoNumero = 1;
            }
            
            // Asegurar que tenga 9 dígitos
            $proximoSecuencial = str_pad($proximoNumero, 9, '0', STR_PAD_LEFT);
            
            // Verificar que no esté en uso (por seguridad)
            while (self::secuencialEnUso($proximoSecuencial, $ruc, $establecimiento, $puntoEmision)) {
                $proximoNumero++;
                $proximoSecuencial = str_pad($proximoNumero, 9, '0', STR_PAD_LEFT);
                
                // Evitar bucle infinito
                if ($proximoNumero > 999999999) {
                    throw new \Exception('Se agotaron los números secuenciales disponibles');
                }
            }
            
            DB::commit();
            return $proximoSecuencial;
            
        } catch (\Exception $e) {
            DB::rollback();
            throw $e;
        }
    }
    
    /**
     * Registrar secuencial usado
     */
    public static function registrarSecuencial($datos)
    {
        try {
            return self::create([
                'secuencial' => $datos['secuencial'],
                'clave_acceso' => $datos['clave_acceso'] ?? null,
                'establecimiento' => $datos['establecimiento'],
                'punto_emision' => $datos['punto_emision'],
                'ruc' => $datos['ruc'],
                'estado' => $datos['estado'] ?? 'USADO',
                'factura_id' => $datos['factura_id'] ?? null,
                'fecha_emision' => $datos['fecha_emision'] ?? now()->toDateString(),
                'metadata' => $datos['metadata'] ?? null
            ]);
        } catch (\Exception $e) {
            Log::error('Error registrando secuencial SRI', [
                'datos' => $datos,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
    
    /**
     * Marcar secuencial como devuelto por SRI
     */
    public function marcarComoDevuelta($motivo = null)
    {
        $this->estado = 'DEVUELTA';
        if ($motivo) {
            $metadata = $this->metadata ?? [];
            $metadata['motivo_devolucion'] = $motivo;
            $metadata['fecha_devolucion'] = now()->toISOString();
            $this->metadata = $metadata;
        }
        $this->save();
        
        Log::info('Secuencial marcado como devuelto', [
            'secuencial' => $this->secuencial,
            'clave_acceso' => $this->clave_acceso,
            'motivo' => $motivo
        ]);
    }
    
    /**
     * Marcar secuencial como autorizado por SRI
     */
    public function marcarComoAutorizada($numeroAutorizacion = null)
    {
        $this->estado = 'AUTORIZADA';
        if ($numeroAutorizacion) {
            $metadata = $this->metadata ?? [];
            $metadata['numero_autorizacion'] = $numeroAutorizacion;
            $metadata['fecha_autorizacion'] = now()->toISOString();
            $this->metadata = $metadata;
        }
        $this->save();
        
        Log::info('Secuencial marcado como autorizado', [
            'secuencial' => $this->secuencial,
            'clave_acceso' => $this->clave_acceso,
            'numero_autorizacion' => $numeroAutorizacion
        ]);
    }
}
