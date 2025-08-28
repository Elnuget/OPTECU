#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Servicio de Envío al SRI Ecuador
Implementación en Python para envío y autorización de comprobantes electrónicos
"""

import sys
import json
import base64
import requests
import xml.etree.ElementTree as ET
from datetime import datetime
import traceback
import urllib3
from requests.adapters import HTTPAdapter
from urllib3.util.retry import Retry

# Deshabilitar warnings SSL para ambiente de pruebas
urllib3.disable_warnings(urllib3.exceptions.InsecureRequestWarning)

class SRIService:
    def __init__(self):
        # URLs del SRI (siempre usar ambiente de pruebas)
        self.url_recepcion = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline'
        self.url_autorizacion = 'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline'
        
        # Configurar sesión con reintentos
        self.session = requests.Session()
        retry_strategy = Retry(
            total=3,
            status_forcelist=[429, 500, 502, 503, 504],
            allowed_methods=["HEAD", "GET", "OPTIONS", "POST"]
        )
        adapter = HTTPAdapter(max_retries=retry_strategy)
        self.session.mount("http://", adapter)
        self.session.mount("https://", adapter)

    def extraer_clave_acceso(self, xml_content):
        """Extraer clave de acceso del XML"""
        try:
            root = ET.fromstring(xml_content)
            
            # Buscar elemento claveAcceso
            clave_acceso = None
            for elem in root.iter():
                if elem.tag.endswith('claveAcceso'):
                    clave_acceso = elem.text
                    break
            
            if not clave_acceso:
                raise Exception("No se encontró clave de acceso en el XML")
            
            return clave_acceso.strip()
            
        except Exception as e:
            raise Exception(f"Error extrayendo clave de acceso: {str(e)}")

    def enviar_comprobante(self, xml_firmado):
        """Enviar comprobante firmado al SRI"""
        try:
            # Extraer clave de acceso
            clave_acceso = self.extraer_clave_acceso(xml_firmado)
            
            # Convertir XML a base64
            xml_base64 = base64.b64encode(xml_firmado.encode('utf-8')).decode('ascii')
            
            # Crear SOAP envelope para recepción
            soap_envelope = self._crear_soap_recepcion(xml_base64)
            
            # Headers para la petición
            headers = {
                'Content-Type': 'text/xml; charset=utf-8',
                'SOAPAction': '',
                'User-Agent': 'Sistema Facturacion Electronica OPTECU'
            }
            
            # Enviar al SRI
            response = self.session.post(
                self.url_recepcion,
                data=soap_envelope,
                headers=headers,
                timeout=60,
                verify=False  # Para ambiente de pruebas
            )
            
            if response.status_code != 200:
                raise Exception(f"Error HTTP {response.status_code}: {response.text}")
            
            # Procesar respuesta
            resultado = self._procesar_respuesta_recepcion(response.text)
            resultado['clave_acceso'] = clave_acceso
            
            return resultado
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'estado': 'ERROR'
            }

    def autorizar_comprobante(self, clave_acceso):
        """Solicitar autorización del comprobante al SRI"""
        try:
            # Crear SOAP envelope para autorización
            soap_envelope = self._crear_soap_autorizacion(clave_acceso)
            
            # Headers para la petición
            headers = {
                'Content-Type': 'text/xml; charset=utf-8',
                'SOAPAction': '',
                'User-Agent': 'Sistema Facturacion Electronica OPTECU'
            }
            
            # Enviar al SRI
            response = self.session.post(
                self.url_autorizacion,
                data=soap_envelope,
                headers=headers,
                timeout=60,
                verify=False  # Para ambiente de pruebas
            )
            
            if response.status_code != 200:
                raise Exception(f"Error HTTP {response.status_code}: {response.text}")
            
            # Procesar respuesta
            resultado = self._procesar_respuesta_autorizacion(response.text)
            
            return resultado
            
        except Exception as e:
            return {
                'success': False,
                'error': str(e),
                'estado': 'ERROR'
            }

    def _crear_soap_recepcion(self, xml_base64):
        """Crear SOAP envelope para recepción"""
        return f'''<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" 
               xmlns:rec="http://ec.gob.sri.ws.recepcion">
    <soap:Header />
    <soap:Body>
        <rec:validarComprobante>
            <xml>{xml_base64}</xml>
        </rec:validarComprobante>
    </soap:Body>
</soap:Envelope>'''

    def _crear_soap_autorizacion(self, clave_acceso):
        """Crear SOAP envelope para autorización"""
        return f'''<?xml version="1.0" encoding="UTF-8"?>
<soap:Envelope xmlns:soap="http://schemas.xmlsoap.org/soap/envelope/" 
               xmlns:aut="http://ec.gob.sri.ws.autorizacion">
    <soap:Header />
    <soap:Body>
        <aut:autorizacionComprobante>
            <claveAccesoComprobante>{clave_acceso}</claveAccesoComprobante>
        </aut:autorizacionComprobante>
    </soap:Body>
</soap:Envelope>'''

    def _procesar_respuesta_recepcion(self, xml_response):
        """Procesar respuesta XML de recepción del SRI"""
        try:
            root = ET.fromstring(xml_response)
            
            # Buscar elemento de respuesta
            estado = None
            mensajes = []
            
            # Navegar por el XML de respuesta
            for elem in root.iter():
                if elem.tag.endswith('estado'):
                    estado = elem.text
                elif elem.tag.endswith('mensaje'):
                    mensaje_info = {
                        'identificador': '',
                        'mensaje': elem.text or '',
                        'informacion_adicional': '',
                        'tipo': 'INFO'
                    }
                    
                    # Buscar información adicional del mensaje
                    parent = elem.getparent() if hasattr(elem, 'getparent') else None
                    if parent is not None:
                        for child in parent:
                            if child.tag.endswith('identificador'):
                                mensaje_info['identificador'] = child.text or ''
                            elif child.tag.endswith('informacionAdicional'):
                                mensaje_info['informacion_adicional'] = child.text or ''
                            elif child.tag.endswith('tipo'):
                                mensaje_info['tipo'] = child.text or 'INFO'
                    
                    mensajes.append(mensaje_info)
            
            # Determinar success basado en el estado
            success = estado and estado.upper() == 'RECIBIDA'
            
            return {
                'success': success,
                'estado': estado or 'DESCONOCIDO',
                'mensajes': mensajes,
                'respuesta_completa': xml_response
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': f"Error procesando respuesta: {str(e)}",
                'estado': 'ERROR_PROCESAMIENTO',
                'respuesta_completa': xml_response
            }

    def _procesar_respuesta_autorizacion(self, xml_response):
        """Procesar respuesta XML de autorización del SRI"""
        try:
            root = ET.fromstring(xml_response)
            
            # Buscar información de autorización
            estado = None
            numero_autorizacion = None
            fecha_autorizacion = None
            ambiente = None
            comprobante = None
            mensajes = []
            
            for elem in root.iter():
                if elem.tag.endswith('estado'):
                    estado = elem.text
                elif elem.tag.endswith('numeroAutorizacion'):
                    numero_autorizacion = elem.text
                elif elem.tag.endswith('fechaAutorizacion'):
                    fecha_autorizacion = elem.text
                elif elem.tag.endswith('ambiente'):
                    ambiente = elem.text
                elif elem.tag.endswith('comprobante'):
                    comprobante = elem.text
                elif elem.tag.endswith('mensaje'):
                    mensaje_info = {
                        'identificador': '',
                        'mensaje': elem.text or '',
                        'informacion_adicional': '',
                        'tipo': 'INFO'
                    }
                    
                    # Buscar información adicional del mensaje
                    parent = elem.getparent() if hasattr(elem, 'getparent') else None
                    if parent is not None:
                        for child in parent:
                            if child.tag.endswith('identificador'):
                                mensaje_info['identificador'] = child.text or ''
                            elif child.tag.endswith('informacionAdicional'):
                                mensaje_info['informacion_adicional'] = child.text or ''
                            elif child.tag.endswith('tipo'):
                                mensaje_info['tipo'] = child.text or 'INFO'
                    
                    mensajes.append(mensaje_info)
            
            # Determinar success basado en el estado
            success = estado and estado.upper() == 'AUTORIZADO'
            
            return {
                'success': success,
                'estado': estado or 'DESCONOCIDO',
                'numero_autorizacion': numero_autorizacion,
                'fecha_autorizacion': fecha_autorizacion,
                'ambiente': ambiente,
                'comprobante': comprobante,
                'mensajes': mensajes,
                'respuesta_completa': xml_response
            }
            
        except Exception as e:
            return {
                'success': False,
                'error': f"Error procesando respuesta de autorización: {str(e)}",
                'estado': 'ERROR_PROCESAMIENTO',
                'respuesta_completa': xml_response
            }

def main():
    """Función principal para ser llamada desde PHP"""
    try:
        if len(sys.argv) < 2:
            print(json.dumps({
                'success': False,
                'error': 'Uso: python sri_service.py <accion> [parametros]'
            }))
            sys.exit(1)

        accion = sys.argv[1]
        sri_service = SRIService()

        if accion == 'enviar':
            if len(sys.argv) < 3:
                print(json.dumps({
                    'success': False,
                    'error': 'Uso: python sri_service.py enviar <xml_file>'
                }))
                sys.exit(1)
            
            xml_file = sys.argv[2]
            
            # Leer el XML firmado
            with open(xml_file, 'r', encoding='utf-8-sig') as f:
                xml_content = f.read()
            
            # Limpiar BOM si existe
            if xml_content.startswith('\ufeff'):
                xml_content = xml_content[1:]
            
            # Enviar al SRI
            resultado = sri_service.enviar_comprobante(xml_content)
            print(json.dumps(resultado))

        elif accion == 'autorizar':
            if len(sys.argv) < 3:
                print(json.dumps({
                    'success': False,
                    'error': 'Uso: python sri_service.py autorizar <clave_acceso>'
                }))
                sys.exit(1)
            
            clave_acceso = sys.argv[2]
            
            # Solicitar autorización
            resultado = sri_service.autorizar_comprobante(clave_acceso)
            print(json.dumps(resultado))

        else:
            print(json.dumps({
                'success': False,
                'error': f'Acción no reconocida: {accion}'
            }))
            sys.exit(1)

    except Exception as e:
        error_result = {
            'success': False,
            'error': str(e),
            'traceback': traceback.format_exc()
        }
        print(json.dumps(error_result))
        sys.exit(1)

if __name__ == "__main__":
    main()
