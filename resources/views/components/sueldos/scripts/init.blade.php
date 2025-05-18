@push('js')
<script>
    // Variables globales para almacenar los totales y el tipo de sucursal
    let totalRetirosMatriz = 0;
    let totalRetirosRocio = 0;
    let totalRetirosNorte = 0;
    // Variables globales para pedidos
    let totalPedidosMatriz = 0;
    let totalPedidosRocio = 0;
    let totalPedidosNorte = 0;
    // Variables globales para historial de caja
    let totalIngresosMatriz = 0;
    let totalEgresosMatriz = 0;
    let totalIngresosRocio = 0;
    let totalEgresosRocio = 0;
    let totalIngresosNorte = 0;
    let totalEgresosNorte = 0;

    $(document).ready(function() {
        // Inicializar DataTable
        var sueldosTable = $('#sueldosTable').DataTable({
            "order": [[0, "desc"]],
            "paging": false,
            "info": false,
            "dom": 'Bfrt',
            "buttons": [
                'excelHtml5',
                'csvHtml5',
                {
                    "extend": 'print',
                    "text": 'IMPRIMIR',
                    "autoPrint": true,
                    "exportOptions": {
                        "columns": [0, 1, 2]
                    },
                    "customize": function(win) {
                        $(win.document.body).css('font-size', '16pt');
                        $(win.document.body).find('table')
                            .addClass('compact')
                            .css('font-size', 'inherit');
                    }
                },
                {
                    "extend": 'pdfHtml5',
                    "text": 'PDF',
                    "filename": 'Sueldos.pdf',
                    "pageSize": 'LETTER',
                    "exportOptions": {
                        "columns": [0, 1, 2]
                    }
                }
            ],
            "language": {
                "url": "//cdn.datatables.net/plug-ins/1.10.16/i18n/Spanish.json"
            }
        });

        // Función para formatear números como moneda
        window.formatCurrency = function(number) {
            return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'USD' }).format(number);
        }

        // Event listeners
        const filtroAno = document.getElementById('filtroAno');
        const filtroMes = document.getElementById('filtroMes');
        const filtroSucursal = document.getElementById('filtroSucursal');
        const filtroUsuario = document.getElementById('filtroUsuario');

        filtroAno.addEventListener('change', function() {
            updateAllCards(this.value, filtroMes.value);
        });

        filtroMes.addEventListener('change', function() {
            updateAllCards(filtroAno.value, this.value);
        });

        if (window.tipoSucursal === 'todas') {
            filtroSucursal.addEventListener('change', function() {
                updateAllCards(filtroAno.value, filtroMes.value);
            });
        }

        filtroUsuario.addEventListener('change', function() {
            updateAllCards(filtroAno.value, filtroMes.value);
        });

        document.getElementById('actualButton').addEventListener('click', function() {
            const currentDate = new Date();
            const currentYear = currentDate.getFullYear();
            const currentMonth = currentDate.getMonth() + 1;

            filtroAno.value = currentYear;
            filtroMes.value = currentMonth;
            if (window.tipoSucursal === 'todas') {
                filtroSucursal.value = '';
            }
            document.getElementById('filtroUsuario').value = '';

            updateAllCards(currentYear, currentMonth);
        });

        // Carga inicial de datos
        updateAllCards(filtroAno.value, filtroMes.value);
    });
</script>
@endpush 