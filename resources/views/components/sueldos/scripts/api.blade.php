@push('js')
<script>
    class APIEndpoints {
        static BASE_URLS = {
            matriz: 'https://opticas.xyz/api',
            rocio: 'https://escleroptica2.opticas.xyz/api',
            norte: 'https://sucursal3.opticas.xyz/api'
        };

        static ENDPOINTS = {
            retiros: '/caja/retiros',
            pedidos: '/pedidos',
            historial: '/caja/historial'
        };

        static getEndpoints(tipo, params = {}) {
            const sucursal = document.getElementById('filtroSucursal').value;
            const queryString = Object.entries(params)
                .map(([key, value]) => `${key}=${value}`)
                .join('&');
            
            const urls = [];
            
            if (sucursal === '' || sucursal === 'matriz') {
                urls.push(`${this.BASE_URLS.matriz}${this.ENDPOINTS[tipo]}?${queryString}`);
            }
            if (sucursal === '' || sucursal === 'rocio') {
                urls.push(`${this.BASE_URLS.rocio}${this.ENDPOINTS[tipo]}?${queryString}`);
            }
            if (sucursal === '' || sucursal === 'norte') {
                urls.push(`${this.BASE_URLS.norte}${this.ENDPOINTS[tipo]}?${queryString}`);
            }
            
            return urls;
        }
    }

    class APIService {
        static async fetchData(url) {
            try {
                const response = await fetch(url);
                if (!response.ok) {
                    throw new Error(`Error HTTP: ${response.status}`);
                }
                return await response.json();
            } catch (error) {
                console.error(`Error al obtener datos de ${url}:`, error);
                throw error;
            }
        }

        static async fetchMultipleEndpoints(urls) {
            return Promise.all(urls.map(url => this.fetchData(url)));
        }

        static async obtenerRetiros(ano, mes, nombre) {
            const urls = APIEndpoints.getEndpoints('retiros', { ano, mes });
            const results = await this.fetchMultipleEndpoints(urls);
            
            return results.reduce((acc, data) => {
                if (data.retiros) {
                    const retirosEmpleado = data.retiros
                        .filter(retiro => 
                            retiro.usuario.toLowerCase() === nombre.toLowerCase() &&
                            !retiro.motivo.toLowerCase().includes('deposito') &&
                            !retiro.motivo.toLowerCase().includes('depósito')
                        );
                    acc.push(...retirosEmpleado);
                }
                return acc;
            }, []);
        }

        static async obtenerPedidos(ano, mes, nombre) {
            const urls = APIEndpoints.getEndpoints('pedidos', { ano, mes });
            const results = await this.fetchMultipleEndpoints(urls);
            
            return results.reduce((acc, data) => {
                if (data.success && data.data.pedidos) {
                    const pedidosEmpleado = data.data.pedidos
                        .filter(pedido => pedido.usuario.toLowerCase() === nombre.toLowerCase());
                    acc.push(...pedidosEmpleado);
                }
                return acc;
            }, []);
        }

        static async obtenerHistorial(ano, mes, nombre) {
            const urls = APIEndpoints.getEndpoints('historial', { ano, mes });
            const results = await this.fetchMultipleEndpoints(urls);
            
            return results.reduce((acc, data) => {
                if (data.success && data.data.movimientos) {
                    const movimientosEmpleado = data.data.movimientos
                        .filter(mov => mov.usuario.toLowerCase() === nombre.toLowerCase());
                    acc.push(...movimientosEmpleado);
                }
                return acc;
            }, []);
        }
    }

    // Función para imprimir rol de pagos
    function imprimirRolPagos(userId) {
        const contenido = document.getElementById(`rol-usuario-${userId}`);
        const ventanaImpresion = window.open('', '_blank');
        
        ventanaImpresion.document.write(`
            <!DOCTYPE html>
            <html lang="es">
            <head>
                <meta charset="UTF-8">
                <title>Rol de Pagos</title>
                <style>
                    @media print {
                        body {
                            font-family: Arial, sans-serif;
                            font-size: 12pt;
                            line-height: 1.4;
                            margin: 2cm;
                        }
                        
                        .table {
                            width: 100%;
                            border-collapse: collapse;
                            margin-bottom: 1rem;
                            page-break-inside: auto;
                        }
                        
                        .table th,
                        .table td {
                            border: 1px solid #ddd;
                            padding: 8px;
                            text-align: left;
                        }
                        
                        .table th {
                            background-color: #f4f4f4;
                            font-weight: bold;
                        }
                        
                        tr {
                            page-break-inside: avoid;
                            page-break-after: auto;
                        }
                        
                        thead {
                            display: table-header-group;
                        }
                        
                        tfoot {
                            display: table-footer-group;
                        }

                        .text-right { text-align: right; }
                        .text-center { text-align: center; }
                        .text-success { color: #28a745; }
                        .text-danger { color: #dc3545; }
                        
                        .badge {
                            padding: 2px 5px;
                            border-radius: 3px;
                            font-size: 90%;
                        }
                        
                        .badge-apertura { background-color: #e8f5e9; }
                        .badge-cierre { background-color: #ffebee; }
                        
                        .btn-imprimir {
                            display: none;
                        }
                        
                        @page {
                            size: A4;
                            margin: 2cm;
                        }
                    }
                </style>
            </head>
            <body>
                ${contenido.innerHTML}
            </body>
            </html>
        `);
        
        ventanaImpresion.document.close();
        setTimeout(() => {
            ventanaImpresion.print();
            ventanaImpresion.close();
        }, 250);
    }

    window.APIService = APIService;
    window.imprimirRolPagos = imprimirRolPagos;
</script>
@endpush 