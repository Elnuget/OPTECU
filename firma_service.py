#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Servicio de Firma Digital Electrónica para Ecuador (SRI)
Implementación en Python para superar limitaciones de OpenSSL 3.0+
"""

import sys
import json
import base64
import hashlib
from datetime import datetime
from cryptography.hazmat import backends
from cryptography.hazmat.primitives import hashes, serialization
from cryptography.hazmat.primitives.asymmetric import padding, rsa
from cryptography.hazmat.primitives.asymmetric.utils import Prehashed
from cryptography import x509
from lxml import etree
import traceback

class FirmaElectronicaService:
    def __init__(self, p12_path, password):
        self.p12_path = p12_path
        self.password = password
        self.cert_data = None
        self.private_key = None
        self.certificate = None

        # Cargar el certificado P12
        self._load_certificate()

    def _load_certificate(self):
        """Carga el certificado P12 y extrae la clave privada y certificado"""
        try:
            with open(self.p12_path, 'rb') as f:
                p12_data = f.read()

            # Usar cryptography para parsear PKCS#12
            from cryptography.hazmat.primitives import serialization
            from cryptography.hazmat.primitives.kdf.pbkdf2 import PBKDF2HMAC

            # Intentar diferentes métodos para parsear P12
            try:
                # Método 1: Usar pkcs12.load_key_and_certificates
                from cryptography.hazmat.primitives.serialization import pkcs12

                private_key, certificate, additional_certificates = pkcs12.load_key_and_certificates(
                    p12_data,
                    self.password.encode(),
                    backends.default_backend()
                )

                if private_key and certificate:
                    self.private_key = private_key
                    self.certificate = certificate
                    # No imprimir mensajes cuando se ejecuta desde PHP
                    # print("Certificado cargado exitosamente con metodo estandar")
                    return

            except Exception as e:
                print("Metodo estandar fallo: {}".format(str(e)))

                # Método 2: Intentar con variaciones de contraseña
                for variation in [self.password + '\n', self.password + '\r\n', self.password.rstrip()]:
                    try:
                        private_key, certificate, additional_certificates = pkcs12.load_key_and_certificates(
                            p12_data,
                            variation.encode(),
                            backends.default_backend()
                        )

                        if private_key and certificate:
                            self.private_key = private_key
                            self.certificate = certificate
                            print("Certificado cargado exitosamente con variacion de contrasena: {}".format(repr(variation)))
                            return
                    except:
                        continue

            # Si todos los métodos fallan
            raise Exception("No se pudo cargar el certificado con ningun metodo disponible")

        except Exception as e:
            print("Error al cargar certificado: {}".format(str(e)))
            raise

    def get_certificate_info(self):
        """Obtiene información del certificado"""
        if not self.certificate:
            raise Exception("Certificado no cargado")

        cert_info = {
            'subject': str(self.certificate.subject),
            'issuer': str(self.certificate.issuer),
            'serial_number': str(self.certificate.serial_number),
            'not_valid_before': self.certificate.not_valid_before.isoformat(),
            'not_valid_after': self.certificate.not_valid_after.isoformat(),
            'signature_algorithm': str(self.certificate.signature_algorithm_oid),
            'public_key_algorithm': str(self.certificate.public_key_algorithm_oid)
        }

        return cert_info

    def sign_xml(self, xml_content):
        """Firma el XML con XAdES-BES"""
        try:
            # Parsear el XML
            root = etree.fromstring(xml_content.encode('utf-8'))

            # Crear el elemento de firma
            signature = etree.Element("{http://www.w3.org/2000/09/xmldsig#}Signature")
            signature.set("Id", "Signature-1")

            # SignedInfo
            signed_info = etree.SubElement(signature, "{http://www.w3.org/2000/09/xmldsig#}SignedInfo")

            # CanonicalizationMethod
            canonicalization = etree.SubElement(signed_info, "{http://www.w3.org/2000/09/xmldsig#}CanonicalizationMethod")
            canonicalization.set("Algorithm", "http://www.w3.org/TR/2001/REC-xml-c14n-20010315")

            # SignatureMethod
            signature_method = etree.SubElement(signed_info, "{http://www.w3.org/2000/09/xmldsig#}SignatureMethod")
            signature_method.set("Algorithm", "http://www.w3.org/2000/09/xmldsig#rsa-sha1")

            # Reference
            reference = etree.SubElement(signed_info, "{http://www.w3.org/2000/09/xmldsig#}Reference")
            reference.set("URI", "")

            # Transforms
            transforms = etree.SubElement(reference, "{http://www.w3.org/2000/09/xmldsig#}Transforms")
            transform = etree.SubElement(transforms, "{http://www.w3.org/2000/09/xmldsig#}Transform")
            transform.set("Algorithm", "http://www.w3.org/2000/09/xmldsig#enveloped-signature")

            # DigestMethod
            digest_method = etree.SubElement(reference, "{http://www.w3.org/2000/09/xmldsig#}DigestMethod")
            digest_method.set("Algorithm", "http://www.w3.org/2000/09/xmldsig#sha1")

            # Calcular digest del documento (sin la firma)
            doc_without_signature = etree.tostring(root, method="c14n", exclusive=True)
            digest = hashlib.sha1(doc_without_signature).digest()
            digest_value = etree.SubElement(reference, "{http://www.w3.org/2000/09/xmldsig#}DigestValue")
            digest_value.text = base64.b64encode(digest).decode()

            # SignatureValue
            signed_info_c14n = etree.tostring(signed_info, method="c14n", exclusive=True)
            signature_digest = hashlib.sha1(signed_info_c14n).digest()

            # Firmar con la clave privada
            signature_bytes = self.private_key.sign(
                signature_digest,
                padding.PKCS1v15(),
                hashes.SHA1()
            )

            signature_value = etree.SubElement(signature, "{http://www.w3.org/2000/09/xmldsig#}SignatureValue")
            signature_value.text = base64.b64encode(signature_bytes).decode()

            # KeyInfo
            key_info = etree.SubElement(signature, "{http://www.w3.org/2000/09/xmldsig#}KeyInfo")

            # X509Data
            x509_data = etree.SubElement(key_info, "{http://www.w3.org/2000/09/xmldsig#}X509Data")

            # X509Certificate
            cert_der = self.certificate.public_bytes(serialization.Encoding.DER)
            x509_cert = etree.SubElement(x509_data, "{http://www.w3.org/2000/09/xmldsig#}X509Certificate")
            x509_cert.text = base64.b64encode(cert_der).decode()

            # Agregar la firma al documento
            root.append(signature)

            # Retornar el XML firmado
            signed_xml = etree.tostring(root, encoding='unicode', method='xml')

            return signed_xml

        except Exception as e:
            print("Error al firmar XML: {}".format(str(e)))
            traceback.print_exc()
            raise

def main():
    """Función principal para ser llamada desde PHP"""
    try:
        if len(sys.argv) < 4:
            print(json.dumps({
                'success': False,
                'error': 'Uso: python firma_service.py <ruta_certificado> <password> <xml_file>'
            }))
            sys.exit(1)

        p12_path = sys.argv[1]
        password = sys.argv[2]
        xml_file = sys.argv[3]

        # Leer el XML desde el archivo
        with open(xml_file, 'r', encoding='utf-8') as f:
            xml_content = f.read()

        # Crear servicio de firma
        firma_service = FirmaElectronicaService(p12_path, password)

        # Obtener información del certificado
        cert_info = firma_service.get_certificate_info()

        # Firmar el XML
        signed_xml = firma_service.sign_xml(xml_content)

        # Retornar resultado
        result = {
            'success': True,
            'certificate_info': cert_info,
            'signed_xml': signed_xml
        }

        print(json.dumps(result))

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
