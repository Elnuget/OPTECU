@push('js')
    <script>
        $(document).ready(function() {
            // Verificar si DataTables está disponible
            if (typeof $.fn.DataTable === 'undefined') {
                console.warn('DataTables no está cargado');
                return;
            }

            // Función para guardar el estado de las tablas y la posición del scroll
            function saveState() {
                // Guardar qué tablas están expandidas
                const expandedTables = [];
                $('.collapse').each(function() {
                    if ($(this).hasClass('show')) {
                        expandedTables.push($(this).attr('id'));
                    }
                });
                localStorage.setItem('expandedTables', JSON.stringify(expandedTables));

                // Guardar la posición del scroll
                localStorage.setItem('scrollPosition', window.scrollY);
            }

            // Función para restaurar el estado
            function restoreState() {
                // Restaurar tablas expandidas
                const expandedTables = JSON.parse(localStorage.getItem('expandedTables') || '[]');
                expandedTables.forEach(tableId => {
                    $(`#${tableId}`).addClass('show');
                    $(`[data-target="#${tableId}"]`).find('.transition-icon').addClass('fa-rotate-180');
                });

                // Restaurar posición del scroll
                const scrollPosition = localStorage.getItem('scrollPosition');
                if (scrollPosition) {
                    window.scrollTo(0, parseInt(scrollPosition));
                }
            }

            // Guardar estado antes de recargar
            window.addEventListener('beforeunload', saveState);

            // Restaurar estado después de cargar
            restoreState();

            // Función para limpiar completamente las DataTables
            function clearAllDataTables() {
                $('.inventario-table').each(function() {
                    const $table = $(this);
                    
                    // Si hay una instancia de DataTable
                    if ($.fn.DataTable.isDataTable(this)) {
                        try {
                            const dt = $table.DataTable();
                            // No llamar a clear() para preservar el contenido original
                            dt.destroy(); // Sin parámetro true para mantener el DOM original
                        } catch (e) {
                            console.warn('Error limpiando DataTable:', e);
                        }
                    }
                    
                    // Limpiar solo referencias y clases, no el contenido
                    $table.removeData();
                    $table.removeClass('dataTable');
                    
                    // Solo remover wrappers específicos, no el contenido de la tabla
                    const $searchWrapper = $table.siblings('.dataTables_filter');
                    const $infoWrapper = $table.siblings('.dataTables_info');
                    const $pagingWrapper = $table.siblings('.dataTables_paginate');
                    
                    $searchWrapper.remove();
                    $infoWrapper.remove();
                    $pagingWrapper.remove();
                });
            }

            // Función para inicializar DataTables de forma segura y conservadora
            function initializeDataTables() {
                // Verificar que DataTables esté disponible
                if (typeof $.fn.DataTable === 'undefined') {
                    console.warn('DataTables no disponible para inicialización');
                    return;
                }

                $('.inventario-table').each(function() {
                    const $table = $(this);
                    
                    // Solo procesar si la tabla es visible
                    if (!$table.is(':visible')) {
                        return;
                    }
                    
                    // Verificar que la tabla tenga contenido
                    if ($table.find('thead tr th').length === 0 || $table.find('tbody tr').length === 0) {
                        return;
                    }
                    
                    // Si ya está inicializada, verificar si funciona correctamente
                    if ($.fn.DataTable.isDataTable(this)) {
                        try {
                            // Intentar acceder a la instancia para verificar que funciona
                            const dt = $table.DataTable();
                            dt.rows().count(); // Simple verificación
                            return; // Si funciona, no hacer nada
                        } catch (e) {
                            // Si hay error, destruir y reinicializar
                            try {
                                $(this).DataTable().destroy(false);
                            } catch (e2) {
                                console.warn('Error destruyendo DataTable problemática:', e2);
                            }
                        }
                    }
                    
                    // Inicializar DataTable solo si no está inicializada o después de destruir una problemática
                    try {
                        $table.DataTable({
                            dom: '<"row"<"col-12"f>>' +
                                 '<"row"<"col-12"t>>',
                            ordering: true,
                            searching: true,
                            paging: false,
                            info: false,
                            responsive: false,
                            autoWidth: false,
                            language: {
                                search: "Buscar en esta tabla:",
                                zeroRecords: "No se encontraron registros coincidentes",
                                searchPlaceholder: "Buscar número, código, lugar, empresa..."
                            },
                            columnDefs: [
                                {
                                    targets: 0,
                                    type: 'num',
                                },
                                {
                                    targets: 4,
                                    type: 'string',
                                }
                            ],
                            searchCols: [
                                null, // Número
                                null, // Lugar  
                                null, // Columna
                                null, // Código
                                null, // Empresa
                                null, // Cantidad
                                null  // Acciones
                            ],
                            drawCallback: function() {
                                saveState();
                                
                                const searchTerm = $('#busquedaGlobal').val();
                                if (searchTerm && searchTerm.length > 0) {
                                    updateSearchResultsCounter();
                                }
                            }
                        });
                    } catch (error) {
                        console.warn('Error inicializando DataTable:', error);
                    }
                });
            }

            // Inicializar DataTables
            initializeDataTables();

            // Variable para controlar si se está procesando inicialización
            let isInitializing = false;

            // Función mejorada para reinicializar DataTables
            function reinitializeDataTables() {
                if (isInitializing) {
                    console.log('Inicialización en progreso, omitiendo...');
                    return;
                }
                
                isInitializing = true;
                setTimeout(function() {
                    initializeDataTables();
                    isInitializing = false;
                }, 200);
            }

            // Reinicializar DataTables cuando se muestran los collapse (solo una vez por evento)
            // Usar un debounce para evitar múltiples reinicializaciones
            let collapseTimeout;
            $('.collapse').on('shown.bs.collapse', function () {
                clearTimeout(collapseTimeout);
                collapseTimeout = setTimeout(function() {
                    reinitializeDataTables();
                }, 100);
            });

            // Variable para controlar el estado de expansión
            let allExpanded = false;

            // Función para expandir/contraer todas las tarjetas
            $('#toggleAll').on('click', function() {
                // Evitar múltiples clics rápidos
                if (isInitializing) {
                    return;
                }
                
                allExpanded = !allExpanded;
                if (allExpanded) {
                    $('.collapse').collapse('show');
                    $('.transition-icon').addClass('fa-rotate-180');
                    // Reinicializar DataTables después de expandir con un delay más largo
                    setTimeout(function() {
                        reinitializeDataTables();
                    }, 600);
                } else {
                    $('.collapse').collapse('hide');
                    $('.transition-icon').removeClass('fa-rotate-180');
                }
                saveState();
            });

            // Botón buscar y expandir
            $('#buscarExpandir').on('click', function() {
                const searchTerm = $('#busquedaGlobal').val().toLowerCase().trim();
                
                if (searchTerm === '') {
                    Swal.fire({
                        icon: 'info',
                        title: 'Búsqueda vacía',
                        text: 'Ingrese un término de búsqueda'
                    });
                    return;
                }
                
                // Expandir todas las tarjetas
                $('.collapse').collapse('show');
                $('.transition-icon').addClass('fa-rotate-180');
                allExpanded = true;
                
                // Reinicializar DataTables después de expandir con la función mejorada
                setTimeout(function() {
                    if (!isInitializing) {
                        isInitializing = true;
                        initializeDataTables();
                        
                        setTimeout(function() {
                            // Realizar la búsqueda después de reinicializar
                            let totalMatches = 0;
                            $('.inventario-table').each(function() {
                                if ($.fn.DataTable.isDataTable(this)) {
                                    const table = $(this).DataTable();
                                    table.search(searchTerm).draw();
                                    
                                    // Contar coincidencias visibles
                                    const visibleRows = table.rows({search: 'applied'}).count();
                                    totalMatches += visibleRows;
                                }
                            });
                            
                            // Resaltar resultados
                            $('.inventario-table tbody tr').removeClass('highlight-search');
                            $('.inventario-table tbody tr:visible').each(function() {
                                let row = $(this);
                                let rowText = row.text().toLowerCase();
                                if (rowText.includes(searchTerm)) {
                                    row.addClass('highlight-search');
                                }
                            });
                            
                            // Mostrar resultado de la búsqueda
                            if (totalMatches > 0) {
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Búsqueda completada',
                                    text: `Se encontraron ${totalMatches} coincidencias para "${searchTerm}"`,
                                    timer: 2000,
                                    showConfirmButton: false
                                });
                            } else {
                                Swal.fire({
                                    icon: 'warning',
                                    title: 'Sin resultados',
                                    text: `No se encontraron coincidencias para "${searchTerm}"`
                                });
                            }
                            
                            isInitializing = false;
                        }, 200);
                    }
                }, 600);
                
                saveState();
            });

            // Función para actualizar contador de resultados
            function updateSearchResultsCounter() {
                let totalResults = 0;
                $('.inventario-table').each(function() {
                    if ($.fn.DataTable.isDataTable(this)) {
                        const table = $(this).DataTable();
                        const visibleRows = table.rows({search: 'applied'}).count();
                        totalResults += visibleRows;
                    }
                });
                
                // Mostrar contador si existe
                let counter = $('.search-results-counter');
                if (counter.length === 0) {
                    $('#busquedaGlobal').parent().append('<div class="search-results-counter"></div>');
                    counter = $('.search-results-counter');
                }
                
                const searchTerm = $('#busquedaGlobal').val();
                if (searchTerm && searchTerm.length > 0) {
                    counter.text(`${totalResults} resultados encontrados`).show();
                } else {
                    counter.hide();
                }
            }

            // Búsqueda global
            $('#busquedaGlobal').on('keyup', function() {
                let searchTerm = $(this).val().toLowerCase();
                $('.inventario-table').each(function() {
                    if ($.fn.DataTable.isDataTable(this)) {
                        // Aplicar búsqueda global en todas las columnas
                        $(this).DataTable().search(searchTerm).draw();
                    }
                });
                
                // Actualizar contador de resultados
                updateSearchResultsCounter();
                
                // Marcar resultados encontrados
                if (searchTerm.length > 0) {
                    $('.inventario-table tbody tr:visible').each(function() {
                        let row = $(this);
                        let rowText = row.text().toLowerCase();
                        if (rowText.includes(searchTerm)) {
                            row.addClass('highlight-search');
                        } else {
                            row.removeClass('highlight-search');
                        }
                    });
                } else {
                    $('.inventario-table tbody tr').removeClass('highlight-search');
                }
            });

            // Manejar la rotación del icono en los headers de las tarjetas
            $('.card-header').on('click', function() {
                $(this).find('.transition-icon').toggleClass('fa-rotate-180');
                // Pequeño retraso para asegurar que el estado se guarde después de la transición
                setTimeout(saveState, 350);
            });

            // Guardar estado cuando el usuario hace scroll
            let scrollTimeout;
            window.addEventListener('scroll', function() {
                clearTimeout(scrollTimeout);
                scrollTimeout = setTimeout(saveState, 100);
            });

            // Auto-submit cuando cambie el filtro de empresa
            $('#empresa_id').change(function() {
                $(this).closest('form').submit();
            });

            // Función para cargar sucursal por defecto desde localStorage
            function cargarSucursalPorDefecto() {
                // Usar la nueva clase SucursalCache si está disponible
                if (window.SucursalCache) {
                    SucursalCache.preseleccionarEnSelect('empresa_id', true);
                } else {
                    // Fallback al método anterior
                    try {
                        const sucursalData = localStorage.getItem('sucursal_abierta');
                        if (sucursalData && !window.location.search.includes('empresa_id=')) {
                            const sucursal = JSON.parse(sucursalData);
                            const empresaSelect = document.getElementById('empresa_id');
                            if (empresaSelect) {
                                const option = empresaSelect.querySelector(`option[value="${sucursal.id}"]`);
                                if (option) {
                                    empresaSelect.value = sucursal.id;
                                    empresaSelect.style.borderColor = '#28a745';
                                    empresaSelect.style.boxShadow = '0 0 0 0.2rem rgba(40, 167, 69, 0.25)';
                                    $(empresaSelect).closest('form').submit();
                                }
                            }
                        }
                    } catch (e) {
                        console.error('Error al cargar sucursal por defecto:', e);
                    }
                }
            }

            // Cargar sucursal por defecto al inicializar
            cargarSucursalPorDefecto();

            // Funciones de navegación
            window.crearArticulo = function() {
                window.location.href = "{{ route('inventario.create') }}";
            }

            window.actualizarArticulos = function() {
                window.location.href = "{{ route('inventario.actualizar') }}";
            }

            window.generarQR = function() {
                if (confirm('¿Está seguro que desea generar nuevos registros?')) {
                    window.location.href = "{{ route('generarQR') }}";
                }
            }

            window.añadirQR = function() {
                window.location.href = "{{ route('leerQR') }}";
            }

            window.historialMovimientos = function() {
                window.location.href = "{{ route('pedidos.inventario-historial') }}";
            }

            // Función para actualizar el campo (definida fuera de los event handlers)
            function updateField(inputElement, newValue, currentValue, field, id, displayText) {
                if (newValue === currentValue) {
                    inputElement.siblings('.display-value').show();
                    inputElement.hide();
                    return;
                }
                
                let cell = inputElement.closest('.editable');
                let row = cell.closest('tr');
                let displayValue = cell.find('.display-value');
                
                // Obtener los valores actuales de la fila
                let data = {};
                
                try {
                    // Obtener y validar número
                    let numeroText = row.find('[data-field="numero"] .display-value').text().trim();
                    data.numero = parseInt(numeroText);
                    if (isNaN(data.numero)) throw new Error('El número debe ser un valor válido');
                    
                    // Obtener lugar y columna directamente de la fila
                    data.lugar = row.find('[data-field="lugar"] .display-value').text().trim();
                    if (!data.lugar) throw new Error('El lugar no puede estar vacío');
                    
                    data.columna = parseInt(row.find('[data-field="columna"] .display-value').text().trim());
                    if (isNaN(data.columna)) throw new Error('La columna debe ser un número válido');
                    
                    // Obtener y validar código
                    data.codigo = row.find('[data-field="codigo"] .display-value').text().trim();
                    if (!data.codigo) throw new Error('El código no puede estar vacío');
                    
                    // Obtener y validar cantidad
                    let cantidadText = row.find('[data-field="cantidad"] .display-value').text().trim();
                    data.cantidad = parseInt(cantidadText);
                    if (isNaN(data.cantidad)) throw new Error('La cantidad debe ser un número válido');
                    
                    // Obtener empresa_id (opcional)
                    let empresaSelect = row.find('[data-field="empresa_id"] .edit-input');
                    if (empresaSelect.length > 0) {
                        data.empresa_id = empresaSelect.val() || null;
                    } else {
                        // Si no hay select, obtener del display-value
                        let empresaText = row.find('[data-field="empresa_id"] .display-value').text().trim();
                        if (empresaText && empresaText !== 'N/A') {
                            // Buscar el ID de la empresa por nombre (esto es un fallback)
                            data.empresa_id = null; // Por ahora null, se manejará en el servidor
                        } else {
                            data.empresa_id = null;
                        }
                    }
                    
                    // Actualizar el campo específico con el nuevo valor
                    if (field === 'numero') {
                        let newNum = parseInt(newValue);
                        if (isNaN(newNum) || newNum < 0) throw new Error('El número debe ser un valor válido y no negativo');
                        data.numero = newNum;
                    } else if (field === 'cantidad') {
                        let newCant = parseInt(newValue);
                        if (isNaN(newCant) || newCant < 0) throw new Error('La cantidad debe ser un valor válido y no negativo');
                        data.cantidad = newCant;
                    } else if (field === 'codigo') {
                        if (!newValue.trim()) throw new Error('El código no puede estar vacío');
                        data.codigo = newValue.trim();
                    } else if (field === 'lugar') {
                        if (!newValue.trim()) throw new Error('El lugar no puede estar vacío');
                        data.lugar = newValue.trim();
                    } else if (field === 'columna') {
                        let newCol = parseInt(newValue);
                        if (isNaN(newCol) || newCol < 0) throw new Error('La columna debe ser un valor válido y no negativo');
                        data.columna = newCol;
                    } else if (field === 'empresa_id') {
                        data.empresa_id = newValue || null;
                    }
                    
                    // Log para debug
                    console.log('Datos a enviar:', data);
                    
                    // Mostrar indicador de carga
                    cell.addClass('bg-light');
                    
                    // Realizar la petición AJAX
                    $.ajax({
                        url: `/inventario/${id}/update-inline`,
                        method: 'POST',
                        data: data,
                        headers: {
                            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                        },
                        success: function(response) {
                            if (response.success) {
                                // Actualizar el display value según el tipo de campo
                                if (field === 'empresa_id') {
                                    let selectedText = displayText || 'N/A';
                                    if (!newValue || newValue === '') {
                                        selectedText = 'N/A';
                                    }
                                    displayValue.text(selectedText).show();
                                } else {
                                    displayValue.text(newValue).show();
                                }
                                
                                inputElement.hide();
                                
                                // Actualizar el valor en la fila
                                if (field === 'empresa_id') {
                                    row.find(`[data-field="${field}"] .display-value`).text(displayText || 'N/A');
                                } else {
                                    row.find(`[data-field="${field}"] .display-value`).text(newValue);
                                }
                                
                                // Actualizar clase de fila si la cantidad es 0
                                if (field === 'cantidad') {
                                    if (parseInt(newValue) === 0) {
                                        row.addClass('table-danger');
                                    } else {
                                        row.removeClass('table-danger');
                                    }
                                }
                                
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Actualizado',
                                    text: response.message,
                                    timer: 1500,
                                    showConfirmButton: false
                                }).then(() => {
                                    // Preservar parámetros actuales al recargar
                                    if (response.redirect_params) {
                                        const params = new URLSearchParams(response.redirect_params);
                                        window.location.href = window.location.pathname + '?' + params.toString();
                                    } else {
                                        window.location.reload();
                                    }
                                });
                            } else {
                                displayValue.show();
                                inputElement.hide();
                                
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error',
                                    text: response.message
                                });
                            }
                        },
                        error: function(xhr) {
                            displayValue.show();
                            inputElement.hide();
                            
                            let errorMsg = 'No se pudo actualizar el registro';
                            if (xhr.responseJSON && xhr.responseJSON.message) {
                                errorMsg = xhr.responseJSON.message;
                            }
                            
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: errorMsg
                            });
                        },
                        complete: function() {
                            cell.removeClass('bg-light');
                        }
                    });
                } catch (error) {
                    displayValue.show();
                    inputElement.hide();
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: error.message
                    });
                }
            }

        // Variables globales para prevenir duplicación de modales
        let updatingElements = new Set();

        // Función updateField para manejar las actualizaciones
        function updateField(inputElement, newValue, currentValue, field, id, newText = null) {
            // Crear clave única para este elemento
            const elementKey = `${id}-${field}`;
            
            // Prevenir múltiples actualizaciones simultáneas del mismo elemento
            if (updatingElements.has(elementKey)) {
                console.log('Actualización ya en progreso para', elementKey, ', ignorando...');
                return;
            }

            // Evitar actualización si no hay cambios
            if (newValue == currentValue) {
                inputElement.hide();
                inputElement.siblings('.display-value').show();
                return;
            }

            // Marcar como actualizando
            updatingElements.add(elementKey);

            // Preparar datos
            let data = {
                _token: '{{ csrf_token() }}',
                field: field,
                value: newValue
            };

            console.log('Enviando datos:', data, 'para elemento:', elementKey);

            // Mostrar loading
            inputElement.prop('disabled', true);

            $.ajax({
                url: `/inventario/${id}/update-inline`,
                type: 'POST',
                data: data,
                success: function(response) {
                    console.log('Respuesta exitosa:', response);
                    if (response.success) {
                        // Actualizar la vista
                        let displayElement = inputElement.siblings('.display-value');
                        
                        if (field === 'empresa_id') {
                            // Para empresa, usar el texto del option seleccionado
                            let selectedText = newText || inputElement.find('option:selected').text();
                            displayElement.text(selectedText);
                            console.log('Empresa actualizada a:', selectedText);
                        } else {
                            displayElement.text(newValue);
                        }
                        
                        // Ocultar input y mostrar display
                        inputElement.hide();
                        displayElement.show();
                        
                        // Mostrar mensaje de éxito y recargar página
                        Swal.fire({
                            icon: 'success',
                            title: 'Actualizado',
                            text: `${field} actualizado correctamente`,
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            // Recargar la página automáticamente después del mensaje
                            window.location.reload();
                        });
                    } else {
                        throw new Error(response.message || 'Error al actualizar');
                    }
                },
                error: function(xhr) {
                    console.error('Error en AJAX:', xhr);
                    console.error('Response text:', xhr.responseText);
                    console.error('Response JSON:', xhr.responseJSON);
                    
                    let errorMessage = 'Error al actualizar el campo';
                    
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMessage = xhr.responseJSON.message;
                    } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                        // Mostrar errores de validación específicos
                        let errors = xhr.responseJSON.errors;
                        errorMessage = Object.values(errors).flat().join(', ');
                    }
                    
                    // Restaurar valor original
                    if (field === 'empresa_id') {
                        inputElement.val(currentValue);
                    } else {
                        inputElement.val(currentValue);
                    }
                    
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de validación',
                        text: errorMessage
                    });
                },
                complete: function() {
                    inputElement.prop('disabled', false);
                    // Remover de elementos actualizando
                    updatingElements.delete(elementKey);
                }
            });
        }

            // Edición en línea
            $('.editable').off('click.editInline').on('click.editInline', function() {
                let currentValue = $(this).find('.display-value').text().trim();
                let field = $(this).data('field');
                let id = $(this).closest('tr').data('id');
                let input = $(this).find('.edit-input');
                let displayValue = $(this).find('.display-value');
                
                displayValue.hide();
                input.show().focus();
                
                // Para selects, no establecer val() ya que tienen las opciones preseleccionadas
                if (input.is('select')) {
                    // El select ya tiene el valor correcto por el atributo selected en el HTML
                } else {
                    input.val(currentValue);
                }

                // Remover eventos previos para evitar duplicados
                input.off('blur.editInline keypress.editInline change.editInline keyup.editInline');

                input.on('blur.editInline keypress.editInline', function(e) {
                    if (e.type === 'keypress' && e.which !== 13) return;
                    
                    let newValue = $(this).val();
                    let newText = '';
                    
                    if ($(this).is('select')) {
                        newText = $(this).find('option:selected').text();
                    }
                    
                    updateField($(this), newValue, currentValue, field, id, newText);
                });

                // También manejar el evento change para selects
                input.on('change.editInline', function(e) {
                    let newValue = $(this).val();
                    let newText = $(this).find('option:selected').text();
                    
                    updateField($(this), newValue, currentValue, field, id, newText);
                });

                // Cancelar edición con Escape
                input.on('keyup.editInline', function(e) {
                    if (e.key === 'Escape') {
                        displayValue.show();
                        input.hide();
                    }
                });
            });

            // Variables para controlar la creación de artículos
            let creatingArticles = new Set();

            // Función para manejar el botón de agregar fila
            $('.add-row-btn').on('click', function() {
                const columna = $(this).data('columna');
                const table = $(this).closest('table');
                const newRow = table.find('.new-row[data-columna="' + columna + '"]');
                
                // Mostrar la nueva fila si está oculta
                if (newRow.is(':hidden')) {
                    newRow.show();
                    // Activar la edición en la celda de código
                    setTimeout(() => {
                        newRow.find('td[data-field="codigo"]').trigger('click');
                    }, 100);
                }
            });

            // Manejo específico para filas nuevas (crear artículo)
            $('.table').on('click', 'tr.new-row td.editable', function() {
                const cell = $(this);
                const field = cell.data('field');
                const row = cell.closest('tr');
                const displayValue = cell.find('.display-value');
                const input = cell.find('.edit-input');
                
                // Solo permitir edición de código, empresa_id y cantidad en filas nuevas
                if (!['codigo', 'empresa_id', 'cantidad'].includes(field)) {
                    return;
                }
                
                displayValue.hide();
                input.show().focus();
                
                // Remover eventos previos para evitar duplicados
                input.off('blur.newRow keypress.newRow change.newRow');
                
                // Evento para blur y enter (pero no para change)
                input.on('blur.newRow keypress.newRow', function(e) {
                    if (e.type === 'keypress' && e.which !== 13) return;
                    
                    const value = $(this).val();
                    if (!value) {
                        displayValue.show();
                        input.hide();
                        return;
                    }
                    
                    // Solo crear el artículo si es el campo código y tiene valor
                    if (field === 'codigo' && value.trim()) {
                        createNewArticle(row);
                    } else {
                        // Para otros campos, solo actualizar la vista
                        displayValue.text(value);
                        displayValue.show();
                        input.hide();
                    }
                });
                
                // Solo para selects de empresa, manejar el cambio
                if (field === 'empresa_id') {
                    input.on('change.newRow', function() {
                        const selectedText = $(this).find('option:selected').text();
                        displayValue.text(selectedText);
                        displayValue.show();
                        input.hide();
                    });
                }
            });

            // Función para crear un nuevo artículo (con protección contra duplicados)
            function createNewArticle(row) {
                const rowKey = row.data('columna') + '-' + row.find('td[data-field="numero"] .display-value').text();
                
                // Verificar si ya se está creando este artículo
                if (creatingArticles.has(rowKey)) {
                    console.log('Ya se está creando este artículo, ignorando...');
                    return;
                }
                
                // Marcar como en proceso de creación
                creatingArticles.add(rowKey);
                
                const codigo = row.find('td[data-field="codigo"] .edit-input').val();
                const empresaId = row.find('td[data-field="empresa_id"] .edit-input').val();
                const cantidad = row.find('td[data-field="cantidad"] .edit-input').val() || 1;
                
                // Validar que al menos el código esté presente
                if (!codigo || codigo.trim() === '') {
                    creatingArticles.delete(rowKey);
                    Swal.fire({
                        icon: 'warning',
                        title: 'Código requerido',
                        text: 'Debe ingresar un código para crear el artículo'
                    });
                    return;
                }
                
                // Obtener datos de la fila
                const numero = row.find('td[data-field="numero"] .display-value').text();
                const lugar = row.data('lugar');
                const columna = row.data('columna');
                
                // Obtener fecha del filtro o usar actual
                let fecha;
                const fechaFiltro = $('input[name="fecha"]').val();
                if (fechaFiltro) {
                    fecha = fechaFiltro + '-01';
                } else {
                    const today = new Date();
                    fecha = today.getFullYear() + '-' + 
                            String(today.getMonth() + 1).padStart(2, '0') + '-01';
                }
                
                const data = {
                    _token: '{{ csrf_token() }}',
                    fecha: fecha,
                    numero: numero,
                    lugar: lugar,
                    columna: columna,
                    codigo: codigo.trim().toUpperCase(),
                    empresa_id: empresaId || null,
                    cantidad: cantidad
                };
                
                console.log('Creando artículo:', data);
                
                // Deshabilitar la fila mientras se crea
                row.find('input, select').prop('disabled', true);
                
                // Mostrar loading
                Swal.fire({
                    title: 'Creando artículo...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    showConfirmButton: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
                
                $.ajax({
                    url: '{{ route("inventario.store") }}',
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        console.log('Artículo creado exitosamente:', response);
                        Swal.fire({
                            icon: 'success',
                            title: 'Artículo creado',
                            text: 'El artículo se ha creado exitosamente',
                            timer: 1500,
                            showConfirmButton: false
                        }).then(() => {
                            window.location.reload();
                        });
                    },
                    error: function(xhr) {
                        console.error('Error al crear artículo:', xhr);
                        let errorMessage = 'Error al crear el artículo';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.responseJSON && xhr.responseJSON.errors) {
                            const errors = xhr.responseJSON.errors;
                            errorMessage = Object.values(errors).flat().join(', ');
                        }
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                        
                        // Restaurar la fila y quitar del control de duplicados
                        row.find('input, select').prop('disabled', false);
                        row.find('.edit-input').hide();
                        row.find('.display-value').show();
                        creatingArticles.delete(rowKey);
                    },
                    complete: function() {
                        // Remover del control de duplicados al completar (éxito o error)
                        creatingArticles.delete(rowKey);
                    }
                });
            }
        });
    </script>
@endpush 