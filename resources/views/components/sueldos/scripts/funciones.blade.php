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
            return new Date(dateString).toLocaleDateString('es-ES', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit'
            });
        }

        static formatTime(dateString) {
            return new Date(dateString).toLocaleTimeString('es-ES', {
                hour: '2-digit',
                minute: '2-digit'
            });
        }

        static getSucursalName(url) {
            // Asegurarnos de que la URL sea una cadena y la convertimos a minúsculas
            const urlLower = (url || '').toLowerCase();
            
            console.log('Detectando sucursal para URL:', urlLower); // Debug

            // Orden específico de verificación para evitar falsos positivos
            if (urlLower.includes('escleroptica2.opticas.xyz')) {
                return 'ROCÍO';
            } else if (urlLower.includes('sucursal3.opticas.xyz')) {
                return 'NORTE';
            } else if (urlLower.includes('opticas.xyz')) {
                return 'MATRIZ';
            }
            
            return 'DESCONOCIDA';
        }

        static agruparPorFecha(items, fechaKey = 'fecha') {
            return items.reduce((acc, item) => {
                const fecha = item[fechaKey].split('T')[0];
                if (!acc[fecha]) {
                    acc[fecha] = [];
                }
                acc[fecha].push(item);
                return acc;
            }, {});
        }
    }

    class RolPagosAPI {
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
                movimientos: []
            };
        }

        static getApiUrls(tipo) {
            const sucursal = document.getElementById('filtroSucursal').value;
            const urls = [];

            // Modificamos el orden y la lógica de las URLs
            if (sucursal === '' || sucursal === 'matriz') {
                urls.push(`https://opticas.xyz/api/${tipo}`);
            }
            if (sucursal === '' || sucursal === 'rocio') {
                urls.push(`https://escleroptica2.opticas.xyz/api/${tipo}`);
            }
            if (sucursal === '' || sucursal === 'norte') {
                urls.push(`https://sucursal3.opticas.xyz/api/${tipo}`);
            }

            console.log('URLs generadas para', tipo, ':', urls); // Debug
            return urls;
        }

        async obtenerRetiros(ano, mes) {
            const urls = RolPagosAPI.getApiUrls('caja/retiros').map(url => `${url}?ano=${ano}&mes=${mes}`);
            
            this.data.retiros = [];
            this.data.retiros_total = 0;
            
            for (const url of urls) {
                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    if (data.retiros) {
                        const retirosEmpleado = data.retiros
                            .filter(retiro => 
                                retiro.usuario.toLowerCase() === this.nombre.toLowerCase() &&
                                !retiro.motivo.toLowerCase().includes('deposito') &&
                                !retiro.motivo.toLowerCase().includes('depósito')
                            )
                            .map(retiro => {
                                const sucursal = RolPagosUtils.getSucursalName(url);
                                console.log('Retiro procesado:', { url, sucursal }); // Debug
                                return {
                                    ...retiro,
                                    url,
                                    sucursal
                                };
                            });
                        
                        this.data.retiros.push(...retirosEmpleado);
                        this.data.retiros_total += retirosEmpleado.reduce((sum, retiro) => 
                            sum + Math.abs(parseFloat(retiro.valor)), 0
                        );
                    }
                } catch (error) {
                    console.error('Error al obtener retiros:', error);
                }
            }
        }

        async obtenerPedidos(ano, mes) {
            const urls = RolPagosAPI.getApiUrls('pedidos').map(url => `${url}?ano=${ano}&mes=${mes}`);
            
            this.data.pedidos = [];
            this.data.pedidos_total = 0;
            
            for (const url of urls) {
                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    if (data.success && data.data.pedidos) {
                        const pedidosEmpleado = data.data.pedidos
                            .filter(pedido => pedido.usuario.toLowerCase() === this.nombre.toLowerCase())
                            .map(pedido => {
                                const sucursal = RolPagosUtils.getSucursalName(url);
                                console.log('Pedido procesado:', { url, sucursal }); // Debug
                                return {
                                    ...pedido,
                                    url,
                                    sucursal
                                };
                            });
                        
                        this.data.pedidos.push(...pedidosEmpleado);
                        this.data.pedidos_total += pedidosEmpleado.reduce((sum, pedido) => 
                            sum + parseFloat(pedido.total), 0
                        );
                    }
                } catch (error) {
                    console.error('Error al obtener pedidos:', error);
                }
            }
        }

        async obtenerHistorial(ano, mes) {
            const urls = RolPagosAPI.getApiUrls('caja/historial').map(url => `${url}?ano=${ano}&mes=${mes}`);
            
            this.data.historial = { ingresos: 0, egresos: 0 };
            this.data.movimientos = [];
            
            for (const url of urls) {
                try {
                    const response = await fetch(url);
                    const data = await response.json();
                    if (data.success && data.data.movimientos) {
                        const movimientosEmpleado = data.data.movimientos
                            .filter(mov => mov.usuario.toLowerCase() === this.nombre.toLowerCase())
                            .map(mov => {
                                const sucursal = RolPagosUtils.getSucursalName(url);
                                console.log('Movimiento procesado:', { url, sucursal }); // Debug
                                return {
                                    ...mov,
                                    url,
                                    sucursal
                                };
                            });
                        
                        movimientosEmpleado.forEach(mov => {
                            const monto = Math.abs(parseFloat(mov.monto));
                            if (mov.descripcion === 'Apertura') {
                                this.data.historial.ingresos += monto;
                            } else {
                                this.data.historial.egresos += monto;
                            }
                        });
                        
                        this.data.movimientos.push(...movimientosEmpleado);
                    }
                } catch (error) {
                    console.error('Error al obtener historial:', error);
                }
            }
        }

        actualizarUI() {
            try {
                const mes = document.getElementById('filtroMes').value;
                const ano = document.getElementById('filtroAno').value;
                
                // Filtrar datos por mes y año
                const pedidosFiltrados = this.data.pedidos.filter(pedido => {
                    const fecha = new Date(pedido.fecha);
                    const mesPedido = (fecha.getMonth() + 1).toString().padStart(2, '0');
                    const anoPedido = fecha.getFullYear().toString();
                    return mesPedido === mes && anoPedido === ano;
                });

                const retirosFiltrados = this.data.retiros.filter(retiro => {
                    const fecha = new Date(retiro.fecha);
                    const mesRetiro = (fecha.getMonth() + 1).toString().padStart(2, '0');
                    const anoRetiro = fecha.getFullYear().toString();
                    return mesRetiro === mes && anoRetiro === ano;
                });

                // Calcular totales solo con los datos filtrados
                const totalPedidos = pedidosFiltrados.reduce((sum, pedido) => 
                    sum + parseFloat(pedido.total), 0
                );

                const totalRetiros = retirosFiltrados.reduce((sum, retiro) => 
                    sum + Math.abs(parseFloat(retiro.valor)), 0
                );

                // Actualizar período y total
                document.getElementById(`periodo_${this.userId}`).textContent = `${mes}/${ano}`;
                document.getElementById(`total_${this.userId}`).textContent = RolPagosUtils.formatCurrency(totalPedidos || 0);

                // Agrupar datos por fecha
                const movimientosPorFecha = this.agruparMovimientosPorFecha();
                const pedidosPorFecha = RolPagosUtils.agruparPorFecha(pedidosFiltrados);
                const retirosPorFecha = RolPagosUtils.agruparPorFecha(retirosFiltrados);
                
                // Generar filas
                const todasLasFechas = new Set([
                    ...Object.keys(movimientosPorFecha),
                    ...Object.keys(pedidosPorFecha),
                    ...Object.keys(retirosPorFecha)
                ]);

                const filas = this.generarFilasTabla(Array.from(todasLasFechas), {
                    movimientosPorFecha,
                    pedidosPorFecha,
                    retirosPorFecha
                });

                document.getElementById(`desglose_${this.userId}`).innerHTML = filas.join('');

                return true;
            } catch (error) {
                console.error('Error al actualizar UI:', error);
                return false;
            }
        }

        agruparMovimientosPorFecha() {
            const movimientosPorFecha = {};
            
            this.data.movimientos.forEach(mov => {
                const fecha = mov.fecha.split('T')[0];
                if (!movimientosPorFecha[fecha]) {
                    movimientosPorFecha[fecha] = {
                        matriz: { apertura: null, cierre: null },
                        rocio: { apertura: null, cierre: null },
                        norte: { apertura: null, cierre: null }
                    };
                }
                
                const sucursalKey = mov.sucursal.toLowerCase().replace('í', 'i');
                if (mov.descripcion === 'Apertura') {
                    movimientosPorFecha[fecha][sucursalKey].apertura = mov;
                } else {
                    movimientosPorFecha[fecha][sucursalKey].cierre = mov;
                }
            });
            
            return movimientosPorFecha;
        }

        generarFilasTabla(fechas, datos) {
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

            // Filtrar fechas que no correspondan al mes y año seleccionados
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
                const movimientosDia = datos.movimientosPorFecha[fecha] || {
                    matriz: { apertura: null, cierre: null },
                    rocio: { apertura: null, cierre: null },
                    norte: { apertura: null, cierre: null }
                };
                const pedidosDelDia = datos.pedidosPorFecha[fecha] || [];
                const retirosDelDia = datos.retirosPorFecha[fecha] || [];
                
                const totalPedidosDia = pedidosDelDia.reduce((sum, pedido) => sum + parseFloat(pedido.total), 0);
                const totalRetirosDia = retirosDelDia.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);

                let sucursalDia = this.determinarSucursalDia(movimientosDia, pedidosDelDia, retirosDelDia);

                return this.generarFilaHTML(fecha, {
                    movimientosDia,
                    pedidosDelDia,
                    retirosDelDia,
                    totalPedidosDia,
                    totalRetirosDia,
                    sucursalDia
                });
            });
        }

        determinarSucursalDia(movimientosDia, pedidosDelDia, retirosDelDia) {
            let sucursales = new Set();
            
            // Recolectar todas las sucursales de las diferentes fuentes
            if (movimientosDia.matriz.apertura) sucursales.add('MATRIZ');
            if (movimientosDia.rocio.apertura) sucursales.add('ROCÍO');
            if (movimientosDia.norte.apertura) sucursales.add('NORTE');
            pedidosDelDia.forEach(p => sucursales.add(p.sucursal));
            retirosDelDia.forEach(r => sucursales.add(r.sucursal));
            
            // Convertir el Set a Array y filtrar valores nulos o undefined
            const sucursalesArray = Array.from(sucursales).filter(s => s);
            
            if (sucursalesArray.length === 0) return 'NO ESPECIFICADA';
            if (sucursalesArray.length === 1) return sucursalesArray[0];
            
            // Si hay múltiples sucursales, mostrarlas todas
            return sucursalesArray.join(' / ');
        }

        generarFilaHTML(fecha, datos) {
            const {
                movimientosDia,
                pedidosDelDia,
                retirosDelDia,
                totalPedidosDia,
                totalRetirosDia
            } = datos;

            return `
                <tr>
                    <td>${RolPagosUtils.formatDate(fecha)}</td>
                    <td>
                        ${this.generarHTMLMovimientos(movimientosDia)}
                    </td>
                    <td>
                        ${this.generarHTMLPedidos(pedidosDelDia, totalPedidosDia)}
                    </td>
                    <td>
                        ${this.generarHTMLRetiros(retirosDelDia, totalRetirosDia)}
                    </td>
                </tr>
            `;
        }

        generarHTMLMovimientos(movimientosDia) {
            let html = '';
            
            // Array de sucursales para iterar
            const sucursales = [
                { key: 'matriz', nombre: 'MATRIZ' },
                { key: 'rocio', nombre: 'ROCÍO' },
                { key: 'norte', nombre: 'NORTE' }
            ];

            sucursales.forEach(({ key, nombre }) => {
                const movimientos = movimientosDia[key];
                if (movimientos.apertura || movimientos.cierre) {
                    html += `<div class="sucursal-movimientos mb-2">
                        <div class="badge badge-secondary mb-1">${nombre}</div>`;

                    if (movimientos.apertura) {
                        html += `
                            <div class="badge badge-apertura d-block mb-1">
                                APERTURA: ${RolPagosUtils.formatCurrency(Math.abs(movimientos.apertura.monto))}
                                <span class="hora-movimiento">
                                    ${RolPagosUtils.formatTime(movimientos.apertura.fecha)}
                                    <small class="d-block text-white-50">
                                        <i class="fas fa-user"></i> ${movimientos.apertura.usuario}
                                    </small>
                                </span>
                            </div>
                        `;
                    }
                    
                    if (movimientos.cierre) {
                        html += `
                            <div class="badge badge-cierre d-block">
                                CIERRE: ${RolPagosUtils.formatCurrency(Math.abs(movimientos.cierre.monto))}
                                <span class="hora-movimiento">
                                    ${RolPagosUtils.formatTime(movimientos.cierre.fecha)}
                                    <small class="d-block text-white-50">
                                        <i class="fas fa-user"></i> ${movimientos.cierre.usuario}
                                    </small>
                                </span>
                            </div>
                        `;
                    }

                    html += '</div>';
                }
            });
            
            return html || '<small class="text-muted">Sin movimientos</small>';
        }

        generarHTMLPedidos(pedidos, total) {
            if (pedidos.length === 0) {
                return '<small class="text-muted">Sin pedidos</small>';
            }

            // Agrupar pedidos por sucursal
            const pedidosPorSucursal = pedidos.reduce((acc, pedido) => {
                if (!acc[pedido.sucursal]) {
                    acc[pedido.sucursal] = {
                        pedidos: [],
                        total: 0
                    };
                }
                acc[pedido.sucursal].pedidos.push(pedido);
                acc[pedido.sucursal].total += parseFloat(pedido.total);
                return acc;
            }, {});

            return `
                <div class="operaciones-container">
                    ${Object.entries(pedidosPorSucursal).map(([sucursal, data]) => `
                        <div class="sucursal-operaciones mb-3">
                            <div class="badge badge-secondary mb-2">
                                <i class="fas fa-store-alt mr-1"></i>${sucursal}
                            </div>
                            <div class="pedidos-dia">
                                <strong>Total: ${RolPagosUtils.formatCurrency(data.total)}</strong>
                                <ul class="list-unstyled mb-0">
                                    ${data.pedidos.map(pedido => `
                                        <li>
                                            <small>
                                                ${RolPagosUtils.formatTime(pedido.fecha)} - ${pedido.cliente}
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

        generarHTMLRetiros(retiros, total) {
            if (retiros.length === 0) {
                return '<small class="text-muted">Sin retiros</small>';
            }

            // Agrupar retiros por sucursal
            const retirosPorSucursal = retiros.reduce((acc, retiro) => {
                if (!acc[retiro.sucursal]) {
                    acc[retiro.sucursal] = {
                        retiros: [],
                        total: 0
                    };
                }
                acc[retiro.sucursal].retiros.push(retiro);
                acc[retiro.sucursal].total += Math.abs(parseFloat(retiro.valor));
                return acc;
            }, {});

            return `
                <div class="operaciones-container">
                    ${Object.entries(retirosPorSucursal).map(([sucursal, data]) => `
                        <div class="sucursal-operaciones mb-3">
                            <div class="badge badge-secondary mb-2">
                                <i class="fas fa-store-alt mr-1"></i>${sucursal}
                            </div>
                            <div class="retiros-dia">
                                <strong>Total: ${RolPagosUtils.formatCurrency(data.total)}</strong>
                                <ul class="list-unstyled mb-0">
                                    ${data.retiros.map(retiro => `
                                        <li>
                                            <small>
                                                ${RolPagosUtils.formatTime(retiro.fecha)} - ${retiro.motivo}
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
            const api = new RolPagosAPI(userId, nombre);
            
            await Promise.all([
                api.obtenerRetiros(ano, mes),
                api.obtenerPedidos(ano, mes),
                api.obtenerHistorial(ano, mes)
            ]);

            api.actualizarUI();
        } catch (error) {
            console.error('Error al generar rol:', error);
            alert('Error al generar el rol de pagos');
        }
    }
</script>
@endpush 