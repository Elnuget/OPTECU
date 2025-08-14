# Implementación de Detalles de Sueldo

## Controlador SueldosController

Para que funcione completamente, necesitas agregar la lógica para obtener los detalles de sueldo en tu controlador.

### Ejemplo de implementación en el método index:

```php
<?php

namespace App\Http\Controllers;

use App\Models\DetalleSueldo;
use App\Models\Sueldo;
use Illuminate\Http\Request;

class SueldosController extends Controller
{
    public function index(Request $request)
    {
        // ... código existente para sueldos y pedidos ...
        
        // Obtener detalles de sueldo con filtros
        $queryDetalles = DetalleSueldo::with('user');
        
        // Aplicar filtros si están presentes
        if ($request->filled('anio')) {
            $queryDetalles->where('ano', $request->anio);
        }
        
        if ($request->filled('mes')) {
            $queryDetalles->where('mes', $request->mes);
        }
        
        if ($request->filled('usuario')) {
            $queryDetalles->whereHas('user', function($q) use ($request) {
                $q->where('name', 'LIKE', '%' . $request->usuario . '%');
            });
        }
        
        $detallesSueldo = $queryDetalles->orderBy('ano', 'desc')
                                       ->orderBy('mes', 'desc')
                                       ->orderBy('created_at', 'desc')
                                       ->get();
        
        return view('sueldos.index', compact(
            'sueldos', 
            'pedidos', 
            'retirosCaja', 
            'usuariosConPedidos',
            'detallesSueldo' // Agregar esta variable
        ));
    }
}
```

## Rutas Necesarias

Agregar estas rutas en `web.php`:

```php
// Rutas para Detalles de Sueldo
Route::resource('detalles-sueldo', DetallesSueldoController::class)
     ->except(['index'])
     ->names([
         'create' => 'detalles-sueldo.create',
         'store' => 'detalles-sueldo.store',
         'show' => 'detalles-sueldo.show',
         'edit' => 'detalles-sueldo.edit',
         'update' => 'detalles-sueldo.update',
         'destroy' => 'detalles-sueldo.destroy',
     ]);
```

## Controlador DetallesSueldoController

Crear el controlador:

```bash
php artisan make:controller DetallesSueldoController --resource --model=DetalleSueldo
```

## Modelo User

Agregar relación en el modelo User:

```php
public function detallesSueldo()
{
    return $this->hasMany(DetalleSueldo::class);
}
```

## Migración

Si no existe la tabla, crear migración:

```bash
php artisan make:migration create_detalles_sueldos_table
```

## Estructura de la tabla:

```php
public function up()
{
    Schema::create('detalles_sueldos', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->constrained()->onDelete('cascade');
        $table->integer('mes');
        $table->integer('ano');
        $table->string('descripcion');
        $table->decimal('valor', 10, 2);
        $table->timestamps();
        $table->softDeletes();
        
        // Índices
        $table->index(['ano', 'mes']);
        $table->index(['user_id', 'ano', 'mes']);
    });
}
```

## Permisos

Asegúrate de que los permisos `@can('admin')` estén correctamente configurados en tu aplicación.

## Notas Importantes

1. La variable `$detallesSueldo` debe ser pasada desde el controlador
2. Las rutas deben estar definidas correctamente
3. Los permisos deben estar configurados
4. El modelo DetalleSueldo debe tener la relación con User correctamente configurada
