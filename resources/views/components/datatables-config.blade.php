{{-- 
    Script de configuración global para DataTables en español
    Incluir este archivo en vistas que usen DataTables para evitar problemas de CORS
--}}

@push('js')
<script>
// Verificar que jQuery esté cargado
if (typeof jQuery === 'undefined') {
    console.warn('jQuery no está cargado. DataTables puede no funcionar correctamente.');
}

// Configuración de DataTables en español (alternativa sin CDN)
$(document).ready(function() {
    // Solo ejecutar si DataTables está disponible
    if (typeof $.fn.DataTable !== 'undefined') {
        
        // Establecer configuración por defecto
        $.extend(true, $.fn.dataTable.defaults, {
            "language": {
                "processing": "Procesando...",
                "lengthMenu": "Mostrar _MENU_ registros",
                "zeroRecords": "No se encontraron resultados",
                "emptyTable": "Ningún dato disponible en esta tabla",
                "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
                "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
                "infoFiltered": "(filtrado de un total de _MAX_ registros)",
                "search": "Buscar:",
                "infoThousands": ",",
                "loadingRecords": "Cargando...",
                "paginate": {
                    "first": "Primero",
                    "last": "Último",
                    "next": "Siguiente",
                    "previous": "Anterior"
                },
                "aria": {
                    "sortAscending": ": Activar para ordenar la columna de manera ascendente",
                    "sortDescending": ": Activar para ordenar la columna de manera descendente"
                },
                "buttons": {
                    "copy": "Copiar",
                    "colvis": "Visibilidad",
                    "collection": "Colección",
                    "colvisRestore": "Restaurar visibilidad",
                    "csv": "CSV",
                    "excel": "Excel",
                    "pageLength": {
                        "-1": "Mostrar todas las filas",
                        "_": "Mostrar %d filas"
                    },
                    "pdf": "PDF",
                    "print": "Imprimir"
                },
                "decimal": ",",
                "thousands": ".",
                "select": {
                    "cells": {
                        "1": "1 celda seleccionada",
                        "_": "%d celdas seleccionadas"
                    },
                    "columns": {
                        "1": "1 columna seleccionada", 
                        "_": "%d columnas seleccionadas"
                    },
                    "rows": {
                        "1": "1 fila seleccionada",
                        "_": "%d filas seleccionadas"
                    }
                }
            }
        });
        
        console.log('Configuración de DataTables en español cargada correctamente');
    } else {
        console.warn('DataTables no está disponible. Verifique que la librería esté cargada.');
    }
});
</script>
@endpush
