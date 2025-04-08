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
     * Asegura que el valor se guarde con formato correcto
     */
    public function setValorAttribute($value)
    {
        $this->attributes['valor'] = number_format((float)$value, 2, '.', '');
    }
} 