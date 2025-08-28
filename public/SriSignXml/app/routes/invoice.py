import os
import random
import asyncio
from fastapi import APIRouter
from app.models.invoice import Invoice, InfoToSignXml
from app.utils.create_access_key import createAccessKey
from app.utils.create_xml import createXml
from app.utils.sign_xml import sign_xml_file
from app.utils.send_xml import send_xml_to_reception, send_xml_to_authorization
from app.utils.control_temp_file import createTempXmlFile, createTempFile
from app.utils.get_content_xml_file import get_content_xml_file
from app.utils.sri_service import SRIService
from dotenv import dotenv_values

routerInvoice = APIRouter()
config = {
    **dotenv_values('.env')
}


@routerInvoice.post("/invoice/sign", tags=['Invoice'])
async def sign_invoice(invoice: Invoice):
    try:
        # create access key
        randomNumber = str(random.randint(1, 99999999)).zfill(8)
        accessKey = createAccessKey(
            documentInfo=invoice.documentInfo, randomNumber=randomNumber)

        # generate xml
        xmlData = createXml(info=invoice, accessKeyInvoice=accessKey)

        # xml name
        xmlFileName = str(accessKey) + '.xml'

        # xml string
        xmlString = xmlData['xmlString']

        # create temp files to create xml
        xmlNoSigned = createTempXmlFile(xmlString, xmlFileName)
        xmlSigned = createTempXmlFile(xmlString, xmlFileName)

        # get digital signature
        certificateName = 'signature.p12'
        pathSignature = os.path.abspath('app/signature.p12')
        with open(pathSignature, 'rb') as file:
            digitalSignature = file.read()
            certificateToSign = createTempFile(
                digitalSignature, certificateName)

        # password of signature
        passwordP12 = config['PASSWORD']
        infoToSignXml = InfoToSignXml(
            pathXmlToSign=xmlNoSigned.name,
            pathXmlSigned=xmlSigned.name,
            pathSignatureP12=certificateToSign.name,
            passwordSignature=passwordP12)

        # sign xml and creating temp file
        print(f"🔐 Intentando firmar XML...")
        print(f"📂 Archivo XML no firmado: {xmlNoSigned.name}")
        print(f"📂 Archivo XML firmado: {xmlSigned.name}")
        print(f"🔑 Certificado P12: {certificateToSign.name}")
        print(f"🔒 Contraseña configurada: {'***' if passwordP12 else 'NO CONFIGURADA'}")
        
        isXmlCreated = sign_xml_file(infoToSignXml)
        print(f"✅ XML firmado exitosamente: {isXmlCreated}")

        # url for reception and authorization
        urlReception = config["URL_RECEPTION"]
        urlAuthorization = config["URL_AUTHORIZATION"]

        # send xml for reception
        isReceived = False
        if isXmlCreated:
            isReceived = await send_xml_to_reception(
                pathXmlSigned=xmlSigned.name,
                urlToReception=urlReception,
            )

        # send xml for authorization
        isAuthorized = False
        xmlSignedValue = None
        
        # Siempre obtener el contenido del XML firmado si se creó exitosamente
        if isXmlCreated:
            try:
                # Intentar diferentes encodings para leer el XML firmado
                for encoding in ['utf-8', 'latin-1', 'cp1252']:
                    try:
                        with open(xmlSigned.name, 'r', encoding=encoding) as f:
                            xmlSignedValue = f.read()
                        print(f"✅ XML firmado leído exitosamente con encoding {encoding}: {len(xmlSignedValue)} caracteres")
                        break
                    except UnicodeDecodeError:
                        continue
                else:
                    # Si ningún encoding funciona, leer como binario y convertir
                    with open(xmlSigned.name, 'rb') as f:
                        xml_bytes = f.read()
                    xmlSignedValue = xml_bytes.decode('utf-8', errors='ignore')
                    print(f"✅ XML firmado leído como binario: {len(xmlSignedValue)} caracteres")
            except Exception as e:
                print(f"❌ Error al leer XML firmado: {e}")
                xmlSignedValue = None
        
        if isReceived:
            responseAuthorization = await send_xml_to_authorization(
                accessKey,
                urlAuthorization,
            )
            isAuthorized = responseAuthorization['isValid']
            # Solo sobrescribir el XML si viene del SRI
            if 'xml' in responseAuthorization and responseAuthorization['xml']:
                xmlSignedValue = responseAuthorization['xml']

        return {
            'result': {
                'accessKey': accessKey,
                'isReceived': isReceived,
                'isAuthorized': isAuthorized,
                'xmlFileSigned': xmlSignedValue
            }
        }
    except Exception as e:
        print(e)
        return {'result': None}


@routerInvoice.get("/invoice/status/{access_key}", tags=['Invoice'])
async def check_invoice_status(access_key: str):
    """
    Consulta el estado de autorización de una factura usando su clave de acceso
    """
    try:
        print(f"🔍 Consultando estado de autorización para clave: {access_key}")
        
        # Usar el servicio SRI para consultar autorización
        sri_service = SRIService()
        result = sri_service.consultar_autorizacion(access_key)
        
        print(f"📋 Resultado consulta: {result}")
        
        # Determinar el estado basado en la respuesta
        estado = result.get('estado', 'DESCONOCIDO')
        autorizado = result.get('success', False)
        
        if estado == 'AUTORIZADO':
            autorizado = True
        elif estado in ['RECHAZADO', 'DEVUELTA']:
            autorizado = False
        else:
            autorizado = None  # Aún procesando
            
        return {
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
        print(f"❌ Error consultando estado: {e}")
        return {
            'error': str(e),
            'result': {
                'accessKey': access_key,
                'estado': 'ERROR',
                'isAuthorized': False
            }
        }
