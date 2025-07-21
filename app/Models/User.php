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
     * Obtener la empresa del usuario (relación original - una sola empresa)
     */
    public function empresa()
    {
        return $this->belongsTo(Empresa::class);
    }

    /**
     * Obtener las empresas del usuario (relación muchos a muchos)
     */
    public function empresas()
    {
        return $this->belongsToMany(Empresa::class, 'user_empresa', 'user_id', 'empresa_id')
                    ->withTimestamps();
    }

    /**
     * Obtener todas las empresas del usuario (principal + adicionales)
     */
    public function todasLasEmpresas()
    {
        $empresas = collect();
        
        // Agregar empresa principal si existe
        if ($this->empresa) {
            $empresas->push($this->empresa);
        }
        
        // Agregar empresas adicionales
        $empresasAdicionales = $this->empresas;
        
        // Combinar y remover duplicados por ID
        return $empresas->merge($empresasAdicionales)->unique('id');
    }

    /**
     * Verificar si el usuario tiene acceso a una empresa específica
     */
    public function tieneAccesoAEmpresa($empresaId)
    {
        // Verificar si es la empresa principal
        if ($this->empresa_id == $empresaId) {
            return true;
        }
        
        // Verificar si está en las empresas adicionales
        return $this->empresas()->where('empresa_id', $empresaId)->exists();
    }
}
