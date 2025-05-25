@push('css')
    <style>
        /* Estilos base y transformación a mayúsculas */
        body, 
        .content-wrapper, 
        .main-header, 
        .main-sidebar, 
        .card-title,
        .info-box-text,
        .info-box-number,
        .custom-select,
        .btn,
        label,
        input,
        select,
        option,
        .datalist,
        .form-control,
        p,
        h1, h2, h3, h4, h5, h6,
        th,
        td,
        span,
        a,
        .dropdown-item,
        .alert,
        .modal-title,
        .modal-body p,
        .dropdown-menu,
        .nav-link,
        .menu-item {
            text-transform: uppercase !important;
        }

        /* Estilos responsivos generales */
        .card-body {
            padding: 1rem;
        }

        .form-row {
            margin-right: -5px;
            margin-left: -5px;
        }

        .form-row > [class*='col-'] {
            padding-right: 5px;
            padding-left: 5px;
        }

        /* Ajustes responsivos para la barra de herramientas */
        .btn-toolbar {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn-group {
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .btn {
            white-space: nowrap;
            padding: 0.375rem 0.75rem;
            font-size: 0.875rem;
        }

        /* Ajustes para tablas responsivas */
        .table-responsive {
            margin: 0;
            padding: 0;
            border: none;
            width: 100%;
        }

        .table {
            min-width: 100%;
        }

        .table td, .table th {
            padding: 0.5rem;
            font-size: 0.875rem;
            white-space: nowrap;
        }

        /* Ajustes responsivos para tarjetas */
        .card-header {
            padding: 0.75rem 1rem;
        }

        .card-title {
            font-size: 1rem;
            margin: 0;
        }

        .badge {
            font-size: 0.75rem;
            white-space: nowrap;
        }

        /* Media queries para dispositivos móviles */
        @media (max-width: 768px) {
            /* Ajustes del formulario en móvil */
            .form-row > [class*='col-'] {
                margin-bottom: 0.5rem;
            }

            /* Ajustes de botones en móvil */
            .btn-toolbar {
                justify-content: center;
            }

            .btn {
                padding: 0.25rem 0.5rem;
                font-size: 0.8rem;
            }

            /* Ajustes de tarjetas en móvil */
            .card-header {
                padding: 0.5rem;
            }

            .card-title {
                font-size: 0.9rem;
            }

            .badge {
                font-size: 0.7rem;
                padding: 0.25em 0.5em;
            }

            /* Ajustes de tabla en móvil */
            .table td, .table th {
                padding: 0.25rem;
                font-size: 0.8rem;
            }

            /* Ajustes de los iconos en móvil */
            .fas {
                font-size: 0.9rem;
            }

            /* Mejorar visualización de badges en móvil */
            .d-flex.align-items-center {
                flex-wrap: wrap;
                gap: 0.25rem;
            }

            .badge {
                margin: 0.1rem !important;
            }
        }

        /* Media queries para tablets */
        @media (min-width: 769px) and (max-width: 1024px) {
            .btn {
                padding: 0.3rem 0.6rem;
                font-size: 0.85rem;
            }

            .card-title {
                font-size: 0.95rem;
            }

            .table td, .table th {
                padding: 0.4rem;
                font-size: 0.85rem;
            }
        }

        /* Ajustes para la búsqueda y filtros */
        #busquedaGlobal {
            max-width: 100%;
        }

        /* Ajustes para los campos editables */
        .editable .edit-input {
            width: 100%;
            min-width: 50px;
        }

        /* Asegurar que los menús desplegables sean responsivos */
        .dropdown-menu {
            max-width: 100%;
            max-height: 80vh;
            overflow-y: auto;
        }

        /* Estilos para la tabla de inventario */
        .inventario-table {
            width: 100%;
            margin: 0;
            border-collapse: collapse;
        }

        .inventario-table th,
        .inventario-table td {
            padding: 8px 12px;
            vertical-align: middle;
            border: 1px solid #dee2e6;
        }

        .inventario-table th {
            background-color: #f8f9fa;
            font-weight: 600;
            white-space: nowrap;
        }

        /* Estilo para resaltar las filas duplicadas */
        .duplicate-row {
            background-color: #fff3cd !important;
        }
        .duplicate-row:hover {
            background-color: #ffeeba !important;
        }
    </style>
@endpush 