@push('js')
<script>
    // Variables globales para almacenar datos del rol
    let empleadoSeleccionado = null;
    let datosRol = {
        retiros: 0,
        pedidos: [],
        pedidos_total: 0,
        historial: {
            ingresos: 0,
            egresos: 0
        },
        movimientos: []
    };

    // Función para habilitar/deshabilitar el botón de generar rol
    function actualizarBotonGenerarRol() {
        const selectEmpleado = document.getElementById('filtroUsuario');
        const btnGenerarRol = document.getElementById('btnGenerarRol');
        btnGenerarRol.disabled = !selectEmpleado.value;
        empleadoSeleccionado = selectEmpleado.value ? {
            id: selectEmpleado.value,
            nombre: selectEmpleado.options[selectEmpleado.selectedIndex].text
        } : null;
    }

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

    // Función para generar el rol de pagos
    async function generarRolPagos() {
        const selectEmpleado = document.getElementById('filtroUsuario');
        if (!selectEmpleado.value) {
            alert('Por favor seleccione un usuario');
            return;
        }

        empleadoSeleccionado = {
            id: selectEmpleado.value,
            nombre: selectEmpleado.options[selectEmpleado.selectedIndex].text
        };

        const ano = document.getElementById('filtroAno').value;
        const mes = document.getElementById('filtroMes').value;

        if (!ano || !mes) {
            alert('Por favor seleccione año y mes');
            return;
        }

        // Mostrar el contenedor del rol y ocultar el mensaje
        document.getElementById('contenedorRolPagos').classList.remove('d-none');
        document.getElementById('mensajeSeleccionUsuario').classList.add('d-none');

        // Obtener datos de las APIs
        try {
            await Promise.all([
                obtenerRetiros(ano, mes),
                obtenerPedidos(ano, mes),
                obtenerHistorial(ano, mes)
            ]);

            // Actualizar el rol con los datos
            actualizarRolPagos();

        } catch (error) {
            console.error('Error al generar rol:', error);
            alert('Error al generar el rol de pagos');
        }
    }

    // Función para agrupar movimientos por fecha
    function agruparMovimientosPorFecha(movimientos) {
        const movimientosPorFecha = {};
        
        movimientos.forEach(mov => {
            const fecha = mov.fecha.split('T')[0]; // Obtener solo la fecha
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
            const fecha = pedido.fecha.split('T')[0]; // Obtener solo la fecha
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
            const fecha = retiro.fecha.split('T')[0]; // Obtener solo la fecha
            if (!retirosPorFecha[fecha]) {
                retirosPorFecha[fecha] = [];
            }
            retirosPorFecha[fecha].push(retiro);
        });
        return retirosPorFecha;
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
    async function obtenerRetiros(ano, mes) {
        const urls = getApiUrls('caja/retiros').map(url => `${url}?ano=${ano}&mes=${mes}`);
        datosRol.retiros = [];
        datosRol.retiros_total = 0;
        
        for (const url of urls) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (data.retiros) {
                    const retirosEmpleado = data.retiros.filter(retiro => 
                        retiro.usuario.toLowerCase() === empleadoSeleccionado.nombre.toLowerCase() &&
                        !retiro.motivo.toLowerCase().includes('deposito') &&
                        !retiro.motivo.toLowerCase().includes('depósito')
                    );
                    datosRol.retiros = [...datosRol.retiros, ...retirosEmpleado];
                    datosRol.retiros_total += retirosEmpleado.reduce((sum, retiro) => 
                        sum + Math.abs(parseFloat(retiro.valor)), 0
                    );
                }
            } catch (error) {
                console.error('Error al obtener retiros:', error);
            }
        }
    }

    // Función para obtener pedidos del empleado
    async function obtenerPedidos(ano, mes) {
        const urls = getApiUrls('pedidos').map(url => `${url}?ano=${ano}&mes=${mes}`);
        datosRol.pedidos = [];
        datosRol.pedidos_total = 0;
        
        for (const url of urls) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (data.success && data.data.pedidos) {
                    const pedidosEmpleado = data.data.pedidos.filter(pedido => 
                        pedido.usuario.toLowerCase() === empleadoSeleccionado.nombre.toLowerCase()
                    );
                    datosRol.pedidos = [...datosRol.pedidos, ...pedidosEmpleado];
                    datosRol.pedidos_total += pedidosEmpleado.reduce((sum, pedido) => 
                        sum + parseFloat(pedido.total), 0
                    );
                }
            } catch (error) {
                console.error('Error al obtener pedidos:', error);
            }
        }
    }

    // Función para obtener historial del empleado
    async function obtenerHistorial(ano, mes) {
        const urls = getApiUrls('caja/historial').map(url => `${url}?ano=${ano}&mes=${mes}`);
        datosRol.historial = { ingresos: 0, egresos: 0 };
        datosRol.movimientos = [];
        
        for (const url of urls) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (data.success && data.data.movimientos) {
                    const movimientosEmpleado = data.data.movimientos.filter(mov => 
                        mov.usuario.toLowerCase() === empleadoSeleccionado.nombre.toLowerCase()
                    );
                    
                    movimientosEmpleado.forEach(mov => {
                        // Agregar la URL a cada movimiento para identificar la sucursal
                        mov.url = url;
                        const monto = Math.abs(parseFloat(mov.monto));
                        if (mov.descripcion === 'Apertura') {
                            datosRol.historial.ingresos += monto;
                        } else {
                            datosRol.historial.egresos += monto;
                        }
                        datosRol.movimientos.push(mov);
                    });
                }
            } catch (error) {
                console.error('Error al obtener historial:', error);
            }
        }
    }

    // Función para actualizar el rol de pagos
    function actualizarRolPagos() {
        try {
            const mes = document.getElementById('filtroMes').value;
            const ano = document.getElementById('filtroAno').value;
            
            // Verificar y actualizar elementos del DOM de forma segura
            const elementos = {
                nombre: document.getElementById('rolEmpleadoNombre'),
                periodo: document.getElementById('rolPeriodo'),
                desglose: document.getElementById('rolDesglose'),
                totalRecibir: document.getElementById('rolTotalRecibir'),
                contenedor: document.getElementById('contenedorRolPagos'),
                mensaje: document.getElementById('mensajeSeleccionUsuario')
            };

            // Verificar que todos los elementos existan
            for (const [key, element] of Object.entries(elementos)) {
                if (!element) {
                    throw new Error(`No se encontró el elemento: ${key}`);
                }
            }

            // Mostrar el contenedor y ocultar el mensaje
            elementos.contenedor.classList.remove('d-none');
            elementos.mensaje.classList.add('d-none');

            // Actualizar encabezado
            elementos.nombre.textContent = empleadoSeleccionado.nombre;
            elementos.periodo.textContent = `${mes}/${ano}`;

            // Agrupar movimientos, pedidos y retiros por fecha
            const movimientosPorFecha = agruparMovimientosPorFecha(datosRol.movimientos);
            const pedidosPorFecha = agruparPedidosPorFecha(datosRol.pedidos || []);
            const retirosPorFecha = agruparRetirosPorFecha(datosRol.retiros || []);
            
            // Variable para el total global de pedidos
            let totalGlobalPedidos = 0;

            // Actualizar desglose de movimientos
            const filas = Object.entries(movimientosPorFecha).map(([fecha, movs]) => {
                // Determinar la sucursal
                let sucursal = '';
                if (movs.apertura) {
                    sucursal = getSucursalName(movs.apertura.url || '');
                } else if (movs.cierre) {
                    sucursal = getSucursalName(movs.cierre.url || '');
                }

                // Obtener pedidos del día
                const pedidosDelDia = pedidosPorFecha[fecha] || [];
                const totalPedidosDia = pedidosDelDia.reduce((sum, pedido) => sum + parseFloat(pedido.total), 0);
                
                // Obtener retiros del día
                const retirosDelDia = retirosPorFecha[fecha] || [];
                const totalRetirosDia = retirosDelDia.reduce((sum, retiro) => sum + Math.abs(parseFloat(retiro.valor)), 0);

                // Sumar al total global
                totalGlobalPedidos += totalPedidosDia;

                return `
                    <tr>
                        <td>${formatDate(fecha)}</td>
                        <td>
                            ${movs.apertura ? 
                                `<span class="badge badge-apertura">APERTURA</span>
                                 <span class="hora-movimiento">${formatTime(movs.apertura.fecha)}</span>` : 
                                ''}
                            ${movs.apertura && movs.cierre ? '<br>' : ''}
                            ${movs.cierre ? 
                                `<span class="badge badge-cierre">CIERRE</span>
                                 <span class="hora-movimiento">${formatTime(movs.cierre.fecha)}</span>` : 
                                ''}
                        </td>
                        <td>
                            <span class="sucursal-badge ${getSucursalClass(sucursal)}">${sucursal}</span>
                        </td>
                        <td>
                            ${pedidosDelDia.length > 0 ? 
                                `<div class="pedidos-dia">
                                    <strong>Total: ${formatCurrency(totalPedidosDia)}</strong>
                                    <ul class="list-unstyled mb-0">
                                        ${pedidosDelDia.map(pedido => `
                                            <li>
                                                <small>
                                                    ${formatTime(pedido.fecha)} - ${pedido.cliente} 
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
                `;
            });

            elementos.desglose.innerHTML = filas.join('');
            elementos.totalRecibir.textContent = formatCurrency(totalGlobalPedidos);

        } catch (error) {
            console.error('Error al actualizar el rol:', error);
            alert('Error al actualizar el rol de pagos: ' + error.message);
        }
    }

    // Event listeners
    document.addEventListener('DOMContentLoaded', function() {
        // Listener para el select de empleado
        const selectEmpleado = document.getElementById('filtroUsuario');
        selectEmpleado.addEventListener('change', actualizarBotonGenerarRol);

        // Listener para el botón de generar rol
        const btnGenerarRol = document.getElementById('btnGenerarRol');
        btnGenerarRol.addEventListener('click', generarRolPagos);

        // Listener para el botón de imprimir
        const btnImprimirRol = document.getElementById('btnImprimirRol');
        btnImprimirRol.addEventListener('click', function() {
            window.print();
        });

        // Listener para el cambio de sucursal
        const selectSucursal = document.getElementById('filtroSucursal');
        selectSucursal.addEventListener('change', function() {
            if (empleadoSeleccionado) {
                generarRolPagos();
            }
        });

        // Si hay un usuario seleccionado al cargar, generar el rol
        if (selectEmpleado.value) {
            generarRolPagos();
        }
    });

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
                return 'bg-primary';
            case 'ROCÍO':
                return 'bg-success';
            case 'NORTE':
                return 'bg-warning';
            default:
                return 'bg-secondary';
        }
    }

    // Función para formatear la hora
    function formatTime(dateString) {
        const date = new Date(dateString);
        return date.toLocaleTimeString('es-ES', {
            hour: '2-digit',
            minute: '2-digit'
        });
    }

    // Función para actualizar el total global de retiros y la barra de progreso
    function updateGlobalRetiros() {
        const sucursal = window.tipoSucursal !== 'todas' ? window.tipoSucursal : document.getElementById('filtroSucursal').value;
        let totalGlobal = 0;

        if (window.tipoSucursal !== 'todas') {
            if (window.tipoSucursal === 'matriz') totalGlobal = Math.abs(totalRetirosMatriz);
            else if (window.tipoSucursal === 'rocio') totalGlobal = Math.abs(totalRetirosRocio);
            else if (window.tipoSucursal === 'norte') totalGlobal = Math.abs(totalRetirosNorte);
        } else {
            if (sucursal === '') {
                totalGlobal = Math.abs(totalRetirosMatriz) + Math.abs(totalRetirosRocio) + Math.abs(totalRetirosNorte);
            } else if (sucursal === 'matriz') {
                totalGlobal = Math.abs(totalRetirosMatriz);
            } else if (sucursal === 'rocio') {
                totalGlobal = Math.abs(totalRetirosRocio);
            } else if (sucursal === 'norte') {
                totalGlobal = Math.abs(totalRetirosNorte);
            }
        }

        const totalSpan = document.getElementById('total-retiros-global');
        totalSpan.textContent = formatCurrency(-totalGlobal);

        if (totalGlobal > 0) {
            const porcentajeMatriz = ((sucursal === '' || sucursal === 'matriz' ? Math.abs(totalRetirosMatriz) : 0) / totalGlobal) * 100;
            const porcentajeRocio = ((sucursal === '' || sucursal === 'rocio' ? Math.abs(totalRetirosRocio) : 0) / totalGlobal) * 100;
            const porcentajeNorte = ((sucursal === '' || sucursal === 'norte' ? Math.abs(totalRetirosNorte) : 0) / totalGlobal) * 100;

            const progressMatriz = document.getElementById('progress-retiros-matriz');
            const progressRocio = document.getElementById('progress-retiros-rocio');
            const progressNorte = document.getElementById('progress-retiros-norte');

            progressMatriz.style.width = porcentajeMatriz + '%';
            progressRocio.style.width = porcentajeRocio + '%';
            progressNorte.style.width = porcentajeNorte + '%';

            progressMatriz.textContent = `Matriz: ${formatCurrency(sucursal === '' || sucursal === 'matriz' ? totalRetirosMatriz : 0)}`;
            progressRocio.textContent = `Rocío: ${formatCurrency(sucursal === '' || sucursal === 'rocio' ? totalRetirosRocio : 0)}`;
            progressNorte.textContent = `Norte: ${formatCurrency(sucursal === '' || sucursal === 'norte' ? totalRetirosNorte : 0)}`;
        }
    }

    // Función para actualizar el total global de pedidos y la barra de progreso
    function updateGlobalPedidos() {
        const sucursal = window.tipoSucursal !== 'todas' ? window.tipoSucursal : document.getElementById('filtroSucursal').value;
        let totalGlobal = 0;

        if (window.tipoSucursal !== 'todas') {
            if (window.tipoSucursal === 'matriz') totalGlobal = totalPedidosMatriz;
            else if (window.tipoSucursal === 'rocio') totalGlobal = totalPedidosRocio;
            else if (window.tipoSucursal === 'norte') totalGlobal = totalPedidosNorte;
        } else {
            if (sucursal === '') {
                totalGlobal = totalPedidosMatriz + totalPedidosRocio + totalPedidosNorte;
            } else if (sucursal === 'matriz') {
                totalGlobal = totalPedidosMatriz;
            } else if (sucursal === 'rocio') {
                totalGlobal = totalPedidosRocio;
            } else if (sucursal === 'norte') {
                totalGlobal = totalPedidosNorte;
            }
        }

        const totalSpan = document.getElementById('total-pedidos-global');
        totalSpan.textContent = formatCurrency(totalGlobal);

        if (totalGlobal > 0) {
            const porcentajeMatriz = ((sucursal === '' || sucursal === 'matriz' ? totalPedidosMatriz : 0) / totalGlobal) * 100;
            const porcentajeRocio = ((sucursal === '' || sucursal === 'rocio' ? totalPedidosRocio : 0) / totalGlobal) * 100;
            const porcentajeNorte = ((sucursal === '' || sucursal === 'norte' ? totalPedidosNorte : 0) / totalGlobal) * 100;

            const progressMatriz = document.getElementById('progress-pedidos-matriz');
            const progressRocio = document.getElementById('progress-pedidos-rocio');
            const progressNorte = document.getElementById('progress-pedidos-norte');

            progressMatriz.style.width = porcentajeMatriz + '%';
            progressRocio.style.width = porcentajeRocio + '%';
            progressNorte.style.width = porcentajeNorte + '%';

            progressMatriz.textContent = `Matriz: ${formatCurrency(sucursal === '' || sucursal === 'matriz' ? totalPedidosMatriz : 0)}`;
            progressRocio.textContent = `Rocío: ${formatCurrency(sucursal === '' || sucursal === 'rocio' ? totalPedidosRocio : 0)}`;
            progressNorte.textContent = `Norte: ${formatCurrency(sucursal === '' || sucursal === 'norte' ? totalPedidosNorte : 0)}`;
        }
    }

    // Función para actualizar el total global del historial
    function updateGlobalHistorial() {
        const sucursal = window.tipoSucursal !== 'todas' ? window.tipoSucursal : document.getElementById('filtroSucursal').value;
        let totalIngresos = 0;
        let totalEgresos = 0;

        if (window.tipoSucursal !== 'todas') {
            if (window.tipoSucursal === 'matriz') {
                totalIngresos = totalIngresosMatriz;
                totalEgresos = totalEgresosMatriz;
            } else if (window.tipoSucursal === 'rocio') {
                totalIngresos = totalIngresosRocio;
                totalEgresos = totalEgresosRocio;
            } else if (window.tipoSucursal === 'norte') {
                totalIngresos = totalIngresosNorte;
                totalEgresos = totalEgresosNorte;
            }
        } else {
            if (sucursal === '') {
                totalIngresos = totalIngresosMatriz + totalIngresosRocio + totalIngresosNorte;
                totalEgresos = totalEgresosMatriz + totalEgresosRocio + totalEgresosNorte;
            } else if (sucursal === 'matriz') {
                totalIngresos = totalIngresosMatriz;
                totalEgresos = totalEgresosMatriz;
            } else if (sucursal === 'rocio') {
                totalIngresos = totalIngresosRocio;
                totalEgresos = totalEgresosRocio;
            } else if (sucursal === 'norte') {
                totalIngresos = totalIngresosNorte;
                totalEgresos = totalEgresosNorte;
            }
        }

        const balance = totalIngresos - totalEgresos;

        document.getElementById('total-ingresos-global').textContent = formatCurrency(totalIngresos);
        document.getElementById('total-egresos-global').textContent = formatCurrency(totalEgresos);
        document.getElementById('total-balance-global').textContent = formatCurrency(balance);
    }

    // Función para mostrar/ocultar tarjetas según la sucursal seleccionada
    function toggleSucursalCards(sucursal) {
        const allCards = {
            'matriz': ['card-retiros-matriz', 'card-pedidos-matriz', 'card-historial-matriz'],
            'rocio': ['card-retiros-rocio', 'card-pedidos-rocio', 'card-historial-rocio'],
            'norte': ['card-retiros-norte', 'card-pedidos-norte', 'card-historial-norte']
        };

        if (window.tipoSucursal !== 'todas') {
            Object.entries(allCards).forEach(([currentSucursal, cards]) => {
                cards.forEach(cardId => {
                    const card = document.getElementById(cardId);
                    if (card) {
                        card.style.display = currentSucursal === window.tipoSucursal ? 'block' : 'none';
                    }
                });
            });
            document.getElementById('card-retiros-total').style.display = 'none';
            document.getElementById('card-pedidos-total').style.display = 'none';
            document.getElementById('card-historial-total').style.display = 'none';
        } else {
            if (sucursal === '') {
                Object.values(allCards).flat().forEach(cardId => {
                    document.getElementById(cardId).style.display = 'block';
                });
                document.getElementById('card-retiros-total').style.display = 'block';
                document.getElementById('card-pedidos-total').style.display = 'block';
                document.getElementById('card-historial-total').style.display = 'block';
            } else {
                Object.entries(allCards).forEach(([currentSucursal, cards]) => {
                    cards.forEach(cardId => {
                        document.getElementById(cardId).style.display = currentSucursal === sucursal ? 'block' : 'none';
                    });
                });
                document.getElementById('card-retiros-total').style.display = 'none';
                document.getElementById('card-pedidos-total').style.display = 'none';
                document.getElementById('card-historial-total').style.display = 'none';
            }
        }
    }

    // Función para actualizar todas las tarjetas
    function updateAllCards(ano, mes) {
        const sucursal = window.tipoSucursal !== 'todas' ? window.tipoSucursal : document.getElementById('filtroSucursal').value;
        
        if (sucursal === '' || sucursal === 'matriz' || window.tipoSucursal === 'todas') {
            fetchAndDisplayRetirosMatriz(ano, mes);
            fetchAndDisplayPedidosMatriz(ano, mes);
            fetchAndDisplayHistorialMatriz(ano, mes);
        }
        if (sucursal === '' || sucursal === 'rocio' || window.tipoSucursal === 'todas') {
            fetchAndDisplayRetirosRocio(ano, mes);
            fetchAndDisplayPedidosRocio(ano, mes);
            fetchAndDisplayHistorialRocio(ano, mes);
        }
        if (sucursal === '' || sucursal === 'norte' || window.tipoSucursal === 'todas') {
            fetchAndDisplayRetirosNorte(ano, mes);
            fetchAndDisplayPedidosNorte(ano, mes);
            fetchAndDisplayHistorialNorte(ano, mes);
        }

        toggleSucursalCards(sucursal);
    }

    // Manejadores de modales
    $('#verSueldoModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const fecha = button.data('fecha');
        const descripcion = button.data('descripcion');
        const valor = button.data('valor');
        
        const modal = $(this);
        modal.find('#verFecha').text(fecha);
        modal.find('#verDescripcion').text(descripcion);
        modal.find('#verValor').text(formatCurrency(valor));
    });

    $('#editarSueldoModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const id = button.data('id');
        const fecha = button.data('fecha');
        const descripcion = button.data('descripcion');
        const valor = button.data('valor');
        
        const modal = $(this);
        const form = modal.find('#formEditarSueldo');
        form.attr('action', `/sueldos/${id}`);
        modal.find('#editFecha').val(fecha);
        modal.find('#editDescripcion').val(descripcion);
        modal.find('#editValor').val(valor);
    });

    $('#confirmarEliminarModal').on('show.bs.modal', function (event) {
        const button = $(event.relatedTarget);
        const url = button.data('url');
        
        const form = $(this).find('#eliminarForm');
        form.attr('action', url);
    });
</script>
@endpush 