## Cambios en el Controlador para el Filtro de Empresa

Para que el filtro de empresa funcione correctamente en la vista `resources/views/pagos/index.blade.php`, necesitas hacer los siguientes cambios en tu controlador:

1. Obtener todas las empresas y pasarlas a la vista:

```php
use App\Models\Empresa;

// En el método index del controlador
public function index(Request $request)
{
    // Código existente...

    // Obtener todas las empresas para el filtro
    $empresas = Empresa::orderBy('nombre')->get();

    // Incluir las empresas en los datos que se pasan a la vista
    return view('pagos.index', compact('pagos', 'mediosdepago', 'totalPagos', 'empresas'));
}
```

2. Agregar el filtro por empresa a la consulta de pagos:

```php
// En el método donde filtras los pagos
if ($request->has('empresa') && $request->empresa != '') {
    $pagos = $pagos->filter(function($pago) use ($request) {
        return $pago->pedido->empresa_id == $request->empresa;
    });
}
```

Estos cambios permitirán que el filtro de empresa funcione correctamente en la página de pagos.
