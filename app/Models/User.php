<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'user',
        'email',
        'password',
        'active',
        'is_admin',
        'empresa_id',
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'active' => 'boolean',
        'is_admin' => 'boolean',
        'email_verified_at' => 'datetime',
        'empresa_id' => 'integer',
    ];

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = [
        
    ];

    public function cashHistories()
    {
        return $this->hasMany(CashHistory::class);
    }

    /**
     * Obtener los egresos registrados por el usuario
     */
    public function egresos()
    {
        return $this->hasMany(Egreso::class);
    }

    /**
     * Obtener las asistencias del usuario
     */
    public function asistencias()
    {
        return $this->hasMany(Asistencia::class);
    }

    /**
     * Obtener la empresa del usuario
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }
}
