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
        display: inline-block;
        padding: 4px 8px;
        color: white;
        border-radius: 4px;
        font-size: 0.85em;
        margin: 2px 0;
        min-width: 80px;
        text-align: center;
    }

    .sucursal-matriz { 
        background-color: #007bff;
        &:hover { background-color: darken(#007bff, 10%); }
    }
    
    .sucursal-rocio { 
        background-color: #28a745;
        &:hover { background-color: darken(#28a745, 10%); }
    }
    
    .sucursal-norte { 
        background-color: #17a2b8;
        &:hover { background-color: darken(#17a2b8, 10%); }
    }

    /* Rol de usuario */
    .rol-usuario {
        margin-bottom: 30px;
        border: 1px solid #ddd;
        padding: 20px;
        border-radius: 5px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    /* Contenedores de datos */
    .pedidos-dia, .retiros-dia {
        padding: 8px;
        background-color: rgba(0,0,0,0.02);
        border-radius: 4px;
        
        strong {
            display: block;
            margin-bottom: 5px;
            color: #495057;
        }
        
        ul {
            margin: 0;
            padding: 0;
            
            li {
                padding: 2px 0;
                border-bottom: 1px dashed rgba(0,0,0,0.1);
                
                &:last-child {
                    border-bottom: none;
                }
            }
        }
    }

    /* Nuevos estilos para las sucursales */
    .badge-info {
        transition: all 0.3s ease;
    }

    .badge-info:hover {
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    }

    .text-muted i {
        opacity: 0.7;
    }

    /* Estilos específicos por sucursal */
    .badge-info[data-sucursal="MATRIZ"] {
        background-color: #007bff;
    }

    .badge-info[data-sucursal="ROCÍO"] {
        background-color: #17a2b8;
    }

    .badge-info[data-sucursal="NORTE"] {
        background-color: #6610f2;
    }

    /* Nuevos estilos para detalles de sucursal */
    .sucursal-container {
        background-color: rgba(0,0,0,0.02);
        border-radius: 4px;
        padding: 5px;
    }

    .sucursal-details {
        margin-top: 4px;
    }

    .badge-sm {
        font-size: 0.75em;
        padding: 3px 6px;
        margin: 0 2px;
    }

    /* Estilos para los badges de usuario */
    .hora-movimiento small {
        margin-top: 2px;
        font-size: 0.85em;
    }

    .hora-movimiento small i {
        margin-right: 3px;
    }

    /* Ajustes para los badges de operaciones */
    .badge-warning {
        background-color: #ffc107;
        color: #000;
    }

    .badge-success {
        background-color: #28a745;
    }

    .badge-danger {
        background-color: #dc3545;
    }

    /* Hover effects */
    .badge-warning:hover,
    .badge-success:hover,
    .badge-danger:hover {
        opacity: 0.9;
    }

    /* Estilos para movimientos por sucursal */
    .sucursal-movimientos {
        background-color: rgba(0,0,0,0.03);
        border-radius: 4px;
        padding: 8px;
    }

    .sucursal-movimientos .badge {
        width: 100%;
        text-align: left;
        white-space: normal;
    }

    .badge-secondary {
        background-color: #6c757d;
        font-size: 0.8em;
    }

    .badge-apertura, .badge-cierre {
        margin: 4px 0;
        padding: 6px 8px;
    }

    .badge-apertura {
        background-color: #28a745;
    }

    .badge-cierre {
        background-color: #dc3545;
    }

    .hora-movimiento {
        float: right;
        text-align: right;
    }

    /* Ajustes responsive */
    @media (max-width: 768px) {
        .hora-movimiento {
            display: block;
            float: none;
            margin-top: 4px;
        }
    }
</style> 