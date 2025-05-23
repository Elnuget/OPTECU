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

    class RolPagosManager {
        constructor() {
            this.tipoSucursal = window.tipoSucursal;
            this.initializeEventListeners();
            this.loadInitialData();
        }

        initializeEventListeners() {
            const filtros = ['#filtroAno', '#filtroMes'];
            if (this.tipoSucursal === 'todas') {
                filtros.push('#filtroSucursal');
            }

            // Manejar cambios en los filtros
            $(filtros.join(', ')).on('change', () => this.updateAllData());

            // Manejar el botón "Actual"
            $('#actualButton').on('click', () => this.setCurrentPeriod());

            // Manejar botones de impresión
            $('.btn-imprimir').on('click', (e) => {
                const userId = $(e.currentTarget).data('user');
                this.imprimirRolPagos(userId);
            });
        }

        setCurrentPeriod() {
            const now = new Date();
            $('#filtroAno').val(now.getFullYear());
            $('#filtroMes').val(now.getMonth() + 1);
            
            if (this.tipoSucursal === 'todas') {
                $('#filtroSucursal').val('');
            }

            this.updateAllData();
        }

        updateAllData() {
            const ano = $('#filtroAno').val();
            const mes = $('#filtroMes').val();

            if (!ano || !mes) {
                alert('Por favor seleccione año y mes');
                return;
            }

            $('.rol-usuario').each((_, element) => {
                const userId = $(element).attr('id').replace('rol-usuario-', '');
                const nombre = $(element).find('.text-primary').first().text();
                this.cargarDatosUsuario(userId, nombre);
            });
        }

        cargarDatosUsuario(userId, nombre) {
            const ano = $('#filtroAno').val();
            const mes = $('#filtroMes').val();
            const sucursal = $('#filtroSucursal').val();
            
            obtenerRolPagos(userId, nombre, ano, mes, sucursal);
        }

        loadInitialData() {
            this.updateAllData();
        }
    }

    // Inicializar cuando el documento esté listo
    $(document).ready(() => {
        window.rolPagosManager = new RolPagosManager();
    });
</script>
@endpush 