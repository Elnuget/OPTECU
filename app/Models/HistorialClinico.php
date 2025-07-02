<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class HistorialClinico extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'historiales_clinicos';
    protected $dates = [
        'deleted_at',
        'fecha',
        'fecha_nacimiento',
        'proxima_consulta'
    ];

    protected $fillable = [
        'empresa_id',
        'fecha',
        'nombres',
        'apellidos',
        'cedula',
        'edad',
        'fecha_nacimiento',
        'celular',
        'ocupacion',
        'motivo_consulta',
        'enfermedad_actual',
        'antecedentes_personales_oculares',
        'antecedentes_personales_generales',
        'antecedentes_familiares_oculares',
        'antecedentes_familiares_generales',
        'agudeza_visual_vl_sin_correccion_od',
        'agudeza_visual_vl_sin_correccion_oi',
        'agudeza_visual_vl_sin_correccion_ao',
        'agudeza_visual_vp_sin_correccion_od',
        'agudeza_visual_vp_sin_correccion_oi',
        'agudeza_visual_vp_sin_correccion_ao',
        'ph_od',
        'ph_oi',
        'add',
        'optotipo',
        'lensometria_od',
        'lensometria_oi',
        'tipo_lente',
        'material',
        'filtro',
        'tiempo_uso',
        'refraccion_od',
        'refraccion_oi',
        'rx_final_dp_od',
        'rx_final_dp_oi',
        'rx_final_av_vl_od',
        'rx_final_av_vl_oi',
        'rx_final_av_vp_od',
        'rx_final_av_vp_oi',
        'diagnostico',
        'tratamiento',
        'proxima_consulta',
        'cotizacion',
        'usuario_id'
    ];

    // Definir la relación con el modelo Empresa
    public function empresa()
    {
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }

    // Definir la relación con el modelo User
    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
