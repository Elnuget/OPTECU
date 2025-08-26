<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Declarante extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'declarante';

    protected $fillable = [
        'nombre',
        'ruc',
        'firma',
        'direccion_matriz',
        'establecimiento',
        'punto_emision',
        'obligado_contabilidad'
    ];

    protected $casts = [
        'obligado_contabilidad' => 'boolean',
    ];

    protected $dates = ['deleted_at'];

    /**
     * Obtener el texto de obligado a llevar contabilidad
     */
    public function getObligadoContabilidadTextoAttribute()
    {
        return $this->obligado_contabilidad ? 'SI' : 'NO';
    }

    /**
     * Verificar si el declarante tiene certificado digital P12/PFX
     */
    public function tieneCertificadoAttribute()
    {
        return !empty($this->firma) && file_exists(public_path('uploads/firmas/' . $this->firma));
    }

    /**
     * Verificar si el declarante tiene certificado P12 (método principal)
     */
    public function tieneCertificadoP12Attribute()
    {
        if (empty($this->firma)) {
            return false;
        }
        
        $extension = strtolower(pathinfo($this->firma, PATHINFO_EXTENSION));
        return in_array($extension, ['p12', 'pfx']) && file_exists(public_path('uploads/firmas/' . $this->firma));
    }

    /**
     * Obtener el tipo de certificado (siempre P12 ahora)
     */
    public function getTipoCertificadoAttribute()
    {
        if (empty($this->firma)) {
            return 'ninguno';
        }
        
        $extension = strtolower(pathinfo($this->firma, PATHINFO_EXTENSION));
        
        if (in_array($extension, ['p12', 'pfx'])) {
            return 'p12';
        }
        
        return 'desconocido';
    }

    /**
     * Obtener la ruta completa del certificado PEM
     */
    public function getRutaCertificadoAttribute()
    {
        if ($this->firma) {
            return public_path('uploads/firmas/' . $this->firma);
        }
        return null;
    }

    // Los métodos relacionados con el secuencial han sido eliminados
}
