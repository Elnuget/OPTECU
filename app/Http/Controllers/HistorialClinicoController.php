<?php

namespace App\Http\Controllers;

use App\Models\HistorialClinico;
use App\Models\MensajesEnviados;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class HistorialClinicoController extends Controller
{
    public function index(Request $request)
    {
        // Iniciar la consulta con la relación usuario
        $query = HistorialClinico::with('usuario');

        // Aplicar filtros solo si se proporcionan mes y año
        if ($request->filled('mes') && $request->filled('ano')) {
            $query->whereYear('fecha', $request->get('ano'))
                  ->whereMonth('fecha', $request->get('mes'));
        }

        // Obtener los historiales
        $historiales = $query->get();

        return view('historiales_clinicos.index', compact('historiales'));
    }

    public function create()
    {
        // Obtener antecedentes únicos
        $antecedentesPersonalesOculares = HistorialClinico::select('antecedentes_personales_oculares')
            ->whereNotNull('antecedentes_personales_oculares')
            ->distinct()
            ->pluck('antecedentes_personales_oculares');

        $antecedentesPersonalesGenerales = HistorialClinico::select('antecedentes_personales_generales')
            ->whereNotNull('antecedentes_personales_generales')
            ->distinct()
            ->pluck('antecedentes_personales_generales');

        $antecedentesFamiliaresOculares = HistorialClinico::select('antecedentes_familiares_oculares')
            ->whereNotNull('antecedentes_familiares_oculares')
            ->distinct()
            ->pluck('antecedentes_familiares_oculares');

        $antecedentesFamiliaresGenerales = HistorialClinico::select('antecedentes_familiares_generales')
            ->whereNotNull('antecedentes_familiares_generales')
            ->distinct()
            ->pluck('antecedentes_familiares_generales');

        return view('historiales_clinicos.create', compact(
            'antecedentesPersonalesOculares',
            'antecedentesPersonalesGenerales',
            'antecedentesFamiliaresOculares',
            'antecedentesFamiliaresGenerales'
        ));
    }

    protected function validationRules()
    {
        return [
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'edad' => 'required|numeric|min:0|max:150',
            'fecha_nacimiento' => 'nullable|date',
            'cedula' => 'nullable|string|max:50',
            'celular' => 'required|string|max:20',
            'ocupacion' => 'required|string|max:100',
            'fecha' => 'required|date',
            'motivo_consulta' => 'required|string|max:1000',
            'enfermedad_actual' => 'required|string|max:1000',
            'antecedentes_personales_oculares' => 'required|string|max:1000',
            'antecedentes_personales_generales' => 'required|string|max:1000',
            'antecedentes_familiares_oculares' => 'required|string|max:1000',
            'antecedentes_familiares_generales' => 'required|string|max:1000',
            'agudeza_visual_vl_sin_correccion_od' => 'required|string|max:50',
            'agudeza_visual_vl_sin_correccion_oi' => 'required|string|max:50',
            'agudeza_visual_vl_sin_correccion_ao' => 'required|string|max:50',
            'agudeza_visual_vp_sin_correccion_od' => 'required|string|max:50',
            'agudeza_visual_vp_sin_correccion_oi' => 'required|string|max:50',
            'agudeza_visual_vp_sin_correccion_ao' => 'required|string|max:50',
            'ph_od' => 'required|string|max:50',
            'ph_oi' => 'required|string|max:50',
            'optotipo' => 'nullable|string|max:1000',
            'lensometria_od' => 'nullable|string|max:50',
            'lensometria_oi' => 'nullable|string|max:50',
            'tipo_lente' => 'nullable|string|max:50',
            'material' => 'nullable|string|max:50',
            'filtro' => 'nullable|string|max:50',
            'tiempo_uso' => 'nullable|string|max:50',
            'refraccion_od' => 'required|string|max:50',
            'refraccion_oi' => 'required|string|max:50',
            'rx_final_dp_od' => 'required|string|max:50',
            'rx_final_dp_oi' => 'required|string|max:50',
            'rx_final_av_vl_od' => 'required|string|max:50',
            'rx_final_av_vl_oi' => 'required|string|max:50',
            'rx_final_av_vp_od' => 'required|string|max:50',
            'rx_final_av_vp_oi' => 'required|string|max:50',
            'add' => 'nullable|string|max:50',
            'diagnostico' => 'required|string|max:1000',
            'tratamiento' => 'required|string|max:1000',
            'proxima_consulta' => 'nullable|date',
            'cotizacion' => 'nullable|string|max:1000',
            'usuario_id' => 'nullable|exists:users,id',
        ];
    }

    public function store(Request $request)
    {
        try {
            // Validar los datos
            $validator = \Validator::make($request->all(), $this->validationRules(), [
                'required' => 'El campo :attribute es obligatorio.',
                'string' => 'El campo :attribute debe ser texto.',
                'max' => [
                    'numeric' => 'El campo :attribute no debe ser mayor a :max.',
                    'string' => 'El campo :attribute no debe exceder :max caracteres.',
                ],
                'numeric' => 'El campo :attribute debe ser un número.',
                'date' => 'El campo :attribute debe ser una fecha válida.',
                'min' => [
                    'numeric' => 'El campo :attribute debe ser al menos :min.',
                    'string' => 'El campo :attribute debe tener al menos :min caracteres.',
                ],
            ], [
                'edad' => 'edad',
                'nombres' => 'nombres',
                'apellidos' => 'apellidos',
                'celular' => 'celular',
                'ocupacion' => 'ocupación',
                'motivo_consulta' => 'motivo de consulta',
                'enfermedad_actual' => 'enfermedad actual',
                'antecedentes_personales_oculares' => 'antecedentes personales oculares',
                'antecedentes_personales_generales' => 'antecedentes personales generales',
                'antecedentes_familiares_oculares' => 'antecedentes familiares oculares',
                'antecedentes_familiares_generales' => 'antecedentes familiares generales',
                'agudeza_visual_vl_sin_correccion_od' => 'agudeza visual VL sin corrección OD',
                'agudeza_visual_vl_sin_correccion_oi' => 'agudeza visual VL sin corrección OI',
                'agudeza_visual_vl_sin_correccion_ao' => 'agudeza visual VL sin corrección AO',
                'agudeza_visual_vp_sin_correccion_od' => 'agudeza visual VP sin corrección OD',
                'agudeza_visual_vp_sin_correccion_oi' => 'agudeza visual VP sin corrección OI',
                'agudeza_visual_vp_sin_correccion_ao' => 'agudeza visual VP sin corrección AO',
                'ph_od' => 'PH OD',
                'ph_oi' => 'PH OI',
                'refraccion_od' => 'refracción OD',
                'refraccion_oi' => 'refracción OI',
                'rx_final_dp_od' => 'RX final DP OD',
                'rx_final_dp_oi' => 'RX final DP OI',
                'rx_final_av_vl_od' => 'RX final AV VL OD',
                'rx_final_av_vl_oi' => 'RX final AV VL OI',
                'rx_final_av_vp_od' => 'RX final AV VP OD',
                'rx_final_av_vp_oi' => 'RX final AV VP OI',
                'diagnostico' => 'diagnóstico',
                'tratamiento' => 'tratamiento'
            ]);

            if ($validator->fails()) {
                return redirect()
                    ->back()
                    ->withErrors($validator)
                    ->withInput();
            }

            $data = $validator->validated();
            
            // Asegurarse de que el usuario_id esté establecido
            if (!isset($data['usuario_id'])) {
                $data['usuario_id'] = auth()->id();
            }
            
            // Crear el historial clínico
            HistorialClinico::create($data);

            return redirect()
                ->route('historiales_clinicos.index')
                ->with('success', 'Historial clínico creado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al crear historial clínico: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->withErrors(['error_general' => 'Error al crear el historial clínico: ' . $e->getMessage()]);
        }
    }

    public function show($id)
    {
        $historialClinico = HistorialClinico::findOrFail($id);
        return view('historiales_clinicos.show', compact('historialClinico'));
    }

    public function edit($id)
    {
        $historialClinico = HistorialClinico::findOrFail($id);
        return view('historiales_clinicos.edit', compact('historialClinico'));
    }

    public function update(Request $request, $id)
    {
        try {
            $historialClinico = HistorialClinico::findOrFail($id);
            
            // Obtener los datos validados
            $data = $request->validate($this->validationRules());
            
            // Filtrar campos vacíos
            $data = array_filter($data, function($value) {
                return $value !== null && $value !== '';
            });
            
            // Asegurar que el usuario_id se mantiene
            $data['usuario_id'] = $historialClinico->usuario_id;
            
            // Actualizar el registro
            $historialClinico->update($data);
            
            return redirect()
                ->route('historiales_clinicos.index')
                ->with('success', 'Historial clínico actualizado exitosamente');
                
        } catch (\Exception $e) {
            Log::error('Error al actualizar: ' . $e->getMessage());
            return redirect()
                ->back()
                ->withInput()
                ->with('error', 'Error al actualizar el historial clínico: ' . $e->getMessage());
        }
    }

    public function destroy(HistorialClinico $historialClinico)
    {
        $historialClinico->delete();
        return redirect()->route('historiales_clinicos.index');
    }

    public function enviarWhatsapp($id)
    {
        try {
            $historialClinico = HistorialClinico::findOrFail($id);
            
            // Constantes personalizables
            $DESCUENTO_MONTURA = 15;
            $DIAS_VALIDEZ = 15;
            $TELEFONO_OPTICA = "(02) 234-5678";
            $DIRECCION_OPTICA = "Av. Principal 123, Quito";
            $NOMBRE_OPTICA = "Escleróptica";
            $HORARIO_ATENCION = "Lunes a Viernes de 09:00 a 18:00";
            
            // Debug para ver qué datos estamos recibiendo
            Log::info('Datos del historial:', [
                'id' => $id,
                'celular' => $historialClinico->celular,
                'nombres' => $historialClinico->nombres
            ]);

            // Verificar si tiene número de celular y nombres
            if (!$historialClinico->celular) {
                return redirect()->back()
                    ->with('error', 'El paciente no tiene número de celular registrado.')
                    ->with('tipo', 'alert-danger');
            }

            // Formatear el número de teléfono (eliminar espacios y caracteres especiales)
            $telefono = preg_replace('/[^0-9]/', '', $historialClinico->celular);
            
            // Si el número empieza con 0, quitarlo
            if (substr($telefono, 0, 1) === '0') {
                $telefono = substr($telefono, 1);
            }
            
            // Agregar el código de país
            $telefono = "593" . $telefono;
            
            // Debug para ver el número formateado
            Log::info('Número formateado:', ['telefono' => $telefono]);
            
            // Construir el mensaje formal
            $mensaje = "*¡Feliz Cumpleaños!*\n\n";
            $mensaje .= "Estimado/a {$historialClinico->nombres}:\n\n";
            
            // Mensaje principal formal
            $mensaje .= "Reciba un cordial saludo de parte de {$NOMBRE_OPTICA}. En este día especial, queremos expresarle nuestros mejores deseos de bienestar y felicidad.\n\n";

            // Recordatorio de salud visual (condicional)
            if ($historialClinico->fecha) {
                $ultimaConsulta = \Carbon\Carbon::parse($historialClinico->fecha);
                $mesesDesdeUltimaConsulta = $ultimaConsulta->diffInMonths(now());
                
                if ($mesesDesdeUltimaConsulta > 6) {
                    $mensaje .= "Le recordamos que han transcurrido {$mesesDesdeUltimaConsulta} meses desde su última revisión visual. La salud de sus ojos es nuestra prioridad.\n\n";
                }
            }

            // Beneficios de cumpleaños
            $mensaje .= "*Beneficios especiales por su cumpleaños:*\n";
            $mensaje .= "• {$DESCUENTO_MONTURA}% de descuento en monturas seleccionadas\n";
            $mensaje .= "• Examen visual sin costo\n";
            $mensaje .= "• Mantenimiento gratuito de sus lentes\n\n";
            
            // Validez
            $fechaLimite = now()->addDays($DIAS_VALIDEZ)->format('d/m/Y');
            $mensaje .= "Estos beneficios están disponibles hasta el {$fechaLimite}.\n\n";

            // Información de contacto
            $mensaje .= "*Información de contacto:*\n";
            $mensaje .= "Teléfono: {$TELEFONO_OPTICA}\n";
            $mensaje .= "Dirección: {$DIRECCION_OPTICA}\n";
            $mensaje .= "Horario: {$HORARIO_ATENCION}\n\n";

            // Despedida formal
            $mensaje .= "Atentamente,\n";
            $mensaje .= "El equipo de {$NOMBRE_OPTICA}\n";
            $mensaje .= "_Comprometidos con su salud visual_";

            // Codificar el mensaje para URL
            $mensajeCodificado = urlencode($mensaje);

            // Generar el enlace de WhatsApp
            $whatsappUrl = "https://wa.me/{$telefono}?text={$mensajeCodificado}";

            // Debug para ver la URL final
            Log::info('URL de WhatsApp:', ['url' => $whatsappUrl]);

            // Redireccionar a WhatsApp
            return redirect()->away($whatsappUrl);

        } catch (\Exception $e) {
            Log::error('Error al enviar WhatsApp: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Error al intentar enviar el mensaje de WhatsApp: ' . $e->getMessage())
                ->with('tipo', 'alert-danger');
        }
    }

    public function cumpleanos()
    {
        try {
            $mesActual = now()->format('m');
            $añoActual = now()->format('Y');
            
            $cumpleaneros = HistorialClinico::whereRaw('MONTH(fecha_nacimiento) = ?', [$mesActual])
                ->orderByRaw('DAY(fecha_nacimiento)')
                ->get()
                ->map(function ($paciente) use ($añoActual) {
                    $fechaNacimiento = \Carbon\Carbon::parse($paciente->fecha_nacimiento);
                    // Calcular la edad actual
                    $edadActual = $fechaNacimiento->age;
                    // La edad que cumplirá será la actual + 1
                    $edadCumplir = $edadActual + 1;
                    
                    return [
                        'id' => $paciente->id,
                        'nombres' => $paciente->nombres,
                        'apellidos' => $paciente->apellidos,
                        'nombre_completo' => strtoupper($paciente->nombres . ' ' . $paciente->apellidos),
                        'fecha_nacimiento' => $fechaNacimiento->format('d/m/Y'),
                        'dia_cumpleanos' => $fechaNacimiento->format('d'),
                        'dia_nombre' => $fechaNacimiento->locale('es')->format('l'), // Nombre del día
                        'edad_actual' => $edadActual,
                        'edad_cumplir' => $edadCumplir,
                        'celular' => $paciente->celular,
                        'ultima_consulta' => $paciente->fecha ? \Carbon\Carbon::parse($paciente->fecha)->format('d/m/Y') : 'SIN CONSULTAS'
                    ];
                });
            
            // Eliminar duplicados basados en nombre completo, conservando el registro más reciente (ID mayor)
            $nombresCumpleaneros = [];
            $cumpleanerosFiltrados = collect();
            
            foreach ($cumpleaneros->sortByDesc('id') as $cumpleanero) {
                if (!in_array($cumpleanero['nombre_completo'], $nombresCumpleaneros)) {
                    $nombresCumpleaneros[] = $cumpleanero['nombre_completo'];
                    $cumpleanerosFiltrados->push($cumpleanero);
                }
            }
            
            // Reordenar por día de cumpleaños
            $cumpleanerosFiltrados = $cumpleanerosFiltrados->sortBy('dia_cumpleanos')->values();
            
            return view('historiales_clinicos.cumpleanos', [
                'cumpleaneros' => $cumpleanerosFiltrados,
                'mes_actual' => now()->formatLocalized('%B')
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error al obtener cumpleañeros: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar los cumpleañeros.');
        }
    }

    public function listaCumpleanos()
    {
        try {
            // Obtener el mes actual
            $mesActual = now()->format('m');
            $añoActual = now()->format('Y');
            
            // Obtener todos los pacientes que cumplen años en el mes actual
            $cumpleaneros = HistorialClinico::whereRaw('MONTH(fecha_nacimiento) = ?', [$mesActual])
                ->orderByRaw('DAY(fecha_nacimiento)')
                ->get()
                ->map(function ($paciente) use ($añoActual) {
                    $fechaNacimiento = \Carbon\Carbon::parse($paciente->fecha_nacimiento);
                    $edad = $fechaNacimiento->copy()->addYears($añoActual - $fechaNacimiento->year)->diffInYears(now());
                    
                    return [
                        'id' => $paciente->id,
                        'nombres' => $paciente->nombres,
                        'apellidos' => $paciente->apellidos,
                        'fecha_nacimiento' => $fechaNacimiento->format('d/m/Y'),
                        'dia_cumpleanos' => $fechaNacimiento->format('d'),
                        'edad_cumplir' => $edad,
                        'celular' => $paciente->celular
                    ];
                });
            
            return view('historiales_clinicos.lista_cumpleanos', [
                'cumpleaneros' => $cumpleaneros,
                'mes_actual' => now()->formatLocalized('%B')
            ]);
                
        } catch (\Exception $e) {
            Log::error('Error al obtener lista de cumpleaños: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Error al cargar la lista de cumpleaños.');
        }
    }

    public function proximasConsultas()
    {
        try {
            // Obtener la fecha actual
            $hoy = now();
            
            // Obtener historiales con próxima consulta en los próximos 7 días
            $consultas = HistorialClinico::whereNotNull('proxima_consulta')
                ->whereDate('proxima_consulta', '>=', $hoy)
                ->whereDate('proxima_consulta', '<=', $hoy->copy()->addDays(7))
                ->orderBy('proxima_consulta')
                ->get()
                ->map(function ($historial) use ($hoy) {
                    $proximaConsulta = \Carbon\Carbon::parse($historial->proxima_consulta);
                    $diasRestantes = $hoy->diffInDays($proximaConsulta, false);
                    
                    return [
                        'id' => $historial->id,
                        'nombres' => $historial->nombres,
                        'apellidos' => $historial->apellidos,
                        'celular' => $historial->celular,
                        'fecha_consulta' => $proximaConsulta->format('d/m/Y'),
                        'dias_restantes' => max(0, $diasRestantes),
                        'ultima_consulta' => $historial->fecha ? \Carbon\Carbon::parse($historial->fecha)->format('d/m/Y') : 'SIN CONSULTAS',
                        'motivo_consulta' => $historial->motivo_consulta
                    ];
                })
                ->sortBy('dias_restantes')
                ->values();
            
            return view('historiales_clinicos.proximas_consultas', compact('consultas'));
                
        } catch (\Exception $e) {
            Log::error('Error al obtener próximas consultas: ' . $e->getMessage());
            return redirect()->back()->with([
                'error' => 'Error al cargar las próximas consultas.',
                'tipo' => 'alert-danger'
            ]);
        }
    }

    public function enviarMensaje(Request $request, $id)
    {
        try {
            $historial = HistorialClinico::findOrFail($id);
            
            // Verificar si ya se envió un mensaje este mes
            $mesActual = now()->format('Y-m');
            $mensajeEnviado = MensajesEnviados::where('historial_id', $id)
                ->where('tipo', $request->tipo)
                ->whereRaw('DATE_FORMAT(fecha_envio, "%Y-%m") = ?', [$mesActual])
                ->exists();
                
            if ($mensajeEnviado && $request->has('forzar_envio') && !$request->forzar_envio) {
                return response()->json([
                    'error' => 'Ya se envió un mensaje este mes a este paciente',
                    'requiere_confirmacion' => true
                ], 422);
            }

            // Formatear número de teléfono
            $telefono = $historial->celular;
            if (!$telefono) {
                throw new \Exception('El paciente no tiene número de teléfono registrado.');
            }

            if (substr($telefono, 0, 1) === '0') {
                $telefono = '593' . substr($telefono, 1);
            } else if (substr($telefono, 0, 3) !== '593') {
                $telefono = '593' . $telefono;
            }

            // Guardar registro del mensaje enviado
            MensajesEnviados::create([
                'historial_id' => $id,
                'tipo' => $request->tipo,
                'mensaje' => $request->mensaje,
                'fecha_envio' => now()
            ]);

            // Generar URL de WhatsApp
            $mensajeCodificado = urlencode($request->mensaje);
            $whatsappUrl = "https://wa.me/{$telefono}?text={$mensajeCodificado}";

            return response()->json([
                'success' => true,
                'url' => $whatsappUrl
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Guarda un mensaje predeterminado en la sesión
     */
    public function guardarMensajePredeterminado(Request $request)
    {
        try {
            $request->validate([
                'tipo' => 'required|string',
                'mensaje' => 'required|string'
            ]);
            
            // Guardar en la sesión según el tipo
            if ($request->tipo == 'cumpleanos') {
                session(['mensaje_predeterminado' => $request->mensaje]);
            } else if ($request->tipo == 'consulta') {
                session(['mensaje_predeterminado_consulta' => $request->mensaje]);
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Mensaje predeterminado guardado correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function recordatoriosConsulta()
    {
        // Obtener todas las próximas consultas de los próximos 7 días
        $fechaActual = now();
        $fechaLimite = now()->addDays(7);
        
        $proximasConsultas = HistorialClinico::whereNotNull('proxima_consulta')
            ->whereDate('proxima_consulta', '>=', $fechaActual)
            ->whereDate('proxima_consulta', '<=', $fechaLimite)
            ->orderBy('proxima_consulta', 'asc')
            ->get();
            
        // Obtener mensajes predeterminados de la sesión o usar valor por defecto
        $mensajePredeterminado = session('mensaje_recordatorio', 
            "Hola [NOMBRE], le recordamos su cita oftalmológica programada para el [FECHA]. " .
            "Si necesita reagendarla, por favor contáctenos. ¡Le esperamos!");

        return view('mensajes.recordatorios', compact('proximasConsultas', 'mensajePredeterminado'));
    }

    /**
     * Obtiene los historiales clínicos relacionados por nombres y apellidos
     */
    public function historialesRelacionados(Request $request)
    {
        try {
            // Validar los datos de entrada
            $request->validate([
                'nombres' => 'required|string',
                'apellidos' => 'required|string',
            ]);

            $nombres = $request->get('nombres');
            $apellidos = $request->get('apellidos');

            // Buscar historiales con el mismo nombre y apellido, ordenados por fecha descendente
            $historiales = HistorialClinico::where('nombres', 'like', "%{$nombres}%")
                                         ->where('apellidos', 'like', "%{$apellidos}%")
                                         ->orderBy('fecha', 'desc')
                                         ->get();

            // Retornar los historiales en formato JSON
            return response()->json([
                'success' => true,
                'historiales' => $historiales
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener historiales relacionados: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'mensaje' => 'Error al obtener historiales relacionados: ' . $e->getMessage()
            ], 500);
        }
    }
}
