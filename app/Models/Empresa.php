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
        if (empty($this->nombre) || $this->nombre === 'MATRIZ') {
            return 'todas';
        } elseif ($this->nombre === 'EL ROCIO') {
            return 'rocio';
        } elseif ($this->nombre === 'NORTE WENDY') {
            return 'norte';
        }
        return 'todas';
    }
}
