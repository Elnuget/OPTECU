<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Empresa extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'correo'
    ];

    public function getTipoSucursal()
    {
        if (empty($this->nombre) || $this->nombre === 'Matriz') {
            return 'todas';
        } elseif ($this->nombre === 'EL ROCIO') {
            return 'rocio';
        } elseif ($this->nombre === 'NORTE WENDY') {
            return 'norte';
        }
        return 'todas';
    }

    /**
     * Obtener los usuarios de la empresa
     */
    public function usuarios()
    {
        return $this->hasMany(User::class);
    }

    /**
     * Obtener los horarios de la empresa
     */
    public function horarios()
    {
        return $this->hasMany(Horario::class);
    }

    /**
     * Obtener el horario principal de la empresa
     */
    public function horario()
    {
        return $this->hasOne(Horario::class);
    }

    /**
     * Obtener los historiales de caja de la empresa
     */
    public function cashHistories()
    {
        return $this->hasMany(CashHistory::class);
    }
    
    /**
     * Obtener los inventarios de la empresa
     */
    public function inventarios()
    {
        return $this->hasMany(Inventario::class);
    }
}
