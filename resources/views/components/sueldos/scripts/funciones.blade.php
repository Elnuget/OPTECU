@push('js')
<script>
    // Variables globales para almacenar datos del rol
    let empleadoSeleccionado = null;
    let datosRol = {
        retiros: 0,
        pedidos: 0,
        historial: {
            ingresos: 0,
            egresos: 0
        }
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

    // Función para obtener retiros del empleado
    async function obtenerRetiros(ano, mes) {
        const urls = [
            `https://opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`,
            `https://escleroptica2.opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`,
            `https://sucursal3.opticas.xyz/api/caja/retiros?ano=${ano}&mes=${mes}`
        ];

        datosRol.retiros = 0;
        
        for (const url of urls) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (data.retiros) {
                    const retirosEmpleado = data.retiros.filter(retiro => 
                        retiro.usuario.toLowerCase() === empleadoSeleccionado.nombre.toLowerCase()
                    );
                    datosRol.retiros += retirosEmpleado.reduce((sum, retiro) => 
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
        const urls = [
            `https://opticas.xyz/api/pedidos?ano=${ano}&mes=${mes}`,
            `https://escleroptica2.opticas.xyz/api/pedidos?ano=${ano}&mes=${mes}`,
            `https://sucursal3.opticas.xyz/api/pedidos?ano=${ano}&mes=${mes}`
        ];

        datosRol.pedidos = 0;
        
        for (const url of urls) {
            try {
                const response = await fetch(url);
                const data = await response.json();
                if (data.success && data.data.pedidos) {
                    const pedidosEmpleado = data.data.pedidos.filter(pedido => 
                        pedido.usuario.toLowerCase() === empleadoSeleccionado.nombre.toLowerCase()
                    );
                    datosRol.pedidos += pedidosEmpleado.reduce((sum, pedido) => 
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
        const urls = [
            `https://opticas.xyz/api/caja/historial?ano=${ano}&mes=${mes}`,
            `https://escleroptica2.opticas.xyz/api/caja/historial?ano=${ano}&mes=${mes}`,
            `https://sucursal3.opticas.xyz/api/caja/historial?ano=${ano}&mes=${mes}`
        ];

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
        const mes = document.getElementById('filtroMes').value;
        const ano = document.getElementById('filtroAno').value;
        
        // Actualizar encabezado
        document.getElementById('rolEmpleadoNombre').textContent = empleadoSeleccionado.nombre;
        document.getElementById('rolPeriodo').textContent = `${mes}/${ano}`;

        // Calcular totales
        const totalIngresos = datosRol.historial.ingresos + datosRol.pedidos;
        const totalEgresos = datosRol.historial.egresos + datosRol.retiros;

        // Actualizar montos
        document.getElementById('rolSueldoBase').textContent = formatCurrency(datosRol.historial.ingresos);
        document.getElementById('rolComisionPedidos').textContent = formatCurrency(datosRol.pedidos);
        document.getElementById('rolOtrosIngresos').textContent = formatCurrency(0);
        document.getElementById('rolRetiros').textContent = formatCurrency(datosRol.retiros);
        document.getElementById('rolOtrosDescuentos').textContent = formatCurrency(datosRol.historial.egresos);
        document.getElementById('rolTotalIngresos').textContent = formatCurrency(totalIngresos);
        document.getElementById('rolTotalEgresos').textContent = formatCurrency(totalEgresos);
        document.getElementById('rolTotalRecibir').textContent = formatCurrency(totalIngresos - totalEgresos);

        // Actualizar desglose de movimientos
        const desgloseBody = document.getElementById('rolDesglose');
        desgloseBody.innerHTML = datosRol.movimientos.map(mov => `
            <tr>
                <td>${new Date(mov.fecha).toLocaleDateString('es-ES')}</td>
                <td>${mov.descripcion === 'Apertura' ? 'INGRESO' : 'EGRESO'}</td>
                <td>${mov.descripcion}</td>
                <td class="text-${mov.descripcion === 'Apertura' ? 'success' : 'danger'}">
                    ${formatCurrency(Math.abs(mov.monto))}
                </td>
            </tr>
        `).join('');
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

        // Si hay un usuario seleccionado al cargar, generar el rol
        if (selectEmpleado.value) {
            generarRolPagos();
        }
    });

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