@push('js')
<script>
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