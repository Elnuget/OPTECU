<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prestamo extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'valor',
        'motivo'
    ];

    protected $casts = [
        'valor' => 'decimal:2'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 