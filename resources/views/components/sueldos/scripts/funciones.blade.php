@push('js')
<script>
    // Variables globales para almacenar datos de los roles
    let datosRoles = {};

    // Función para formatear moneda
    function formatCurrency(amount) {
        return new Intl.NumberFormat('es-EC', {
            style: 'currency',
            currency: 'USD'
        }).format(amount);
    }

    // Función para formatear fecha
    function formatDate(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('es-ES', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }

    // Función para formatear hora
    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Función para obtener las URLs de API según la sucursal seleccionada
    function getApiUrls(tipo) {
        const sucursal = document.getElementById('filtroSucursal').value;
        const urls = [];

        if (sucursal === '' || sucursal === 'matriz') {
            urls.push(`https://opticas.xyz/api/${tipo}`);
        }
        if (sucursal === '' || sucursal === 'rocio') {
            urls.push(`https://escleroptica2.opticas.xyz/api/${tipo}`);
        }
        if (sucursal === '' || sucursal === 'norte') {
            urls.push(`https://sucursal3.opticas.xyz/api/${tipo}`);
        }

        return urls;
    }

    // Función para obtener retiros del empleado
    async function obtenerRetiros(userId, nombre, ano, mes) {
        const urls = getApiUrls('caja/retiros').map(url => `${url}?ano=${ano}&mes=${mes}`);
        
        if (!datosRoles[userId]) {
            datosRoles[userId] = {
                retiros: [],
                retiros_total: 0,
                pedidos: [],
                pedidos_total: 0,
                historial: {
                    ingresos: 0,
                    egresos: 0
                },
                movimientos: []
            };
        }
        
        datosRoles[userId].retiros = [];
        datosRoles[userId].retiros_total = 0;
        
        for (const url of urls) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (data.retiros) {
                    const retirosEmpleado = data.retiros.filter(retiro => 
                        retiro.usuario.toLowerCase() === nombre.toLowerCase() &&
                        !retiro.motivo.toLowerCase().includes('deposito') &&
                        !retiro.motivo.toLowerCase().includes('depósito')
                    );
                    datosRoles[userId].retiros = [...datosRoles[userId].retiros, ...retirosEmpleado];
                    datosRoles[userId].retiros_total += retirosEmpleado.reduce((sum, retiro) => 
                        sum + Math.abs(parseFloat(retiro.valor)), 0
                    );
                }
            } catch (error) {
                console.error('Error al obtener retiros:', error);
            }
        }
    }

    // Función para obtener pedidos del empleado
    async function obtenerPedidos(userId, nombre, ano, mes) {
        const urls = getApiUrls('pedidos').map(url => `${url}?ano=${ano}&mes=${mes}`);
        
        if (!datosRoles[userId]) {
            datosRoles[userId] = {
                retiros: [],
                retiros_total: 0,
                pedidos: [],
                pedidos_total: 0,
                historial: {
                    ingresos: 0,
                    egresos: 0
                },
                movimientos: []
            };
        }
        
        datosRoles[userId].pedidos = [];
        datosRoles[userId].pedidos_total = 0;
        
        for (const url of urls) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (data.success && data.data.pedidos) {
                    const pedidosEmpleado = data.data.pedidos.filter(pedido => 
                        pedido.usuario.toLowerCase() === nombre.toLowerCase()
                    );
                    datosRoles[userId].pedidos = [...datosRoles[userId].pedidos, ...pedidosEmpleado];
                    datosRoles[userId].pedidos_total += pedidosEmpleado.reduce((sum, pedido) => 
                        sum + parseFloat(pedido.total), 0
                    );
                }
            } catch (error) {
                console.error('Error al obtener pedidos:', error);
            }
        }
    }

    // Función para obtener historial del empleado
    async function obtenerHistorial(userId, nombre, ano, mes) {
        const urls = getApiUrls('caja/historial').map(url => `${url}?ano=${ano}&mes=${mes}`);
        
        if (!datosRoles[userId]) {
            datosRoles[userId] = {
                retiros: [],
                retiros_total: 0,
                pedidos: [],
                pedidos_total: 0,
                historial: {
                    ingresos: 0,
                    egresos: 0
                },
                movimientos: []
            };
        }
        
        datosRoles[userId].historial = { ingresos: 0, egresos: 0 };
        datosRoles[userId].movimientos = [];
        
        for (const url of urls) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (data.success && data.data.movimientos) {
                    const movimientosEmpleado = data.data.movimientos.filter(mov => 
                        mov.usuario.toLowerCase() === nombre.toLowerCase()
                    );
                    
                    movimientosEmpleado.forEach(mov => {
                        mov.url = url;
                        const monto = Math.abs(parseFloat(mov.monto));
                        if (mov.descripcion === 'Apertura') {
                            datosRoles[userId].historial.ingresos += monto;
                        } else {
                            datosRoles[userId].historial.egresos += monto;
                        }
                        datosRoles[userId].movimientos.push(mov);
                    });
                }
            } catch (error) {
                console.error('Error al obtener historial:', error);
            }
        }
    }

    // Función para actualizar el rol de pagos de un usuario específico
    function actualizarRolPagos(userId, nombre) {
        try {
            const mes = document.getElementById('filtroMes').value;
            const ano = document.getElementById('filtroAno').value;
            
            // Actualizar período
            document.getElementById(`periodo_${userId}`).textContent = `${mes}/${ano}`;
            
            // Actualizar total
            document.getElementById(`total_${userId}`).textContent = formatCurrency(datosRoles[userId].pedidos_total || 0);

            // Agrupar movimientos, pedidos y retiros por fecha
            const movimientosPorFecha = agruparMovimientosPorFecha(datosRoles[userId].movimientos);
            const pedidosPorFecha = agruparPedidosPorFecha(datosRoles[userId].pedidos || []);
            const retirosPorFecha = agruparRetirosPorFecha(datosRoles[userId].retiros || []);
            
            // Generar filas para la tabla
            const filas = [];
            const todasLasFechas = new Set([
                ...Object.keys(movimientosPorFecha),
                ...Object.keys(pedidosPorFecha),
                ...Object.keys(retirosPorFecha)
            ]);

            Array.from(todasLasFechas).sort().forEach(fecha => {
                const movimientosDia = movimientosPorFecha[fecha] || { apertura: null, cierre: null };
                const pedidosDelDia = pedidosPorFecha[fecha] || [];
                const retirosDelDia = retirosPorFecha[fecha] || [];
                
                const totalPedidosDia = pedidosDelDia.reduce((sum, pedido) => sum + parseFloat(pedido.total), 0);
                const totalRetirosDia = retirosDelDia.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);

                filas.push(`
                    <tr>
                        <td>${formatDate(fecha)}</td>
                        <td>
                            ${movimientosDia.apertura ? 
                                `<span class="badge badge-apertura">
                                    APERTURA: ${formatCurrency(Math.abs(movimientosDia.apertura.monto))}
                                    <span class="hora-movimiento">${formatTime(movimientosDia.apertura.fecha)}</span>
                                </span>` : ''
                            }
                            ${movimientosDia.cierre ? 
                                `<br><span class="badge badge-cierre">
                                    CIERRE: ${formatCurrency(Math.abs(movimientosDia.cierre.monto))}
                                    <span class="hora-movimiento">${formatTime(movimientosDia.cierre.fecha)}</span>
                                </span>` : ''
                            }
                        </td>
                        <td>
                            ${movimientosDia.apertura ? 
                                `<span class="sucursal-badge ${getSucursalClass(getSucursalName(movimientosDia.apertura.url))}">
                                    ${getSucursalName(movimientosDia.apertura.url)}
                                </span>` : ''
                            }
                        </td>
                        <td>
                            ${pedidosDelDia.length > 0 ? 
                                `<div class="pedidos-dia">
                                    <strong>Total: ${formatCurrency(totalPedidosDia)}</strong>
                                    <ul class="list-unstyled mb-0">
                                        ${pedidosDelDia.map(pedido => `
                                            <li>
                                                <small>
                                                    ${formatTime(pedido.fecha)} - Orden: ${pedido.numero_orden}
                                                    <span class="text-success">${formatCurrency(pedido.total)}</span>
                                                </small>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>` : 
                                '<small class="text-muted">Sin pedidos</small>'
                            }
                        </td>
                        <td>
                            ${retirosDelDia.length > 0 ? 
                                `<div class="retiros-dia">
                                    <strong>Total: ${formatCurrency(totalRetirosDia)}</strong>
                                    <ul class="list-unstyled mb-0">
                                        ${retirosDelDia.map(retiro => `
                                            <li>
                                                <small>
                                                    ${formatTime(retiro.fecha)} - ${retiro.motivo}
                                                    <span class="text-danger">${formatCurrency(Math.abs(retiro.valor))}</span>
                                                </small>
                                            </li>
                                        `).join('')}
                                    </ul>
                                </div>` : 
                                '<small class="text-muted">Sin retiros</small>'
                            }
                        </td>
                    </tr>
                `);
            });

            document.getElementById(`desglose_${userId}`).innerHTML = filas.join('');

            // Verificar si hay datos
            const hayDatos = todasLasFechas.size > 0 || 
                           datosRoles[userId].pedidos_total > 0 || 
                           datosRoles[userId].retiros_total > 0 ||
                           datosRoles[userId].historial.ingresos > 0 ||
                           datosRoles[userId].historial.egresos > 0;

            // Mostrar u ocultar el contenedor del rol
            const contenedorRol = document.getElementById(`rol-usuario-${userId}`);
            if (contenedorRol) {
                contenedorRol.style.display = hayDatos ? 'block' : 'none';
            }

            return hayDatos;

        } catch (error) {
            console.error('Error al actualizar el rol:', error);
            alert('Error al actualizar el rol de pagos: ' + error.message);
            return false;
        }
    }

    // Función para obtener el nombre de la sucursal basado en la URL
    function getSucursalName(url) {
        if (url.includes('opticas.xyz') && !url.includes('escleroptica2') && !url.includes('sucursal3')) {
            return 'MATRIZ';
        } else if (url.includes('escleroptica2')) {
            return 'ROCÍO';
        } else if (url.includes('sucursal3')) {
            return 'NORTE';
        }
        return 'DESCONOCIDO';
    }

    // Función para obtener la clase CSS de la sucursal
    function getSucursalClass(sucursal) {
        switch (sucursal) {
            case 'MATRIZ':
                return 'sucursal-matriz';
            case 'ROCÍO':
                return 'sucursal-rocio';
            case 'NORTE':
                return 'sucursal-norte';
            default:
                return '';
        }
    }

    // Función para agrupar movimientos por fecha
    function agruparMovimientosPorFecha(movimientos) {
        const movimientosPorFecha = {};
        movimientos.forEach(mov => {
            const fecha = mov.fecha.split('T')[0];
            if (!movimientosPorFecha[fecha]) {
                movimientosPorFecha[fecha] = {
                    apertura: null,
                    cierre: null
                };
            }
            if (mov.descripcion === 'Apertura') {
                movimientosPorFecha[fecha].apertura = mov;
            } else {
                movimientosPorFecha[fecha].cierre = mov;
            }
        });
        return movimientosPorFecha;
    }

    // Función para agrupar pedidos por fecha
    function agruparPedidosPorFecha(pedidos) {
        const pedidosPorFecha = {};
        pedidos.forEach(pedido => {
            const fecha = pedido.fecha.split('T')[0];
            if (!pedidosPorFecha[fecha]) {
                pedidosPorFecha[fecha] = [];
            }
            pedidosPorFecha[fecha].push(pedido);
        });
        return pedidosPorFecha;
    }

    // Función para agrupar retiros por fecha
    function agruparRetirosPorFecha(retiros) {
        const retirosPorFecha = {};
        retiros.forEach(retiro => {
            const fecha = retiro.fecha.split('T')[0];
            if (!retirosPorFecha[fecha]) {
                retirosPorFecha[fecha] = [];
            }
            retirosPorFecha[fecha].push(retiro);
        });
        return retirosPorFecha;
    }

    // Función principal para obtener el rol de pagos
    async function obtenerRolPagos(userId, nombre) {
        const ano = document.getElementById('filtroAno').value;
        const mes = document.getElementById('filtroMes').value;

        if (!ano || !mes) {
            alert('Por favor seleccione año y mes');
            return;
        }

        try {
            await Promise.all([
                obtenerRetiros(userId, nombre, ano, mes),
                obtenerPedidos(userId, nombre, ano, mes),
                obtenerHistorial(userId, nombre, ano, mes)
            ]);

            actualizarRolPagos(userId, nombre);
        } catch (error) {
            console.error('Error al generar rol:', error);
            alert('Error al generar el rol de pagos');
        }
    }

    // Función para imprimir rol de pagos
    function imprimirRolPagos(userId) {
        const contenido = document.getElementById(`rol-usuario-${userId}`);
        const ventanaImpresion = window.open('', '_blank');
        ventanaImpresion.document.write(`
            <html>
                <head>
                    <title>Rol de Pagos</title>
                    <style>
                        body { font-family: Arial, sans-serif; }
                        .table { width: 100%; border-collapse: collapse; margin-bottom: 1rem; }
                        .table th, .table td { border: 1px solid #ddd; padding: 8px; }
                        .text-right { text-align: right; }
                        .text-center { text-align: center; }
                    </style>
                </head>
                <body>
                    ${contenido.innerHTML}
                </body>
            </html>
        `);
        ventanaImpresion.document.close();
        ventanaImpresion.print();
    }
</script>
@endpush 