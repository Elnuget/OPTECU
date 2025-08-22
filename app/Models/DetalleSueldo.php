<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class DetalleSueldo extends Model
{
    use HasFactory, SoftDeletes;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'detalles_sueldos';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id',
        'mes',
        'ano',
        'descripcion',
        'valor'
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'valor' => 'decimal:2',
        'ano' => 'integer',
        'mes' => 'integer'
    ];

    /**
     * Get the user that owns the detalle de sueldo.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the sueldo that owns the detalle (if needed).
     * Esta relación es opcional dependiendo de si existe una relación directa 
     * entre detalle_sueldo y sueldo.
     */
    public function sueldo()
    {
        return $this->belongsTo(Sueldo::class);
    }
}
