@push('js')
    <script>
        $(document).ready(function() {
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

            // Destruir instancias existentes de DataTables antes de reinicializar
            $('.table').each(function() {
                if ($.fn.DataTable.isDataTable(this)) {
                    $(this).DataTable().destroy();
                }
            });

            // Inicializar DataTables con configuración responsiva
            const tables = $('.table').DataTable({
                dom: '<"row"<"col-12"f>>' +
                     '<"row"<"col-12"t>>',
                ordering: true,
                searching: true,
                paging: false,
                info: false,
                responsive: {
                    details: {
                        type: 'column',
                        target: 'tr'
                    }
                },
                autoWidth: false,
                language: {
                    search: "Buscar:",
                    zeroRecords: "No se encontraron registros coincidentes",
                    searchPlaceholder: "Buscar en esta columna..."
                },
                columnDefs: [
                    {
                        targets: 0, // Primera columna (NÚMERO)
                        type: 'num',
                    }
                ],
                // Guardar estado cuando se dibuja la tabla
                drawCallback: function() {
                    saveState();
                }
            });

            // Variable para controlar el estado de expansión
            let allExpanded = false;

            // Función para expandir/contraer todas las tarjetas
            $('#toggleAll').on('click', function() {
                allExpanded = !allExpanded;
                if (allExpanded) {
                    $('.collapse').collapse('show');
                    $('.transition-icon').addClass('fa-rotate-180');
                } else {
                    $('.collapse').collapse('hide');
                    $('.transition-icon').removeClass('fa-rotate-180');
                }
                saveState();
            });

            // Botón buscar y expandir
            $('#buscarExpandir').on('click', function() {
                // Expandir todas las tarjetas
                $('.collapse').collapse('show');
                $('.transition-icon').addClass('fa-rotate-180');
                allExpanded = true;
                
                // Realizar la búsqueda
                let searchTerm = $('#busquedaGlobal').val().toLowerCase();
                $('.table').each(function() {
                    $(this).DataTable().search(searchTerm).draw();
                });
                saveState();
            });

            // Búsqueda global
            $('#busquedaGlobal').on('keyup', function() {
                let searchTerm = $(this).val().toLowerCase();
                $('.table').each(function() {
                    $(this).DataTable().search(searchTerm).draw();
                });
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

            // Edición en línea
            $('.editable').on('click', function() {
                let currentValue = $(this).find('.display-value').text().trim();
                let field = $(this).data('field');
                let id = $(this).closest('tr').data('id');
                let input = $(this).find('.edit-input');
                let displayValue = $(this).find('.display-value');
                
                displayValue.hide();
                input.show().focus().val(currentValue);

                input.on('blur keypress', function(e) {
                    if (e.type === 'keypress' && e.which !== 13) return;
                    
                    let newValue = $(this).val();
                    if (newValue === currentValue) {
                        displayValue.show();
                        input.hide();
                        return;
                    }
                    
                    let cell = $(this).closest('.editable');
                    let row = cell.closest('tr');
                    
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
                                    displayValue.text(newValue).show();
                                    input.hide();
                                    
                                    // Actualizar el valor en la fila
                                    row.find(`[data-field="${field}"] .display-value`).text(newValue);
                                    
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
                                        // El estado ya se habrá guardado por el evento beforeunload
                                        window.location.reload();
                                    });
                                } else {
                                    displayValue.show();
                                    input.hide();
                                    
                                    Swal.fire({
                                        icon: 'error',
                                        title: 'Error',
                                        text: response.message
                                    });
                                }
                            },
                            error: function(xhr) {
                                displayValue.show();
                                input.hide();
                                
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
                        input.hide();
                        
                        Swal.fire({
                            icon: 'error',
                            title: 'Error de validación',
                            text: error.message
                        });
                    }
                });

                // Cancelar edición con Escape
                input.on('keyup', function(e) {
                    if (e.key === 'Escape') {
                        displayValue.show();
                        input.hide();
                    }
                });
            });

            // Función para manejar la edición en línea de celdas vacías o con guión
            $('.table').on('click', 'td.editable', function() {
                const cell = $(this);
                const displayValue = cell.find('.display-value');
                const currentValue = displayValue.text().trim();
                
                // Si el valor actual es '-' o está vacío, tratarlo como editable
                if (currentValue === '-' || currentValue === '') {
                    const row = cell.closest('tr');
                    const field = cell.data('field');
                    
                    // Crear input según el tipo de campo
                    let input;
                    if (field === 'cantidad' || field === 'numero' || field === 'columna') {
                        input = $('<input type="number" class="form-control" value="1">');
                    } else {
                        input = $('<input type="text" class="form-control">');
                    }
                    
                    // Reemplazar el contenido de la celda con el input
                    displayValue.hide();
                    cell.append(input);
                    input.focus();
                    
                    // Manejar la pérdida de foco
                    input.on('blur', function() {
                        const value = $(this).val();
                        if (!value) {
                            displayValue.text('-').show();
                            input.remove();
                            return;
                        }
                        
                        // Obtener la fecha actual en formato YYYY-MM-DD
                        const today = new Date();
                        const fecha = today.getFullYear() + '-' + 
                                    String(today.getMonth() + 1).padStart(2, '0') + '-' + 
                                    String(today.getDate()).padStart(2, '0');
                        
                        // Recopilar datos para el nuevo artículo
                        const articleData = {
                            fecha: fecha, // Agregar la fecha actual
                            numero: row.find('td[data-field="numero"] .display-value').text().trim() === '-' ? 
                                   row.find('td[data-field="numero"] input').val() || 1 : 
                                   row.find('td[data-field="numero"] .display-value').text().trim(),
                            lugar: row.find('td[data-field="lugar"] .display-value').text().trim() === '-' ? 
                                  row.find('td[data-field="lugar"] input').val() || 'SOPORTE 1' : 
                                  row.find('td[data-field="lugar"] .display-value').text().trim(),
                            columna: row.find('td[data-field="columna"] .display-value').text().trim() === '-' ? 
                                    row.find('td[data-field="columna"] input').val() || 1 : 
                                    row.find('td[data-field="columna"] .display-value').text().trim(),
                            codigo: row.find('td[data-field="codigo"] .display-value').text().trim() === '-' ? 
                                   row.find('td[data-field="codigo"] input').val() : 
                                   row.find('td[data-field="codigo"] .display-value').text().trim(),
                            cantidad: row.find('td[data-field="cantidad"] .display-value').text().trim() === '-' ? 
                                     row.find('td[data-field="cantidad"] input').val() || 1 : 
                                     row.find('td[data-field="cantidad"] .display-value').text().trim()
                        };

                        // Actualizar el campo actual con el nuevo valor
                        articleData[field] = value;
                        
                        // Asegurarse de que todos los campos requeridos tengan valores válidos
                        if (!articleData.numero || !articleData.lugar || !articleData.columna || !articleData.codigo || !articleData.cantidad) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error de validación',
                                text: 'Todos los campos son requeridos'
                            });
                            displayValue.text('-').show();
                            input.remove();
                            return;
                        }
                        
                        // Verificar si tenemos suficientes datos para crear el artículo
                        $.ajax({
                            url: '{{ route("inventario.store") }}',
                            method: 'POST',
                            data: {
                                _token: '{{ csrf_token() }}',
                                ...articleData
                            },
                            success: function(response) {
                                // Actualizar todas las celdas de la fila
                                Object.keys(articleData).forEach(key => {
                                    if (key !== 'fecha') { // No mostrar la fecha en la tabla
                                        const targetCell = row.find(`td[data-field="${key}"] .display-value`);
                                        targetCell.text(articleData[key]).show();
                                    }
                                });
                                
                                // Remover los inputs
                                row.find('input').remove();
                                
                                // Actualizar el ID de la fila con el nuevo ID del artículo
                                if (response.id) {
                                    row.attr('data-id', response.id);
                                }
                                
                                // Mostrar mensaje de éxito
                                Swal.fire({
                                    icon: 'success',
                                    title: 'Artículo creado exitosamente',
                                    showConfirmButton: false,
                                    timer: 1500
                                }).then(() => {
                                    // El estado ya se habrá guardado por el evento beforeunload
                                    window.location.reload();
                                });
                            },
                            error: function(xhr) {
                                console.error('Error response:', xhr.responseJSON);
                                // Mostrar mensaje de error
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error al crear el artículo',
                                    text: xhr.responseJSON?.message || 'Error desconocido',
                                });
                                displayValue.text('-').show();
                                input.remove();
                            }
                        });
                    });
                    
                    // Manejar la tecla Enter
                    input.on('keypress', function(e) {
                        if (e.which === 13) {
                            $(this).blur();
                        }
                    });
                    
                    // Manejar la tecla Escape
                    input.on('keyup', function(e) {
                        if (e.key === 'Escape') {
                            displayValue.show();
                            input.remove();
                        }
                    });
                }
            });
        });
    </script>
@endpush 