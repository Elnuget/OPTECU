@push('js')
<script>
    // Funciones para obtener datos de retiros
    function fetchAndDisplayRetirosMatriz(ano, mes) {
        const apiUrl = `https://opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`;
        const totalSpan = document.getElementById('total-retiros-matriz');
        const desgloseBody = document.getElementById('desglose-retiros-matriz');
        const loadingOverlay = document.getElementById('loading-overlay-retiros-matriz');

        loadingOverlay.style.display = 'flex';
        totalSpan.textContent = 'CARGANDO...';
        desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>';

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                return response.json();
            })
            .then(data => {
                const retirosFiltered = data.retiros ? data.retiros.filter(retiro => {
                    const motivo = retiro.motivo.toLowerCase();
                    return !motivo.includes('deposito') && !motivo.includes('depósito');
                }) : [];
                
                const totalFiltrado = retirosFiltered.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);
                
                totalRetirosMatriz = totalFiltrado;
                totalSpan.textContent = formatCurrency(totalRetirosMatriz);
                updateGlobalRetiros();

                if (data.retiros && data.retiros.length > 0) {
                    desgloseBody.innerHTML = data.retiros.map(retiro => {
                        const esDeposito = retiro.motivo.toLowerCase().includes('deposito') || retiro.motivo.toLowerCase().includes('depósito');
                        return `
                            <tr ${esDeposito ? 'class="bg-light"' : ''}>
                                <td>${retiro.fecha}</td>
                                <td>${retiro.motivo} ${esDeposito ? '<span class="badge badge-info">DEPÓSITO</span>' : ''}</td>
                                <td class="text-danger">${formatCurrency(retiro.valor)}</td>
                                <td>${retiro.usuario}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">NO HAY RETIROS REGISTRADOS</td></tr>';
                }
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                totalSpan.textContent = 'ERROR';
                desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                loadingOverlay.style.display = 'none';
            });
    }

    function fetchAndDisplayRetirosRocio(ano, mes) {
        const apiUrl = `https://escleroptica2.opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`;
        const totalSpan = document.getElementById('total-retiros-rocio');
        const desgloseBody = document.getElementById('desglose-retiros-rocio');
        const loadingOverlay = document.getElementById('loading-overlay-retiros-rocio');

        loadingOverlay.style.display = 'flex';
        totalSpan.textContent = 'CARGANDO...';
        desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>';

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                return response.json();
            })
            .then(data => {
                const retirosFiltered = data.retiros ? data.retiros.filter(retiro => {
                    const motivo = retiro.motivo.toLowerCase();
                    return !motivo.includes('deposito') && !motivo.includes('depósito');
                }) : [];
                
                const totalFiltrado = retirosFiltered.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);
                
                totalRetirosRocio = totalFiltrado;
                totalSpan.textContent = formatCurrency(totalRetirosRocio);
                updateGlobalRetiros();

                if (data.retiros && data.retiros.length > 0) {
                    desgloseBody.innerHTML = data.retiros.map(retiro => {
                        const esDeposito = retiro.motivo.toLowerCase().includes('deposito') || retiro.motivo.toLowerCase().includes('depósito');
                        return `
                            <tr ${esDeposito ? 'class="bg-light"' : ''}>
                                <td>${retiro.fecha}</td>
                                <td>${retiro.motivo} ${esDeposito ? '<span class="badge badge-info">DEPÓSITO</span>' : ''}</td>
                                <td class="text-danger">${formatCurrency(retiro.valor)}</td>
                                <td>${retiro.usuario}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">NO HAY RETIROS REGISTRADOS</td></tr>';
                }
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                totalSpan.textContent = 'ERROR';
                desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                loadingOverlay.style.display = 'none';
            });
    }

    function fetchAndDisplayRetirosNorte(ano, mes) {
        const apiUrl = `https://sucursal3.opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`;
        const totalSpan = document.getElementById('total-retiros-norte');
        const desgloseBody = document.getElementById('desglose-retiros-norte');
        const loadingOverlay = document.getElementById('loading-overlay-retiros-norte');

        loadingOverlay.style.display = 'flex';
        totalSpan.textContent = 'CARGANDO...';
        desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">CARGANDO DATOS...</td></tr>';

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                return response.json();
            })
            .then(data => {
                const retirosFiltered = data.retiros ? data.retiros.filter(retiro => {
                    const motivo = retiro.motivo.toLowerCase();
                    return !motivo.includes('deposito') && !motivo.includes('depósito');
                }) : [];
                
                const totalFiltrado = retirosFiltered.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);
                
                totalRetirosNorte = totalFiltrado;
                totalSpan.textContent = formatCurrency(totalRetirosNorte);
                updateGlobalRetiros();

                if (data.retiros && data.retiros.length > 0) {
                    desgloseBody.innerHTML = data.retiros.map(retiro => {
                        const esDeposito = retiro.motivo.toLowerCase().includes('deposito') || retiro.motivo.toLowerCase().includes('depósito');
                        return `
                            <tr ${esDeposito ? 'class="bg-light"' : ''}>
                                <td>${retiro.fecha}</td>
                                <td>${retiro.motivo} ${esDeposito ? '<span class="badge badge-info">DEPÓSITO</span>' : ''}</td>
                                <td class="text-danger">${formatCurrency(retiro.valor)}</td>
                                <td>${retiro.usuario}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center">NO HAY RETIROS REGISTRADOS</td></tr>';
                }
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                totalSpan.textContent = 'ERROR';
                desgloseBody.innerHTML = '<tr><td colspan="4" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                loadingOverlay.style.display = 'none';
            });
    }

    // Funciones para obtener datos de pedidos
    function fetchAndDisplayPedidosMatriz(ano, mes) {
        const apiUrl = `https://opticas.xyz/api/pedidos?ano=${ano}&mes=${mes}`;
        const totalSpan = document.getElementById('total-pedidos-matriz');
        const desgloseBody = document.getElementById('desglose-pedidos-matriz');
        const loadingOverlay = document.getElementById('loading-overlay-pedidos-matriz');

        loadingOverlay.style.display = 'flex';
        totalSpan.textContent = 'CARGANDO...';
        desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>';

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                return response.json();
            })
            .then(response => {
                if (!response.success) throw new Error('La respuesta no fue exitosa');
                const data = response.data;
                const pedidos = data.pedidos || [];
                const totales = data.totales || { ventas: 0, saldos: 0, cobrado: 0 };
                
                totalPedidosMatriz = parseFloat(totales.ventas) || 0;
                totalSpan.textContent = formatCurrency(totalPedidosMatriz);
                updateGlobalPedidos();

                if (pedidos.length > 0) {
                    desgloseBody.innerHTML = pedidos.map(pedido => {
                        const fecha = new Date(pedido.fecha).toLocaleDateString('es-ES');
                        const saldoPendiente = parseFloat(pedido.saldo) > 0;
                        return `
                            <tr class="${saldoPendiente ? 'table-warning' : ''}">
                                <td>${fecha}</td>
                                <td>${pedido.cliente}</td>
                                <td>${formatCurrency(pedido.total)}</td>
                                <td>
                                    ${saldoPendiente ? 
                                        `<span class="badge badge-warning">SALDO PENDIENTE: ${formatCurrency(pedido.saldo)}</span>` : 
                                        `<span class="badge badge-success">PAGADO</span>`
                                    }
                                </td>
                                <td>${pedido.usuario}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">NO HAY PEDIDOS REGISTRADOS</td></tr>';
                }
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                totalSpan.textContent = 'ERROR';
                desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                loadingOverlay.style.display = 'none';
            });
    }

    function fetchAndDisplayPedidosRocio(ano, mes) {
        const apiUrl = `https://escleroptica2.opticas.xyz/api/pedidos?ano=${ano}&mes=${mes}`;
        const totalSpan = document.getElementById('total-pedidos-rocio');
        const desgloseBody = document.getElementById('desglose-pedidos-rocio');
        const loadingOverlay = document.getElementById('loading-overlay-pedidos-rocio');

        loadingOverlay.style.display = 'flex';
        totalSpan.textContent = 'CARGANDO...';
        desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>';

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                return response.json();
            })
            .then(response => {
                if (!response.success) throw new Error('La respuesta no fue exitosa');
                const data = response.data;
                const pedidos = data.pedidos || [];
                const totales = data.totales || { ventas: 0, saldos: 0, cobrado: 0 };
                
                totalPedidosRocio = parseFloat(totales.ventas) || 0;
                totalSpan.textContent = formatCurrency(totalPedidosRocio);
                updateGlobalPedidos();

                if (pedidos.length > 0) {
                    desgloseBody.innerHTML = pedidos.map(pedido => {
                        const fecha = new Date(pedido.fecha).toLocaleDateString('es-ES');
                        const saldoPendiente = parseFloat(pedido.saldo) > 0;
                        return `
                            <tr class="${saldoPendiente ? 'table-warning' : ''}">
                                <td>${fecha}</td>
                                <td>${pedido.cliente}</td>
                                <td>${formatCurrency(pedido.total)}</td>
                                <td>
                                    ${saldoPendiente ? 
                                        `<span class="badge badge-warning">SALDO PENDIENTE: ${formatCurrency(pedido.saldo)}</span>` : 
                                        `<span class="badge badge-success">PAGADO</span>`
                                    }
                                </td>
                                <td>${pedido.usuario}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">NO HAY PEDIDOS REGISTRADOS</td></tr>';
                }
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                totalSpan.textContent = 'ERROR';
                desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                loadingOverlay.style.display = 'none';
            });
    }

    function fetchAndDisplayPedidosNorte(ano, mes) {
        const apiUrl = `https://sucursal3.opticas.xyz/api/pedidos?ano=${ano}&mes=${mes}`;
        const totalSpan = document.getElementById('total-pedidos-norte');
        const desgloseBody = document.getElementById('desglose-pedidos-norte');
        const loadingOverlay = document.getElementById('loading-overlay-pedidos-norte');

        loadingOverlay.style.display = 'flex';
        totalSpan.textContent = 'CARGANDO...';
        desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>';

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                return response.json();
            })
            .then(response => {
                if (!response.success) throw new Error('La respuesta no fue exitosa');
                const data = response.data;
                const pedidos = data.pedidos || [];
                const totales = data.totales || { ventas: 0, saldos: 0, cobrado: 0 };
                
                totalPedidosNorte = parseFloat(totales.ventas) || 0;
                totalSpan.textContent = formatCurrency(totalPedidosNorte);
                updateGlobalPedidos();

                if (pedidos.length > 0) {
                    desgloseBody.innerHTML = pedidos.map(pedido => {
                        const fecha = new Date(pedido.fecha).toLocaleDateString('es-ES');
                        const saldoPendiente = parseFloat(pedido.saldo) > 0;
                        return `
                            <tr class="${saldoPendiente ? 'table-warning' : ''}">
                                <td>${fecha}</td>
                                <td>${pedido.cliente}</td>
                                <td>${formatCurrency(pedido.total)}</td>
                                <td>
                                    ${saldoPendiente ? 
                                        `<span class="badge badge-warning">SALDO PENDIENTE: ${formatCurrency(pedido.saldo)}</span>` : 
                                        `<span class="badge badge-success">PAGADO</span>`
                                    }
                                </td>
                                <td>${pedido.usuario}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">NO HAY PEDIDOS REGISTRADOS</td></tr>';
                }
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                totalSpan.textContent = 'ERROR';
                desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                loadingOverlay.style.display = 'none';
            });
    }

    // Funciones para obtener datos del historial
    function fetchAndDisplayHistorialMatriz(ano, mes) {
        const apiUrl = `https://opticas.xyz/api/caja/historial?ano=${ano}&mes=${mes}`;
        const totalSpan = document.getElementById('total-historial-matriz');
        const desgloseBody = document.getElementById('desglose-historial-matriz');
        const loadingOverlay = document.getElementById('loading-overlay-historial-matriz');

        loadingOverlay.style.display = 'flex';
        totalSpan.textContent = 'CARGANDO...';
        desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>';

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                return response.json();
            })
            .then(response => {
                if (!response.success) throw new Error('La respuesta no fue exitosa');
                const data = response.data;
                const movimientos = data.movimientos || [];
                const totales = data.totales || { ingresos: 0, egresos: 0 };
                
                totalIngresosMatriz = parseFloat(totales.ingresos) || 0;
                totalEgresosMatriz = parseFloat(totales.egresos) || 0;
                const balance = totalIngresosMatriz - totalEgresosMatriz;
                
                totalSpan.textContent = formatCurrency(balance);
                updateGlobalHistorial();

                if (movimientos.length > 0) {
                    desgloseBody.innerHTML = movimientos.map(movimiento => {
                        const fecha = new Date(movimiento.fecha).toLocaleDateString('es-ES');
                        const esIngreso = movimiento.descripcion === 'Apertura';
                        return `
                            <tr>
                                <td>${fecha}</td>
                                <td>
                                    <span class="badge badge-${esIngreso ? 'success' : 'danger'}">
                                        ${movimiento.descripcion}
                                    </span>
                                </td>
                                <td>${movimiento.descripcion}</td>
                                <td class="text-${esIngreso ? 'success' : 'danger'}">
                                    ${formatCurrency(Math.abs(movimiento.monto))}
                                </td>
                                <td>${movimiento.usuario}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">NO HAY MOVIMIENTOS REGISTRADOS</td></tr>';
                }
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                totalSpan.textContent = 'ERROR';
                desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                loadingOverlay.style.display = 'none';
            });
    }

    function fetchAndDisplayHistorialRocio(ano, mes) {
        const apiUrl = `https://escleroptica2.opticas.xyz/api/caja/historial?ano=${ano}&mes=${mes}`;
        const totalSpan = document.getElementById('total-historial-rocio');
        const desgloseBody = document.getElementById('desglose-historial-rocio');
        const loadingOverlay = document.getElementById('loading-overlay-historial-rocio');

        loadingOverlay.style.display = 'flex';
        totalSpan.textContent = 'CARGANDO...';
        desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>';

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                return response.json();
            })
            .then(response => {
                if (!response.success) throw new Error('La respuesta no fue exitosa');
                const data = response.data;
                const movimientos = data.movimientos || [];
                const totales = data.totales || { ingresos: 0, egresos: 0 };
                
                totalIngresosRocio = parseFloat(totales.ingresos) || 0;
                totalEgresosRocio = parseFloat(totales.egresos) || 0;
                const balance = totalIngresosRocio - totalEgresosRocio;
                
                totalSpan.textContent = formatCurrency(balance);
                updateGlobalHistorial();

                if (movimientos.length > 0) {
                    desgloseBody.innerHTML = movimientos.map(movimiento => {
                        const fecha = new Date(movimiento.fecha).toLocaleDateString('es-ES');
                        const esIngreso = movimiento.descripcion === 'Apertura';
                        return `
                            <tr>
                                <td>${fecha}</td>
                                <td>
                                    <span class="badge badge-${esIngreso ? 'success' : 'danger'}">
                                        ${movimiento.descripcion}
                                    </span>
                                </td>
                                <td>${movimiento.descripcion}</td>
                                <td class="text-${esIngreso ? 'success' : 'danger'}">
                                    ${formatCurrency(Math.abs(movimiento.monto))}
                                </td>
                                <td>${movimiento.usuario}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">NO HAY MOVIMIENTOS REGISTRADOS</td></tr>';
                }
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                totalSpan.textContent = 'ERROR';
                desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                loadingOverlay.style.display = 'none';
            });
    }

    function fetchAndDisplayHistorialNorte(ano, mes) {
        const apiUrl = `https://sucursal3.opticas.xyz/api/caja/historial?ano=${ano}&mes=${mes}`;
        const totalSpan = document.getElementById('total-historial-norte');
        const desgloseBody = document.getElementById('desglose-historial-norte');
        const loadingOverlay = document.getElementById('loading-overlay-historial-norte');

        loadingOverlay.style.display = 'flex';
        totalSpan.textContent = 'CARGANDO...';
        desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">CARGANDO DATOS...</td></tr>';

        fetch(apiUrl)
            .then(response => {
                if (!response.ok) throw new Error('Error en la red o respuesta no válida');
                return response.json();
            })
            .then(response => {
                if (!response.success) throw new Error('La respuesta no fue exitosa');
                const data = response.data;
                const movimientos = data.movimientos || [];
                const totales = data.totales || { ingresos: 0, egresos: 0 };
                
                totalIngresosNorte = parseFloat(totales.ingresos) || 0;
                totalEgresosNorte = parseFloat(totales.egresos) || 0;
                const balance = totalIngresosNorte - totalEgresosNorte;
                
                totalSpan.textContent = formatCurrency(balance);
                updateGlobalHistorial();

                if (movimientos.length > 0) {
                    desgloseBody.innerHTML = movimientos.map(movimiento => {
                        const fecha = new Date(movimiento.fecha).toLocaleDateString('es-ES');
                        const esIngreso = movimiento.descripcion === 'Apertura';
                        return `
                            <tr>
                                <td>${fecha}</td>
                                <td>
                                    <span class="badge badge-${esIngreso ? 'success' : 'danger'}">
                                        ${movimiento.descripcion}
                                    </span>
                                </td>
                                <td>${movimiento.descripcion}</td>
                                <td class="text-${esIngreso ? 'success' : 'danger'}">
                                    ${formatCurrency(Math.abs(movimiento.monto))}
                                </td>
                                <td>${movimiento.usuario}</td>
                            </tr>
                        `;
                    }).join('');
                } else {
                    desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center">NO HAY MOVIMIENTOS REGISTRADOS</td></tr>';
                }
                loadingOverlay.style.display = 'none';
            })
            .catch(error => {
                console.error('Error:', error);
                totalSpan.textContent = 'ERROR';
                desgloseBody.innerHTML = '<tr><td colspan="5" class="text-center text-danger">ERROR AL CARGAR LOS DATOS</td></tr>';
                loadingOverlay.style.display = 'none';
            });
    }
</script>
@endpush 