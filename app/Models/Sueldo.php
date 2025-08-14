<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Sueldo extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'sueldos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'empresa_id',
        'fecha',
        'descripcion',
        'valor',
        'documento'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'fecha' => 'date',
        'valor' => 'decimal:2'
    ];

    /**
     * Get the user that owns the sueldo.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the empresa that owns the sueldo.
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Get the detalles for the sueldo.
     * Relación con detalles_sueldos si es necesaria
     */
    public function detalles()
    {
        return $this->hasMany(DetalleSueldo::class, 'user_id', 'user_id');
    }
}
