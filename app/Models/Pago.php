<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Pago extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'pagos'; // Ensure the model uses the 'pagos' table
    protected $dates = ['deleted_at'];

    protected $fillable = [
        'pedido_id',
        'mediodepago_id',
        'pago',
        'created_at',
        'TC',
        'foto'
    ];

    protected $casts = [
        'pago' => 'decimal:2',
        'TC' => 'boolean'
    ];

    public function setPagoAttribute($value)
    {
        $this->attributes['pago'] = number_format((float)$value, 2, '.', '');
    }

    public function mediodepago()
    {
        return $this->belongsTo(mediosdepago::class);
    }

    public function pedido()
    {
        return $this->belongsTo(Pedido::class)->withTrashed();
    }

    public function scopeValidPayments($query)
    {
        return $query->whereHas('pedido', function($q) {
            $q->whereNotNull('id');
        });
    }
}
