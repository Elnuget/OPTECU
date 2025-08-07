@push('js')
<script>
    class RolPagosUtils {
        static formatCurrency(amount) {
            return new Intl.NumberFormat('es-EC', {
                style: 'currency',
                currency: 'USD'
            }).format(amount);
        }

        static formatDate(dateString) {
            const fecha = new Date(dateString);
            // Convertir a zona horaria de Quito (UTC-5)
            const options = {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                timeZone: 'America/Guayaquil'
            };
            return fecha.toLocaleDateString('es-EC', options);
        }

        static formatTime(dateString) {
            const fecha = new Date(dateString);
            // Convertir a zona horaria de Quito (UTC-5)
            const options = {
                hour: '2-digit',
                minute: '2-digit',
                timeZone: 'America/Guayaquil'
            };
            return fecha.toLocaleTimeString('es-EC', options);
        }

        static getEmpresaName(empresaId) {
            // Obtener el nombre de la empresa desde el select
            const selectEmpresa = document.getElementById('filtroEmpresa');
            if (empresaId && selectEmpresa) {
                const option = selectEmpresa.querySelector(`option[value="${empresaId}"]`);
                return option ? option.textContent : 'DESCONOCIDA';
            }
            return 'TODAS';
        }

        static agruparPorFecha(items, fechaKey = 'fecha') {
            return items.reduce((acc, item) => {
                const fecha = new Date(item[fechaKey]);
                // Usar la zona horaria de Quito para agrupar
                const fechaQuito = new Date(fecha.toLocaleString('en-US', { timeZone: 'America/Guayaquil' }));
                const fechaStr = fechaQuito.toISOString().split('T')[0];
                
                if (!acc[fechaStr]) {
                    acc[fechaStr] = [];
                }
                acc[fechaStr].push(item);
                return acc;
            }, {});
        }
    }

    class RolPagosLocal {
        constructor(userId, nombre) {
            this.userId = userId;
            this.nombre = nombre;
            this.data = {
                retiros: [],
                retiros_total: 0,
                pedidos: [],
                pedidos_total: 0,
                historial: {
                    ingresos: 0,
                    egresos: 0
                },
                movimientos: [],
                registrosCobro: []
            };
        }

        async obtenerDatosLocales(ano, mes) {
            try {
                const empresaId = document.getElementById('filtroEmpresa')?.value || '';
                
                const response = await fetch('/api/sueldos/datos-rol-pagos', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        user_id: this.userId,
                        ano: ano,
                        mes: mes,
                        empresa_id: empresaId
                    })
                });

                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }

                const data = await response.json();
                
                if (data.success) {
                    this.data.retiros = data.data.retiros || [];
                    this.data.retiros_total = data.data.retiros_total || 0;
                    this.data.pedidos = data.data.pedidos || [];
                    this.data.pedidos_total = data.data.pedidos_total || 0;
                    this.data.historial = data.data.historial || { ingresos: 0, egresos: 0 };
                    this.data.movimientos = data.data.movimientos || [];
                    this.data.registrosCobro = data.data.registrosCobro || [];
                }
            } catch (error) {
                console.error('Error al obtener datos locales:', error);
                throw error;
            }
        }

        async obtenerRegistrosCobro(ano, mes) {
            try {
                // Los registros de cobro ya están incluidos en obtenerDatosLocales
                // Solo necesitamos actualizar el total en la interfaz si existe
                const totalElement = document.getElementById(`total_registros_${this.userId}`);
                if (totalElement) {
                    const total = this.data.registrosCobro.reduce((sum, registro) => sum + parseFloat(registro.valor), 0);
                    totalElement.textContent = `$${total.toFixed(2)}`;
                }
            } catch (error) {
                console.error('Error al obtener registros de cobro:', error);
            }
        }

        actualizarUI() {
            try {
                const mes = document.getElementById('filtroMes').value;
                const ano = document.getElementById('filtroAno').value;
                
                // Calcular totales
                const totalPedidos = this.data.pedidos_total;
                const totalRetiros = this.data.retiros_total;

                // Actualizar período y total
                document.getElementById(`periodo_${this.userId}`).textContent = `${mes}/${ano}`;
                document.getElementById(`total_${this.userId}`).textContent = RolPagosUtils.formatCurrency(totalPedidos || 0);

                // Generar filas de la tabla basándose en los movimientos locales
                const todasLasFechas = this.obtenerFechasUnicas();
                const filas = this.generarFilasTablaLocal(todasLasFechas);

                document.getElementById(`desglose_${this.userId}`).innerHTML = filas.join('');

                return true;
            } catch (error) {
                console.error('Error al actualizar UI:', error);
                return false;
            }
        }

        obtenerFechasUnicas() {
            const fechas = new Set();
            
            // Agregar fechas de movimientos
            this.data.movimientos.forEach(mov => {
                const fecha = new Date(mov.fecha);
                const fechaKey = fecha.toISOString().split('T')[0];
                fechas.add(fechaKey);
            });
            
            // Agregar fechas de pedidos
            this.data.pedidos.forEach(pedido => {
                const fecha = new Date(pedido.fecha);
                const fechaKey = fecha.toISOString().split('T')[0];
                fechas.add(fechaKey);
            });
            
            // Agregar fechas de retiros
            this.data.retiros.forEach(retiro => {
                const fecha = new Date(retiro.fecha);
                const fechaKey = fecha.toISOString().split('T')[0];
                fechas.add(fechaKey);
            });
            
            return Array.from(fechas);
        }

        generarFilasTablaLocal(fechas) {
            if (fechas.length === 0) {
                return [`
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i> NO HAY DATOS PARA ESTE PERÍODO
                        </td>
                    </tr>
                `];
            }

            const mesSeleccionado = document.getElementById('filtroMes').value;
            const anoSeleccionado = document.getElementById('filtroAno').value;

            // Filtrar fechas que correspondan al mes y año seleccionados
            const fechasFiltradas = fechas.filter(fecha => {
                const fechaObj = new Date(fecha);
                const mes = (fechaObj.getMonth() + 1).toString().padStart(2, '0');
                const ano = fechaObj.getFullYear().toString();
                return mes === mesSeleccionado && ano === anoSeleccionado;
            });

            if (fechasFiltradas.length === 0) {
                return [`
                    <tr>
                        <td colspan="5" class="text-center text-muted">
                            <i class="fas fa-info-circle"></i> NO HAY DATOS PARA EL PERÍODO ${mesSeleccionado}/${anoSeleccionado}
                        </td>
                    </tr>
                `];
            }

            return fechasFiltradas.sort().map(fecha => {
                // Obtener datos para esta fecha específica
                const movimientosDelDia = this.data.movimientos.filter(mov => {
                    const fechaMov = new Date(mov.fecha).toISOString().split('T')[0];
                    return fechaMov === fecha;
                });

                const pedidosDelDia = this.data.pedidos.filter(pedido => {
                    const fechaPedido = new Date(pedido.fecha).toISOString().split('T')[0];
                    return fechaPedido === fecha;
                });

                const retirosDelDia = this.data.retiros.filter(retiro => {
                    const fechaRetiro = new Date(retiro.fecha).toISOString().split('T')[0];
                    return fechaRetiro === fecha;
                });

                return this.generarFilaHTMLLocal(fecha, {
                    movimientos: movimientosDelDia,
                    pedidos: pedidosDelDia,
                    retiros: retirosDelDia
                });
            });
        }

        generarFilaHTMLLocal(fecha, datos) {
            const { movimientos, pedidos, retiros } = datos;

            // Calcular totales del día
            const totalPedidosDia = pedidos.reduce((sum, pedido) => sum + parseFloat(pedido.total), 0);
            const totalRetirosDia = retiros.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);

            // Formatear la fecha para mostrar
            const fechaMostrar = RolPagosUtils.formatDate(fecha);

            // Buscar si existe un registro de cobro para esta fecha
            const registroCobro = this.data.registrosCobro.find(registro => {
                const fechaRegistro = new Date(registro.fecha).toISOString().split('T')[0];
                return fechaRegistro === fecha;
            });

            return `
                <tr>
                    <td data-fecha="${fecha}">
                        ${fechaMostrar}
                    </td>
                    <td>
                        ${this.generarHTMLMovimientosLocal(movimientos)}
                    </td>
                    <td>
                        ${this.generarHTMLPedidosLocal(pedidos, totalPedidosDia)}
                    </td>
                    <td>
                        ${this.generarHTMLRetirosLocal(retiros, totalRetirosDia)}
                    </td>
                    <td>
                        <input type="number" 
                               class="form-control valor-editable" 
                               data-fecha="${fecha}"
                               value="${registroCobro ? registroCobro.valor : '0.00'}" 
                               step="0.01" 
                               min="0">
                    </td>
                </tr>
            `;
        }

        generarHTMLMovimientosLocal(movimientos) {
            if (!movimientos || movimientos.length === 0) {
                return '<small class="text-muted">Sin movimientos</small>';
            }

            // Agrupar por empresa
            const movimientosPorEmpresa = movimientos.reduce((acc, mov) => {
                const empresa = mov.empresa || 'SIN ESPECIFICAR';
                if (!acc[empresa]) {
                    acc[empresa] = { apertura: [], cierre: [] };
                }
                
                if (mov.descripcion && mov.descripcion.toLowerCase().includes('apertura')) {
                    acc[empresa].apertura.push(mov);
                } else {
                    acc[empresa].cierre.push(mov);
                }
                return acc;
            }, {});

            return Object.entries(movimientosPorEmpresa).map(([empresa, movs]) => `
                <div class="empresa-movimientos mb-2">
                    <div class="badge badge-secondary mb-1">
                        <i class="fas fa-building mr-1"></i>${empresa}
                    </div>
                    ${movs.apertura.map(mov => `
                        <div class="badge badge-apertura d-block mb-1">
                            APERTURA: ${RolPagosUtils.formatCurrency(Math.abs(mov.monto || 0))}
                            <div class="mt-1">
                                <i class="fas fa-clock mr-1"></i>
                                ${RolPagosUtils.formatTime(mov.fecha)}
                            </div>
                        </div>
                    `).join('')}
                    ${movs.cierre.map(mov => `
                        <div class="badge badge-cierre d-block">
                            CIERRE: ${RolPagosUtils.formatCurrency(Math.abs(mov.monto || 0))}
                            <div class="mt-1">
                                <i class="fas fa-clock mr-1"></i>
                                ${RolPagosUtils.formatTime(mov.fecha)}
                            </div>
                        </div>
                    `).join('')}
                </div>
            `).join('') || '<small class="text-muted">Sin movimientos</small>';
        }

        generarHTMLPedidosLocal(pedidos, total) {
            if (pedidos.length === 0) {
                return '<small class="text-muted">Sin pedidos</small>';
            }

            // Agrupar pedidos por empresa
            const pedidosPorEmpresa = pedidos.reduce((acc, pedido) => {
                const empresa = pedido.empresa || 'SIN ESPECIFICAR';
                if (!acc[empresa]) {
                    acc[empresa] = {
                        pedidos: [],
                        total: 0
                    };
                }
                acc[empresa].pedidos.push(pedido);
                acc[empresa].total += parseFloat(pedido.total);
                return acc;
            }, {});

            return `
                <div class="operaciones-container">
                    ${Object.entries(pedidosPorEmpresa).map(([empresa, data]) => `
                        <div class="empresa-operaciones mb-3">
                            <div class="badge badge-secondary mb-2">
                                <i class="fas fa-building mr-1"></i>${empresa}
                            </div>
                            <div class="pedidos-dia">
                                <strong>Total: ${RolPagosUtils.formatCurrency(data.total)}</strong>
                                <ul class="list-unstyled mb-0">
                                    ${data.pedidos.map(pedido => `
                                        <li>
                                            <small>
                                                ${RolPagosUtils.formatTime(pedido.fecha)} - ${pedido.cliente || 'Cliente no especificado'}
                                                <span class="text-success">${RolPagosUtils.formatCurrency(pedido.total)}</span>
                                            </small>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                        </div>
                    `).join('')}
                    <div class="total-general">
                        <strong>TOTAL GENERAL: ${RolPagosUtils.formatCurrency(total)}</strong>
                    </div>
                </div>
            `;
        }

        generarHTMLRetirosLocal(retiros, total) {
            if (retiros.length === 0) {
                return '<small class="text-muted">Sin retiros</small>';
            }

            // Agrupar retiros por empresa
            const retirosPorEmpresa = retiros.reduce((acc, retiro) => {
                const empresa = retiro.empresa || 'SIN ESPECIFICAR';
                if (!acc[empresa]) {
                    acc[empresa] = {
                        retiros: [],
                        total: 0
                    };
                }
                acc[empresa].retiros.push(retiro);
                acc[empresa].total += Math.abs(parseFloat(retiro.valor));
                return acc;
            }, {});

            return `
                <div class="operaciones-container">
                    ${Object.entries(retirosPorEmpresa).map(([empresa, data]) => `
                        <div class="empresa-operaciones mb-3">
                            <div class="badge badge-secondary mb-2">
                                <i class="fas fa-building mr-1"></i>${empresa}
                            </div>
                            <div class="retiros-dia">
                                <strong>Total: ${RolPagosUtils.formatCurrency(data.total)}</strong>
                                <ul class="list-unstyled mb-0">
                                    ${data.retiros.map(retiro => `
                                        <li>
                                            <small>
                                                ${RolPagosUtils.formatTime(retiro.fecha)} - ${retiro.motivo || 'Motivo no especificado'}
                                                <span class="text-danger">${RolPagosUtils.formatCurrency(Math.abs(retiro.valor))}</span>
                                            </small>
                                        </li>
                                    `).join('')}
                                </ul>
                            </div>
                        </div>
                    `).join('')}
                    <div class="total-general">
                        <strong>TOTAL GENERAL: ${RolPagosUtils.formatCurrency(total)}</strong>
                    </div>
                </div>
            `;
        }
    }

    // Función principal para obtener el rol de pagos
    async function obtenerRolPagos(userId, nombre, ano, mes) {
        if (!ano || !mes) {
            alert('Por favor seleccione año y mes');
            return;
        }

        try {
            const rolPagos = new RolPagosLocal(userId, nombre);
            
            // Obtener todos los datos locales
            await rolPagos.obtenerDatosLocales(ano, mes);
            
            // Actualizar la interfaz
            rolPagos.actualizarUI();
        } catch (error) {
            console.error('Error al generar rol:', error);
            alert('Error al generar el rol de pagos');
        }
    }

    // Función para limpiar todos los modales
    function limpiarModales() {
        // Cerrar cualquier modal de SweetAlert2 que esté abierto
        if (Swal.isVisible()) {
            Swal.close();
        }
        
        // Eliminar cualquier overlay residual de SweetAlert2
        const overlays = document.getElementsByClassName('swal2-container');
        while (overlays.length > 0) {
            overlays[0].remove();
        }
        
        // Eliminar cualquier backdrop residual
        const backdrops = document.getElementsByClassName('swal2-backdrop-show');
        while (backdrops.length > 0) {
            backdrops[0].remove();
        }
        
        // Restaurar el scroll del body si está bloqueado
        document.body.classList.remove('swal2-shown', 'swal2-height-auto');
        document.body.style.paddingRight = '';
        document.body.style.overflow = '';
    }

    // Función para guardar el valor del sueldo
    async function guardarValorSueldo(fecha, valor, userId) {
        try {
            // Limpiar cualquier modal residual antes de empezar
            limpiarModales();

            // Mostrar indicador de carga rápido
            const toast = Swal.mixin({
                toast: true,
                position: 'top-end',
                showConfirmButton: false,
                timer: 1000,
                timerProgressBar: true
            });

            await toast.fire({
                icon: 'info',
                title: 'GUARDANDO...'
            });

            const response = await fetch('/sueldos/guardar-valor', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                },
                body: JSON.stringify({
                    fecha: fecha,
                    valor: valor,
                    user_id: userId
                })
            });

            // Asegurarse de que el modal de carga esté cerrado
            limpiarModales();

            if (!response.ok) {
                throw new Error(`Error en la respuesta del servidor: ${response.status}`);
            }

            const data = await response.json();

            if (data.success) {
                // Actualizar la interfaz si es necesario
                const $input = $(`.valor-editable[data-fecha="${fecha}"]`);
                $input.val(valor.toFixed(2));

                // Mostrar mensaje de éxito rápido
                await toast.fire({
                    icon: 'success',
                    title: data.data.wasRecentlyCreated ? 'VALOR REGISTRADO' : 'VALOR ACTUALIZADO'
                });
            } else {
                throw new Error(data.mensaje || 'Error al guardar el valor');
            }
        } catch (error) {
            console.error('Error al guardar:', error);
            
            // Limpiar modales antes de mostrar el error
            limpiarModales();

            await Swal.fire({
                icon: 'error',
                title: '¡ERROR!',
                text: error.message || 'Error al guardar el valor. Por favor, intente nuevamente.',
                showConfirmButton: true,
                confirmButtonText: 'ENTENDIDO',
                confirmButtonColor: '#dc3545',
                customClass: {
                    popup: 'animated shake faster'
                },
                willClose: () => {
                    limpiarModales();
                }
            });

            // Restaurar el valor anterior en caso de error
            const $input = $(`.valor-editable[data-fecha="${fecha}"]`);
            $input.val('0.00').focus();
        }
    }

    // Agregar el evento para manejar cambios en los valores editables
    $(document).on('change', '.valor-editable', async function() {
        const $input = $(this);
        const valor = parseFloat($input.val());
        const fecha = $input.data('fecha'); // Usar el atributo data-fecha
        const userId = $('#selectUsuario').val();

        // Validaciones más estrictas
        if (!userId) {
            limpiarModales();
            await Swal.fire({
                icon: 'warning',
                title: '¡ATENCIÓN!',
                text: 'Por favor seleccione un usuario',
                showConfirmButton: true,
                confirmButtonText: 'ENTENDIDO',
                confirmButtonColor: '#ffc107',
                willClose: () => {
                    limpiarModales();
                }
            });
            $input.val('0.00').focus();
            return;
        }

        if (!valor || isNaN(valor) || valor <= 0) {
            limpiarModales();
            await Swal.fire({
                icon: 'warning',
                title: '¡VALOR INVÁLIDO!',
                text: 'Por favor ingrese un valor numérico mayor a 0',
                showConfirmButton: true,
                confirmButtonText: 'ENTENDIDO',
                confirmButtonColor: '#ffc107',
                willClose: () => {
                    limpiarModales();
                }
            });
            $input.val('0.00').focus();
            return;
        }

        // Validar que la fecha sea válida
        if (!fecha) {
            limpiarModales();
            await Swal.fire({
                icon: 'error',
                title: '¡ERROR!',
                text: 'No se pudo obtener la fecha correctamente',
                showConfirmButton: true,
                confirmButtonText: 'ENTENDIDO',
                confirmButtonColor: '#dc3545',
                willClose: () => {
                    limpiarModales();
                }
            });
            return;
        }

        // Guardar directamente sin confirmación
        await guardarValorSueldo(fecha, valor, userId);
    });

    // Función para limpiar modales residuales al cargar la página
    $(document).ready(function() {
        limpiarModales();
    });

    // Agregar evento para limpiar modales al cambiar de pestaña o minimizar
    $(window).on('blur', limpiarModales);

    // Agregar evento para limpiar modales al presionar ESC
    $(document).on('keydown', function(e) {
        if (e.key === 'Escape') {
            limpiarModales();
        }
    });
</script>
@endpush 