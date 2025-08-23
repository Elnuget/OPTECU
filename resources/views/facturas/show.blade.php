@extends('adminlte::page')
@section('title', 'Ver Factura XML')

@section('content_header')
<h1>Factura #{{ $factura->id }} - XML</h1>
@stop

@section('content')
<div class="card">
    <div class="card-header">
        <h3 class="card-title">
            <i class="fas fa-code"></i> Contenido del XML
        </h3>
        <div class="card-tools">
            @if($xmlContent)
            <button type="button" class="btn btn-sm btn-primary" onclick="copyXmlToClipboard()">
                <i class="fas fa-copy"></i> Copiar
            </button>
            <a href="{{ asset('storage/' . $factura->xml) }}" class="btn btn-sm btn-success" download>
                <i class="fas fa-download"></i> Descargar
            </a>
            @endif
            <a href="{{ route('facturas.index') }}" class="btn btn-sm btn-secondary">
                <i class="fas fa-arrow-left"></i> Volver
            </a>
        </div>
    </div>
    <div class="card-body">
        @if($xmlContent)
            <div style="max-height: 600px; overflow-y: auto; background-color: #f8f9fa; padding: 15px; border: 1px solid #dee2e6; border-radius: 4px;">
                <pre style="margin: 0; font-family: 'Courier New', monospace; font-size: 13px; line-height: 1.4; white-space: pre-wrap; word-wrap: break-word;" id="xmlContent">{{ $xmlContent }}</pre>
            </div>
        @else
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                No se pudo cargar el contenido del archivo XML.
            </div>
        @endif
    </div>
</div>
@stop

@section('css')
<style>
    /* Estilos simples para texto plano */
    .xml-container {
        background-color: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 15px;
        font-family: 'Courier New', monospace;
        font-size: 13px;
        line-height: 1.4;
    }
</style>
@stop

@section('js')
<script>
    function copyXmlToClipboard() {
        const xmlContent = document.getElementById('xmlContent');
        if (xmlContent) {
            const tempTextArea = document.createElement('textarea');
            tempTextArea.value = xmlContent.textContent;
            document.body.appendChild(tempTextArea);
            tempTextArea.select();
            document.execCommand('copy');
            document.body.removeChild(tempTextArea);
            
            toastr.success('XML copiado al portapapeles');
        }
    }
</script>
@stop
