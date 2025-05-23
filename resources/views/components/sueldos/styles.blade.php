<style>
    /* Estilos base */
    .uppercase-all {
        text-transform: uppercase !important;
    }

    /* Aplicar uppercase a elementos específicos */
    body, .content-wrapper, .main-header, .main-sidebar, .card-title,
    .info-box-text, .info-box-number, .custom-select, .btn, label,
    input, select, option, datalist, datalist option, .form-control,
    p, h1, h2, h3, h4, h5, h6, th, td, span, a, .dropdown-item,
    .alert, .modal-title, .modal-body p, .modal-content, .card-header,
    .card-footer, button, .close, .table thead th, .table tbody td,
    .dataTables_filter, .dataTables_info, .paginate_button,
    .info-box span {
        @extend .uppercase-all;
    }

    /* Tablas */
    .table-movimientos {
        th, td {
            vertical-align: middle !important;
        }
        
        th {
            background-color: #f4f6f9;
        }
    }

    /* Badges */
    .badge {
        padding: 5px 10px;
        color: white;
    }

    .badge-apertura { background-color: #28a745; }
    .badge-cierre { background-color: #dc3545; }

    .hora-movimiento {
        font-size: 0.9em;
        color: #6c757d;
        margin-left: 10px;
    }

    /* Sucursales */
    .sucursal-badge {
        @extend .badge;
        background-color: #17a2b8;
        border-radius: 4px;
        font-size: 0.9em;
    }

    .sucursal-matriz { background-color: #007bff; }
    .sucursal-rocio { background-color: #28a745; }
    .sucursal-norte { background-color: #17a2b8; }

    /* Rol de usuario */
    .rol-usuario {
        margin-bottom: 30px;
        border: 1px solid #ddd;
        padding: 20px;
        border-radius: 5px;
    }
</style> 