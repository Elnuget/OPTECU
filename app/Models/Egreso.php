<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Egreso extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'egresos';
    
    protected $fillable = [
        'user_id',
        'empresa_id',
        'valor',
        'motivo'
    ];

    protected $dates = ['deleted_at'];

    protected $casts = [
        'valor' => 'decimal:2'
    ];

    /**
     * Obtener el usuario que registró el egreso
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Obtener la empresa del egreso
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Asegura que el valor se guarde con formato correcto
     */
    public function setValorAttribute($value)
    {
        // Eliminar cualquier formato de miles y convertir a float
        $cleanValue = str_replace([',', '.'], ['', '.'], $value);
        $this->attributes['valor'] = (float)$cleanValue;
    }

    /**
     * Accessor para mostrar el valor con el formato correcto
     */
    public function getValorFormateadoAttribute()
    {
        return number_format((float)$this->valor, 2, '.', ',');
    }
} 