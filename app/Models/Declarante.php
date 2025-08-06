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
        'firma'
    ];

    protected $dates = ['deleted_at'];
}
