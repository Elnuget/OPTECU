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
                
                // Actualizar período y total
                document.getElementById(`periodo_${this.userId}`).textContent = `${mes}/${ano}`;
                document.getElementById(`total_${this.userId}`).textContent = RolPagosUtils.formatCurrency(this.data.pedidos_total || 0);

                // Agrupar datos por fecha
                const movimientosPorFecha = this.agruparMovimientosPorFecha();
                const pedidosPorFecha = RolPagosUtils.agruparPorFecha(this.data.pedidos);
                const retirosPorFecha = RolPagosUtils.agruparPorFecha(this.data.retiros);
                
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
                    movimientosPorFecha[fecha] = { apertura: null, cierre: null };
                }
                
                if (mov.descripcion === 'Apertura') {
                    movimientosPorFecha[fecha].apertura = mov;
                } else {
                    movimientosPorFecha[fecha].cierre = mov;
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

            return fechas.sort().map(fecha => {
                const movimientosDia = datos.movimientosPorFecha[fecha] || { apertura: null, cierre: null };
                const pedidosDelDia = datos.pedidosPorFecha[fecha] || [];
                const retirosDelDia = datos.retirosPorFecha[fecha] || [];
                
                const totalPedidosDia = pedidosDelDia.reduce((sum, pedido) => sum + parseFloat(pedido.total), 0);
                const totalRetirosDia = retirosDelDia.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);

                // Determinar la sucursal del día
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
            if (movimientosDia.apertura) sucursales.add(movimientosDia.apertura.sucursal);
            if (movimientosDia.cierre) sucursales.add(movimientosDia.cierre.sucursal);
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
                totalRetirosDia,
                sucursalDia
            } = datos;

            return `
                <tr>
                    <td>${RolPagosUtils.formatDate(fecha)}</td>
                    <td>
                        ${this.generarHTMLMovimientos(movimientosDia)}
                    </td>
                    <td class="text-center">
                        ${sucursalDia ? 
                            sucursalDia.split(' / ').map(suc => 
                                `<div class="badge badge-info mb-1" style="font-size: 0.9em; display: block;" data-sucursal="${suc}">
                                    <i class="fas fa-store-alt mr-1"></i>${suc}
                                </div>`
                            ).join('') : 
                            '<small class="text-muted"><i class="fas fa-question-circle mr-1"></i>NO ESPECIFICADA</small>'
                        }
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
            
            if (movimientosDia.apertura) {
                html += `
                    <span class="badge badge-apertura">
                        APERTURA: ${RolPagosUtils.formatCurrency(Math.abs(movimientosDia.apertura.monto))}
                        <span class="hora-movimiento">${RolPagosUtils.formatTime(movimientosDia.apertura.fecha)}</span>
                    </span>
                `;
            }
            
            if (movimientosDia.cierre) {
                html += `
                    <br><span class="badge badge-cierre">
                        CIERRE: ${RolPagosUtils.formatCurrency(Math.abs(movimientosDia.cierre.monto))}
                        <span class="hora-movimiento">${RolPagosUtils.formatTime(movimientosDia.cierre.fecha)}</span>
                    </span>
                `;
            }
            
            return html;
        }

        generarHTMLPedidos(pedidos, total) {
            if (pedidos.length === 0) {
                return '<small class="text-muted">Sin pedidos</small>';
            }

            return `
                <div class="pedidos-dia">
                    <strong>Total: ${RolPagosUtils.formatCurrency(total)}</strong>
                    <ul class="list-unstyled mb-0">
                        ${pedidos.map(pedido => `
                            <li>
                                <small>
                                    ${RolPagosUtils.formatTime(pedido.fecha)} - ${pedido.cliente}
                                    <span class="text-success">${RolPagosUtils.formatCurrency(pedido.total)}</span>
                                </small>
                            </li>
                        `).join('')}
                    </ul>
                </div>
            `;
        }

        generarHTMLRetiros(retiros, total) {
            if (retiros.length === 0) {
                return '<small class="text-muted">Sin retiros</small>';
            }

            return `
                <div class="retiros-dia">
                    <strong>Total: ${RolPagosUtils.formatCurrency(total)}</strong>
                    <ul class="list-unstyled mb-0">
                        ${retiros.map(retiro => `
                            <li>
                                <small>
                                    ${RolPagosUtils.formatTime(retiro.fecha)} - ${retiro.motivo}
                                    <span class="text-danger">${RolPagosUtils.formatCurrency(Math.abs(retiro.valor))}</span>
                                </small>
                            </li>
                        `).join('')}
                    </ul>
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