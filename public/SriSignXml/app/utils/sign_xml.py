import logging
from app.lib.xades.xades import Xades


def sign_xml_file(infoToSignXml: any):
    try:
        print(f"🔧 Iniciando proceso de firma...")
        print(f"📄 XML entrada: {infoToSignXml.pathXmlToSign}")
        print(f"📄 XML salida: {infoToSignXml.pathXmlSigned}")
        print(f"🔑 Certificado: {infoToSignXml.pathSignatureP12}")
        
        xades = Xades()
        result = xades.sign(
            infoToSignXml.pathXmlToSign,
            infoToSignXml.pathXmlSigned,
            infoToSignXml.pathSignatureP12,
            infoToSignXml.passwordSignature)
        
        print(f"📤 Resultado de la firma: {result}")
        
        # Verificar si la firma fue exitosa
        if result and b"Documento Firmado Correctamente" in result:
            print("✅ Firma digital completada exitosamente!")
            return True
        else:
            print("❌ La firma no se completó correctamente")
            return False
            
    except Exception as e:
        print(f"❌ Error al firmar XML: {str(e)}")
        logging.error('Error to sign xml: %s' % str(e))
        return False
