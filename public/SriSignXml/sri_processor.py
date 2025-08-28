#!/usr/bin/env python3
# -*- coding: utf-8 -*-
"""
Procesador local SRI para OPTECU
Este script maneja el procesamiento completo de facturas SRI sin API HTTP
Optimizado para integración directa con Laravel/PHP
"""

import os
import sys
import json
import random
import asyncio
import tempfile
from pathlib import Path

# Configurar encoding para Windows
if sys.platform.startswith('win'):
    import codecs
    sys.stdout = codecs.getwriter('utf-8')(sys.stdout.detach())
    sys.stderr = codecs.getwriter('utf-8')(sys.stderr.detach())

# Agregar el directorio app al path para importaciones
sys.path.append(os.path.join(os.path.dirname(__file__), 'app'))

try:
    from utils.create_access_key import createAccessKey
    from utils.create_xml import createXml
    from utils.sign_xml import sign_xml_file
    from utils.send_xml import send_xml_to_reception, send_xml_to_authorization
    from utils.control_temp_file import createTempXmlFile, createTempFile
    from utils.sri_service import SRIService
    from models.invoice import Invoice, InfoToSignXml
    from dotenv import dotenv_values
except ImportError as e:
    print(f"Error importando módulos: {e}", file=sys.stderr)
    sys.exit(1)

def load_config():
    """
    Cargar configuración para ambiente de PRUEBAS SRI
    CONFIGURACIÓN HARDCODEADA PARA SEGURIDAD Y AMBIENTE DE PRUEBAS
    """
    # Configuración fija para ambiente de pruebas - NO cambiar sin autorización
    config = {
        'URL_RECEPTION': 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
        'URL_AUTHORIZATION': 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl',
        'AMBIENTE': '1',  # 1 = Pruebas, 2 = Producción
        'TIPO_EMISION': '1'  # 1 = Normal, 2 = Contingencia
    }
    
    # VALIDACIÓN CRÍTICA: Asegurar ambiente de pruebas
    validar_ambiente_pruebas(config)
    
    log_message("CONFIGURACIÓN: Ambiente de PRUEBAS SRI cargado (sin .env)")
    return config

def log_message(message):
    """Imprimir mensaje de log a stderr para no interferir con el JSON de salida"""
    print(message, file=sys.stderr)

def validar_ambiente_pruebas(config):
    """
    Validación de seguridad: asegurar que estemos en ambiente de pruebas
    CRÍTICO: Previene envío accidental a producción SRI
    """
    urls_to_check = [
        config.get('URL_RECEPTION', ''),
        config.get('URL_AUTHORIZATION', '')
    ]
    
    for url in urls_to_check:
        if url:
            # Verificar que sea ambiente de pruebas (celcer)
            if 'celcer.sri.gob.ec' not in url:
                if 'cel.sri.gob.ec' in url:
                    raise Exception('PELIGRO: Detectada URL de PRODUCCIÓN. Sistema configurado solo para pruebas')
                else:
                    raise Exception('PELIGRO: URL no reconocida. Sistema configurado solo para pruebas')
    
    # Verificar ambiente
    if config.get('AMBIENTE') != '1':
        raise Exception('PELIGRO: Ambiente no configurado para pruebas')
    
    log_message("VALIDACIÓN: Confirmado ambiente de PRUEBAS SRI (celcer)")
    return True

