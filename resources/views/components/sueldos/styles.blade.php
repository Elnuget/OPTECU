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
        padding: 8px 12px;
        color: white;
        font-size: 0.95em;
        margin-bottom: 5px;
        display: block;
        text-align: left;
    }

    .badge-apertura { 
        background-color: #1a8e3c; 
        border: 1px solid #157a33;
    }

    .badge-cierre { 
        background-color: #c82333; 
        border: 1px solid #bd2130;
    }

    .badge-secondary {
        background-color: #4a5568;
        border: 1px solid #2d3748;
    }

    .hora-movimiento {
        float: right;
        text-align: right;
        background-color: rgba(255, 255, 255, 0.15);
        padding: 4px 8px;
        border-radius: 4px;
        margin-left: 10px;
    }

    .hora-movimiento div {
        color: #ffffff;
        font-weight: 500;
        text-shadow: 0 1px 2px rgba(0,0,0,0.2);
    }

    .hora-movimiento i {
        color: rgba(255, 255, 255, 0.9);
        width: 16px;
        text-align: center;
    }

    .fecha-movimiento {
        margin-bottom: 4px;
        border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        padding-bottom: 4px;
    }

    /* Sucursal badges */
    .sucursal-movimientos {
        margin-bottom: 12px;
        border-left: 3px solid #e2e8f0;
        padding-left: 10px;
    }

    .sucursal-movimientos .badge {
        width: 100%;
        margin-bottom: 8px;
    }

    /* Responsive adjustments */
    @media (max-width: 768px) {
        .hora-movimiento {
            float: none;
            display: block;
            margin-top: 8px;
            margin-left: 0;
            background-color: rgba(255, 255, 255, 0.2);
        }

        .badge {
            padding: 10px;
        }

        .fecha-movimiento {
            display: block;
            margin-bottom: 6px;
        }
    }

    /* Mejoras en el contenedor de operaciones */
    .operaciones-container {
        background-color: #ffffff;
        border-radius: 8px;
        padding: 15px;
        box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }

    .sucursal-operaciones {
        margin-bottom: 12px;
        padding: 8px 12px;
        border-left: 3px solid #e2e8f0;
        background-color: #f8fafc;
    }

    .total-general {
        margin-top: 12px;
        padding: 8px 12px;
        background-color: #f7fafc;
        border-left: 3px solid #4a5568;
        color: #2d3748;
        font-weight: 600;
        border-radius: 0;
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
        margin-top: 8px;
    }

    .pedidos-dia strong,
    .retiros-dia strong {
        color: #2d3748;
        font-size: 0.9em;
    }

    .list-unstyled li {
        padding: 4px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.85em;
        color: #4a5568;
    }

    .list-unstyled li:last-child {
        border-bottom: none;
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

    /* Ajustes responsivos */
    @media (max-width: 768px) {
        .table-movimientos {
            font-size: 0.85em;
        }

        .table-movimientos td {
            padding: 0.5rem;
        }

        .sucursal-operaciones {
            margin-bottom: 10px;
        }

        .pedidos-dia, .retiros-dia {
            padding: 8px;
        }

        .badge {
            white-space: normal;
            text-align: left;
        }

        .total-general {
            text-align: left;
            margin-top: 8px;
            padding: 6px;
        }

        .hora-movimiento {
            display: block;
            float: none;
            margin-top: 4px;
            font-size: 0.9em;
        }

        .sucursal-operaciones .badge-secondary {
            font-size: 0.9em;
        }

        ul.list-unstyled li {
            margin-bottom: 5px;
            padding-bottom: 5px;
            border-bottom: 1px solid rgba(0,0,0,0.05);
        }

        ul.list-unstyled li:last-child {
            margin-bottom: 0;
            padding-bottom: 0;
            border-bottom: none;
        }
    }

    /* Ajustes para pantallas muy pequeñas */
    @media (max-width: 576px) {
        .table-movimientos {
            font-size: 0.8em;
        }

        .badge {
            display: block;
            margin-bottom: 4px;
        }

        .sucursal-operaciones {
            padding: 8px;
        }

        .total-general strong {
            display: block;
            font-size: 0.95em;
        }
    }

    /* Estilos para fecha y hora en movimientos */
    .hora-movimiento {
        float: right;
        text-align: right;
        font-size: 0.9em;
    }

    .fecha-movimiento {
        margin-bottom: 2px;
        font-size: 0.95em;
    }

    .badge-apertura, .badge-cierre {
        position: relative;
        padding-right: 120px; /* Espacio para la información de fecha/hora */
    }

    .badge i {
        opacity: 0.8;
    }

    /* Ajustes responsivos para fecha y hora */
    @media (max-width: 768px) {
        .hora-movimiento {
            float: none;
            text-align: left;
            margin-top: 5px;
            display: block;
        }

        .badge-apertura, .badge-cierre {
            padding-right: 12px;
        }

        .fecha-movimiento {
            display: inline-block;
            margin-right: 10px;
        }

        .hora-movimiento div {
            display: inline-block;
            margin-right: 10px;
        }

        .hora-movimiento small {
            margin-top: 4px;
        }
    }

    @media (max-width: 576px) {
        .hora-movimiento div {
            display: block;
            margin-bottom: 3px;
        }

        .fecha-movimiento {
            display: block;
            margin-bottom: 3px;
        }
    }

    /* Título de sucursal */
    .badge-secondary {
        background: none;
        color: #4a5568;
        padding: 4px 0;
        font-size: 0.9em;
        font-weight: 600;
        margin-bottom: 8px;
        border: none;
    }

    .badge-secondary i {
        color: #718096;
        margin-right: 6px;
    }

    /* Estilos para apertura y cierre */
    .badge-apertura,
    .badge-cierre {
        background: none;
        border: none;
        padding: 6px 0;
        margin-bottom: 6px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        font-size: 0.9em;
    }

    .badge-apertura {
        color: #2f855a;
    }

    .badge-cierre {
        color: #c53030;
    }

    /* Contenedor de hora y fecha */
    .hora-movimiento {
        color: #718096;
        font-size: 0.85em;
        background: none;
        padding: 0;
        text-align: right;
        float: none;
        display: inline-block;
        margin-left: 8px;
    }

    .hora-movimiento div {
        display: inline-block;
        margin-left: 8px;
        color: #718096;
        text-shadow: none;
    }

    .hora-movimiento i {
        color: #a0aec0;
        margin-right: 3px;
    }

    .fecha-movimiento {
        border: none;
        margin: 0;
        padding: 0;
        display: inline-block;
    }

    /* Ajustes responsivos */
    @media (max-width: 768px) {
        .sucursal-movimientos {
            margin-bottom: 16px;
        }

        .badge-apertura,
        .badge-cierre {
            flex-direction: column;
            align-items: flex-start;
        }

        .hora-movimiento {
            margin-top: 4px;
            margin-left: 0;
            display: block;
        }

        .hora-movimiento div {
            display: inline-block;
            margin-right: 8px;
            margin-left: 0;
        }
    }

    /* Estilos para operaciones */
    .operaciones-container {
        margin-bottom: 12px;
    }

    .pedidos-dia,
    .retiros-dia {
        margin-top: 8px;
    }

    .pedidos-dia strong,
    .retiros-dia strong {
        color: #2d3748;
        font-size: 0.9em;
    }

    .list-unstyled li {
        padding: 4px 0;
        border-bottom: 1px solid #f0f0f0;
        font-size: 0.85em;
        color: #4a5568;
    }

    .list-unstyled li:last-child {
        border-bottom: none;
    }

    /* Ajustes de espaciado y alineación */
    .table-movimientos td {
        padding: 12px;
        vertical-align: top;
    }

    .table-movimientos th {
        padding: 10px 12px;
        background-color: #f7fafc;
        font-weight: 600;
        color: #4a5568;
        border-bottom: 2px solid #e2e8f0;
    }

    /* Estilos para la columna de fecha */
    .table-movimientos td:first-child {
        white-space: nowrap;
        min-width: 120px;
    }

    .table-movimientos td:first-child .text-muted {
        margin-top: 4px;
    }

    .table-movimientos td:first-child .fas {
        margin-right: 4px;
        color: #a0aec0;
    }

    /* Ajustes responsivos para la columna de fecha */
    @media (max-width: 768px) {
        .table-movimientos td:first-child {
            min-width: 100px;
        }
        
        .table-movimientos td:first-child .text-muted {
            margin-top: 2px;
        }
    }

    .valor-editable {
        width: 100px;
        text-align: right;
        padding: 4px 8px;
        border: 1px solid #ddd;
        border-radius: 4px;
    }

    .valor-editable:focus {
        border-color: #80bdff;
        outline: 0;
        box-shadow: 0 0 0 0.2rem rgba(0,123,255,.25);
    }
</style> 