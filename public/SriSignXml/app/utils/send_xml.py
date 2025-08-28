import base64
import logging
from app.utils.sri_service import SRIService


async def send_xml_to_reception(pathXmlSigned: str, urlToReception: str):
    """Enviar XML al SRI para recepción usando el nuevo servicio SOAP"""
    try:
        print(f"📄 Leyendo XML firmado desde: {pathXmlSigned}")
        
        # Leer el XML firmado
        with open(pathXmlSigned, 'r', encoding='utf-8', errors='ignore') as f:
            xml_content = f.read()
        
        # Limpiar BOM si existe
        if xml_content.startswith('\ufeff'):
            xml_content = xml_content[1:]
        
        print(f"📦 XML leído: {len(xml_content)} caracteres")
        
        # Usar el nuevo servicio SRI
        sri_service = SRIService()
        resultado = sri_service.enviar_comprobante(xml_content)
        
        print(f"🎯 Resultado recepción SRI: {resultado}")
        
        # Devolver resultado completo en lugar de solo el booleano
        return resultado
        
    except Exception as e:
        error_msg = f'Error to send xml for reception: {str(e)}'
        print(f"❌ {error_msg}")
        logging.error(error_msg)
        return {
            'success': False,
            'estado': 'ERROR',
            'mensajes': [{'mensaje': error_msg, 'tipo': 'ERROR'}],
            'error': error_msg
        }


async def send_xml_to_authorization(accessKey: str, urlToAuthorization: str):
    """Solicitar autorización al SRI usando el nuevo servicio SOAP"""
    try:
        print(f"🔐 Solicitando autorización para clave: {accessKey}")
        
        # Usar el nuevo servicio SRI
        sri_service = SRIService()
        resultado = sri_service.autorizar_comprobante(accessKey)
        
        print(f"🎯 Resultado autorización SRI: {resultado}")
        
        return {
            'isValid': resultado['success'],
            'xml': resultado.get('comprobante', ''),
            'numeroAutorizacion': resultado.get('numero_autorizacion', ''),
            'fechaAutorizacion': resultado.get('fecha_autorizacion', ''),
            'estado': resultado.get('estado', ''),
            'mensajes': resultado.get('mensajes', [])
        }
        
    except Exception as e:
        error_msg = f'Error to send xml for authorization: {str(e)}'
        print(f"❌ {error_msg}")
        logging.error(error_msg)
        return {
            'isValid': False,
            'xml': '',
            'error': error_msg
        }
    try:
        async with AsyncClient(urlToAuthorization) as client:
            result = await client.service.autorizacionComprobante(accessKey)

            status = result.autorizaciones.autorizacion[0].estado

            if status == 'AUTORIZADO' or status == 'EN PROCESO':
                logging.info("Response authorization: ", status)

                xml = result.autorizaciones.autorizacion[0].comprobante
                return {
                    'isValid': True,
                    'status': status,
                    'xml': xml
                }
            else:
                logging.warning(result)
                return {
                    'isValid': False,
                    'status': status,
                    'xml': None
                }
    except Exception as e:
        logging.error('Error to send xml for reception: %s' % str(e))
        return {
            'isValid': False,
            'status': status,
            'xml': None,
            'error': str(e)
        }