def procesar_factura_completa(invoice_data, certificate_path, password):
    """
    Procesar factura completa: generar XML, firmar y enviar al SRI
    
    Args:
        invoice_data: Datos de la factura en formato dict
        certificate_path: Ruta al certificado P12
        password: Contraseña del certificado
        
    Returns:
        dict: Resultado del procesamiento
    """
    try:
        config = load_config()
        
        # Verificar que el certificado existe
        if not os.path.exists(certificate_path):
            return {
                'success': False,
                'message': f'Certificado no encontrado: {certificate_path}',
                'result': None
            }
        
        # Crear objeto Invoice desde los datos
        invoice = Invoice(**invoice_data)
        
        # Generar clave de acceso
        random_number = str(random.randint(1, 99999999)).zfill(8)
        access_key = createAccessKey(
            documentInfo=invoice.documentInfo, 
            randomNumber=random_number
        )
        
        log_message(f"Clave de acceso generada: {access_key}")
        
        # Generar XML
        xml_data = createXml(info=invoice, accessKeyInvoice=access_key)
        xml_string = xml_data['xmlString']
        xml_filename = f"{access_key}.xml"
        
        log_message(f"📄 XML generado: {len(xml_string)} caracteres")
        
        # Crear archivos temporales
        xml_no_signed = createTempXmlFile(xml_string, xml_filename)
        xml_signed = createTempXmlFile(xml_string, xml_filename)
        
        # Preparar certificado
        with open(certificate_path, 'rb') as file:
            digital_signature = file.read()
            certificate_to_sign = createTempFile(digital_signature, 'signature.p12')
        
        # Configurar información para firma
        info_to_sign = InfoToSignXml(
            pathXmlToSign=xml_no_signed.name,
            pathXmlSigned=xml_signed.name,
            pathSignatureP12=certificate_to_sign.name,
            passwordSignature=password
        )
        
        log_message(f"🔐 Iniciando proceso de firma...")
        
        # Firmar XML
        is_xml_created = sign_xml_file(info_to_sign)
        
        if not is_xml_created:
            return {
                'success': False,
                'message': 'Error al firmar el XML',
                'result': None
            }
        
        log_message(f"XML firmado exitosamente")
        
        # Leer XML firmado
        xml_signed_content = None
        try:
            for encoding in ['utf-8', 'latin-1', 'cp1252']:
                try:
                    with open(xml_signed.name, 'r', encoding=encoding) as f:
                        xml_signed_content = f.read()
                    break
                except UnicodeDecodeError:
                    continue
            else:
                # Fallback: leer como binario
                with open(xml_signed.name, 'rb') as f:
                    xml_bytes = f.read()
                xml_signed_content = xml_bytes.decode('utf-8', errors='ignore')
        except Exception as e:
            log_message(f"Error leyendo XML firmado: {e}")
        
        # Enviar al SRI para recepción
        is_received = False
        is_authorized = False
        
        if config.get('URL_RECEPTION'):
            log_message(f"📤 Enviando a recepción SRI...")
            try:
                # Usar asyncio para las funciones async
                loop = asyncio.new_event_loop()
                asyncio.set_event_loop(loop)
                
                is_received = loop.run_until_complete(
                    send_xml_to_reception(
                        pathXmlSigned=xml_signed.name,
                        urlToReception=config['URL_RECEPTION']
                    )
                )
                
                if is_received:
                    log_message(f"XML recibido por el SRI")
                    
                    # Enviar para autorización
                    if config.get('URL_AUTHORIZATION'):
                        log_message(f"📋 Solicitando autorización...")
                        response_auth = loop.run_until_complete(
                            send_xml_to_authorization(
                                access_key,
                                config['URL_AUTHORIZATION']
                            )
                        )
                        
                        is_authorized = response_auth.get('isValid', False)
                        
                        if is_authorized:
                            log_message(f"XML autorizado por el SRI")
                            # Actualizar XML con la versión autorizada si está disponible
                            if response_auth.get('xml'):
                                xml_signed_content = response_auth['xml']
                        else:
                            log_message(f"⏳ XML recibido, esperando autorización")
                
                loop.close()
                
            except Exception as e:
                log_message(f"Error en comunicación con SRI: {e}")
                # Continuar con el XML firmado aunque no se pueda enviar al SRI
        
        return {
            'success': True,
            'message': 'Procesamiento completado exitosamente',
            'result': {
                'accessKey': access_key,
                'isReceived': is_received,
                'isAuthorized': is_authorized,
                'xmlFileSigned': xml_signed_content
            }
        }
        
    except Exception as e:
        return {
            'success': False,
            'message': f'Error en procesamiento: {str(e)}',
            'result': None
        }

def consultar_estado_autorizacion(access_key):
    """
    Consultar el estado de autorización de una factura
    
    Args:
        access_key: Clave de acceso de la factura
        
    Returns:
        dict: Estado de la autorización
    """
    try:
        config = load_config()
        sri_service = SRIService()
        result = sri_service.consultar_autorizacion(access_key)
        
        estado = result.get('estado', 'DESCONOCIDO')
        autorizado = result.get('success', False)
        
        if estado == 'AUTORIZADO':
            autorizado = True
        elif estado in ['RECHAZADO', 'DEVUELTA']:
            autorizado = False
        else:
            autorizado = None
            
        return {
            'success': True,
            'result': {
                'accessKey': access_key,
                'estado': estado,
                'isAuthorized': autorizado,
                'numeroAutorizacion': result.get('numero_autorizacion'),
                'fechaAutorizacion': result.get('fecha_autorizacion'),
                'ambiente': result.get('ambiente'),
                'mensajes': result.get('mensajes', []),
                'xmlAutorizado': result.get('comprobante')
            }
        }
        
    except Exception as e:
        return {
            'success': False,
            'message': f'Error consultando estado: {str(e)}',
            'result': None
        }

def main():
    """Función principal del script"""
    if len(sys.argv) < 2:
        log_message("Uso: python sri_processor.py <comando> [argumentos]")
        log_message("Comandos disponibles:")
        log_message("  procesar <json_file> <certificate_path> <password>")
        log_message("  consultar <access_key>")
        sys.exit(1)
    
    comando = sys.argv[1]
    
    try:
        if comando == 'procesar':
            if len(sys.argv) < 5:
                log_message("Uso: python sri_processor.py procesar <json_file> <certificate_path> <password>")
                sys.exit(1)
            
            json_file = sys.argv[2]
            certificate_path = sys.argv[3]
            password = sys.argv[4]
            
            # Cargar datos de la factura
            with open(json_file, 'r', encoding='utf-8') as f:
                invoice_data = json.load(f)
            
            resultado = procesar_factura_completa(invoice_data, certificate_path, password)
            print(json.dumps(resultado, ensure_ascii=False, indent=2))
            
        elif comando == 'consultar':
            if len(sys.argv) < 3:
                log_message("Uso: python sri_processor.py consultar <access_key>")
                sys.exit(1)
            
            access_key = sys.argv[2]
            resultado = consultar_estado_autorizacion(access_key)
            print(json.dumps(resultado, ensure_ascii=False, indent=2))
            
        else:
            log_message(f"Comando desconocido: {comando}")
            sys.exit(1)
            
    except Exception as e:
        error_result = {
            'success': False,
            'message': f'Error ejecutando comando {comando}: {str(e)}',
            'result': None
        }
        print(json.dumps(error_result, ensure_ascii=False, indent=2))
        sys.exit(1)

if __name__ == "__main__":
    main()
