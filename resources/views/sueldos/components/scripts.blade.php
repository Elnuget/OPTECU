@include('atajos')
<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<link href="https://cdn.jsdelivr.net/npm/select2-bootstrap4-theme@1.0.0/dist/select2-bootstrap4.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    $(document).ready(function() {
        console.log('JavaScript cargado correctamente');
        
        // Inicializar Select2 para el selector de usuarios
        $('#usuario').select2({
            theme: 'bootstrap4',
            placeholder: "SELECCIONAR USUARIO",
            allowClear: true
        });
        
        // Configurar el modal antes de mostrarse
        $('#confirmarEliminarModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var url = button.data('url');
            var modal = $(this);
            modal.find('#eliminarForm').attr('action', url);
        });

        // Configurar el modal de eliminar detalle antes de mostrarse
        $('#confirmarEliminarDetalleModal').on('show.bs.modal', function(event) {
            var button = $(event.relatedTarget);
            var url = button.data('url');
            var modal = $(this);
            modal.find('#eliminarDetalleForm').attr('action', url);
        });

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
                        "columns": [0, 1, 2, 3, 4]
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
                        "columns": [0, 1, 2, 3, 4]
                    }
                }
            ],
            "language": {
                "url": "{{ asset('js/datatables/Spanish.json') }}"
            }
        });

        // Inicializar DataTable para Detalles de Sueldo
        var detallesSueldoTable = $('#detallesSueldoTable').DataTable({
            "order": [[0, "desc"]],
            "paging": false,
            "info": false,
            "dom": 'Bfrt',
            "buttons": [
                'excelHtml5',
                'csvHtml5',
                {
                    "extend": 'print',
                    "text": 'IMPRIMIR DETALLES',
                    "autoPrint": true,
                    "exportOptions": {
                        "columns": [0, 1, 2, 3, 4]
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
                    "text": 'PDF DETALLES',
                    "filename": 'Detalles_Sueldos.pdf',
                    "pageSize": 'LETTER',
                    "exportOptions": {
                        "columns": [0, 1, 2, 3, 4]
                    }
                }
            ],
            "language": {
                "url": "{{ asset('js/datatables/Spanish.json') }}"
            }
        });

        // Ninguna funcionalidad de filtro es necesaria ya que hemos eliminado los filtros
        
        // Auto-enviar formulario para usuarios no administradores
        @if(Auth::user() && !Auth::user()->is_admin)
            // Si es un usuario no administrador y no hay resultados cargados, enviar automáticamente el formulario
            if (!$('#detallesSueldoTable tbody tr').length && window.location.search.indexOf('usuario=') === -1) {
                console.log('Auto-enviando formulario para usuario no administrador');
                $('#rolDePagoForm').submit();
            }
        @endif
    });
</script>
