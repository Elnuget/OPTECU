#!/usr/bin/env python3
# -*- coding: utf-8 -*-

"""
Servicio de Firma Digital Electrónica para Ecuador (SRI)
Implementación en Python pa            // Calcular digest del documento (sin            # Calcular digest de SignedProperties con canonicalización C14N exclusiva
            # Usar la misma configuración de canonicalización que para el documento
            signed_props_canonical = etree.tostring(signed_properties_xml, method="c14n", exclusive=True, with_comments=False, inclusive_ns_prefixes=None)
            digest_props = hashlib.sha256(signed_props_canonical).digest()
            digest_value_props = etree.SubElement(reference_signed_props, "{http://www.w3.org/2000/09/xmldsig#}DigestValue")
            digest_value_props.text = base64.b64encode(digest_props).decode('ascii')irma)
            doc_without_signature = etree.tostring(root, method="c14n", exclusive=False)
            digest = hashlib.sha1(doc_without_signature).digest()
            digest_value = etree.SubElement(reference, "{http://www.w3.org/2000/09/xmldsig#}DigestValue")
            digest_value.text = base64.b64encode(digest).decode()perar limitaciones de OpenSSL 3.0+
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

# Register namespaces with proper prefixes for XAdES-BES
etree.register_namespace('ds', 'http://www.w3.org/2000/09/xmldsig#')
etree.register_namespace('etsi', 'http://uri.etsi.org/01903/v1.3.2#')

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
            'not_valid_before': self.certificate.not_valid_before_utc.isoformat(),
            'not_valid_after': self.certificate.not_valid_after_utc.isoformat(),
            'signature_algorithm': str(self.certificate.signature_algorithm_oid),
            'public_key_algorithm': str(self.certificate.public_key_algorithm_oid)
        }

        return cert_info

    def sign_xml(self, xml_content):
        """Firma el XML con XAdES-BES completo según estándares del SRI Ecuador"""
        try:
            # Asegurar que el contenido esté en UTF-8
            if isinstance(xml_content, str):
                xml_content_bytes = xml_content.encode('utf-8')
            else:
                xml_content_bytes = xml_content
                
            # Parsear el XML con codificación UTF-8 explícita
            parser = etree.XMLParser(encoding='utf-8')
            root = etree.fromstring(xml_content_bytes, parser)

            # Generar IDs únicos para la firma
            import random
            signature_id = f"Signature{random.randint(100000, 999999)}"
            signed_info_id = f"Signature-SignedInfo{random.randint(100000, 999999)}"
            signed_properties_id = f"{signature_id}-SignedProperties{random.randint(100000, 999999)}"
            object_id = f"{signature_id}-Object{random.randint(100000, 999999)}"
            certificate_id = f"Certificate{random.randint(100000, 999999)}"
            signature_value_id = f"SignatureValue{random.randint(100000, 999999)}"
            reference_id = f"Reference-ID-{random.randint(100000, 999999)}"
            signed_properties_ref_id = f"SignedPropertiesID{random.randint(100000, 999999)}"

            # Crear el elemento de firma XAdES-BES
            signature = etree.Element("{http://www.w3.org/2000/09/xmldsig#}Signature")
            # Los namespaces se definirán automáticamente por lxml
            signature.set("Id", signature_id)

            # SignedInfo
            signed_info = etree.SubElement(signature, "{http://www.w3.org/2000/09/xmldsig#}SignedInfo")
            signed_info.set("Id", signed_info_id)

            # CanonicalizationMethod - Usar Exclusive C14N como requiere SRI
            canonicalization = etree.SubElement(signed_info, "{http://www.w3.org/2000/09/xmldsig#}CanonicalizationMethod")
            canonicalization.set("Algorithm", "http://www.w3.org/2001/10/xml-exc-c14n#")

            # SignatureMethod
            signature_method = etree.SubElement(signed_info, "{http://www.w3.org/2000/09/xmldsig#}SignatureMethod")
            signature_method.set("Algorithm", "http://www.w3.org/2001/04/xmldsig-more#rsa-sha256")

            # Reference 1: SignedProperties (XAdES)
            reference_signed_props = etree.SubElement(signed_info, "{http://www.w3.org/2000/09/xmldsig#}Reference")
            reference_signed_props.set("Id", signed_properties_ref_id)
            reference_signed_props.set("Type", "http://uri.etsi.org/01903#SignedProperties")
            reference_signed_props.set("URI", f"#{signed_properties_id}")

            digest_method_props = etree.SubElement(reference_signed_props, "{http://www.w3.org/2000/09/xmldsig#}DigestMethod")
            digest_method_props.set("Algorithm", "http://www.w3.org/2001/04/xmlenc#sha256")

            # Reference 2: Certificate
            reference_cert = etree.SubElement(signed_info, "{http://www.w3.org/2000/09/xmldsig#}Reference")
            reference_cert.set("URI", f"#{certificate_id}")

            digest_method_cert = etree.SubElement(reference_cert, "{http://www.w3.org/2000/09/xmldsig#}DigestMethod")
            digest_method_cert.set("Algorithm", "http://www.w3.org/2001/04/xmlenc#sha256")

            # Reference 3: Documento principal
            reference_doc = etree.SubElement(signed_info, "{http://www.w3.org/2000/09/xmldsig#}Reference")
            reference_doc.set("Id", reference_id)
            reference_doc.set("URI", "#comprobante")

            # Transforms para el documento
            transforms_doc = etree.SubElement(reference_doc, "{http://www.w3.org/2000/09/xmldsig#}Transforms")
            transform_doc = etree.SubElement(transforms_doc, "{http://www.w3.org/2000/09/xmldsig#}Transform")
            transform_doc.set("Algorithm", "http://www.w3.org/2000/09/xmldsig#enveloped-signature")

            # Agregar transform de canonicalización exclusiva (requerido por SRI)
            transform_c14n = etree.SubElement(transforms_doc, "{http://www.w3.org/2000/09/xmldsig#}Transform")
            transform_c14n.set("Algorithm", "http://www.w3.org/2001/10/xml-exc-c14n#")

            # DigestMethod para el documento
            digest_method_doc = etree.SubElement(reference_doc, "{http://www.w3.org/2000/09/xmldsig#}DigestMethod")
            digest_method_doc.set("Algorithm", "http://www.w3.org/2001/04/xmlenc#sha256")

            # Crear SignedProperties para XAdES-BES
            signed_properties_xml = self._create_signed_properties(signed_properties_id, signature_id)
            
            # Calcular digest de SignedProperties con canonicalización C14N exclusiva
            # Usar la misma configuración de canonicalización que para el documento
            signed_props_canonical = etree.tostring(signed_properties_xml, method="c14n", exclusive=True, with_comments=False, inclusive_ns_prefixes=None)
            digest_props = hashlib.sha256(signed_props_canonical).digest()
            digest_value_props = etree.SubElement(reference_signed_props, "{http://www.w3.org/2000/09/xmldsig#}DigestValue")
            digest_value_props.text = base64.b64encode(digest_props).decode('ascii')

            # Crear KeyInfo y calcular digest del certificado CORRECTAMENTE
            key_info_xml = self._create_key_info(certificate_id)
            # Para el digest del certificado, usar SOLO el contenido del certificado DER
            # NO canonicalizar todo el KeyInfo, solo el certificado
            cert_der = self.certificate.public_bytes(serialization.Encoding.DER)
            digest_cert = hashlib.sha256(cert_der).digest()
            digest_value_cert = etree.SubElement(reference_cert, "{http://www.w3.org/2000/09/xmldsig#}DigestValue")
            digest_value_cert.text = base64.b64encode(digest_cert).decode('ascii')

            # Calcular digest del documento aplicando transforms correctamente
            # Primero crear una copia del documento
            root_copy = etree.fromstring(etree.tostring(root))

            # Aplicar transform enveloped-signature: remover la firma
            signature_to_remove = root_copy.find('.//{http://www.w3.org/2000/09/xmldsig#}Signature')
            if signature_to_remove is not None:
                signature_to_remove.getparent().remove(signature_to_remove)

            # Aplicar transform xml-exc-c14n: canonicalización exclusiva
            # Usar canonicalización exclusiva SIN namespaces inclusivos para mayor compatibilidad
            doc_without_signature = etree.tostring(root_copy, method="c14n", exclusive=True, with_comments=False, inclusive_ns_prefixes=None)
            digest_doc = hashlib.sha256(doc_without_signature).digest()
            digest_value_doc = etree.SubElement(reference_doc, "{http://www.w3.org/2000/09/xmldsig#}DigestValue")
            digest_value_doc.text = base64.b64encode(digest_doc).decode('ascii')

            # Firmar SignedInfo con canonicalización C14N exclusiva
            # Usar la misma configuración consistente para canonicalización
            signed_info_canonical = etree.tostring(signed_info, method="c14n", exclusive=True, with_comments=False, inclusive_ns_prefixes=None)

            # Firmar los bytes canónicos directamente (evitar doble hashing)
            signature_bytes = self.private_key.sign(
                signed_info_canonical,
                padding.PKCS1v15(),
                hashes.SHA256()
            )

            # SignatureValue - base64 limpio sin saltos extra
            signature_value = etree.SubElement(signature, "{http://www.w3.org/2000/09/xmldsig#}SignatureValue")
            signature_value.set("Id", signature_value_id)
            signature_value.text = base64.b64encode(signature_bytes).decode('ascii')

            # Agregar KeyInfo
            signature.append(key_info_xml)

            # Crear Object con QualifyingProperties (XAdES)
            ds_object = etree.SubElement(signature, "{http://www.w3.org/2000/09/xmldsig#}Object")
            ds_object.set("Id", object_id)

            qualifying_properties = etree.SubElement(ds_object, "{http://uri.etsi.org/01903/v1.3.2#}QualifyingProperties")
            qualifying_properties.set("Target", f"#{signature_id}")

            qualifying_properties.append(signed_properties_xml)

            # Agregar la firma al documento
            root.append(signature)

            # Retornar el XML firmado como bytes UTF-8 sin BOM para evitar problemas de encoding
            signed_xml_bytes = etree.tostring(root, encoding='utf-8', method='xml', pretty_print=False)
            # Convertir a string UTF-8 para consistencia con el resto del sistema
            signed_xml = signed_xml_bytes.decode('utf-8')
            return signed_xml

        except Exception as e:
            print("Error al firmar XML: {}".format(str(e)))
            traceback.print_exc()
            raise

    def _create_signed_properties(self, signed_properties_id, signature_id):
        """Crear elemento SignedProperties para XAdES-BES"""
        signed_properties = etree.Element("{http://uri.etsi.org/01903/v1.3.2#}SignedProperties")
        signed_properties.set("Id", signed_properties_id)

        signed_signature_properties = etree.SubElement(signed_properties, "{http://uri.etsi.org/01903/v1.3.2#}SignedSignatureProperties")

        # SigningTime
        signing_time = etree.SubElement(signed_signature_properties, "{http://uri.etsi.org/01903/v1.3.2#}SigningTime")
        from datetime import datetime, timezone, timedelta
        # Hora de Ecuador (UTC-5)
        ecuador_tz = timezone(timedelta(hours=-5))
        now = datetime.now(ecuador_tz)
        signing_time.text = now.strftime('%Y-%m-%dT%H:%M:%S%z')[:-2] + ':' + now.strftime('%Y-%m-%dT%H:%M:%S%z')[-2:]

        # SigningCertificate
        signing_certificate = etree.SubElement(signed_signature_properties, "{http://uri.etsi.org/01903/v1.3.2#}SigningCertificate")
        cert = etree.SubElement(signing_certificate, "{http://uri.etsi.org/01903/v1.3.2#}Cert")

        # CertDigest
        cert_digest = etree.SubElement(cert, "{http://uri.etsi.org/01903/v1.3.2#}CertDigest")
        digest_method = etree.SubElement(cert_digest, "{http://www.w3.org/2000/09/xmldsig#}DigestMethod")
        digest_method.set("Algorithm", "http://www.w3.org/2001/04/xmlenc#sha256")

        # Calcular digest del certificado
        cert_der = self.certificate.public_bytes(serialization.Encoding.DER)
        cert_digest_value = hashlib.sha256(cert_der).digest()
        digest_value = etree.SubElement(cert_digest, "{http://www.w3.org/2000/09/xmldsig#}DigestValue")
        digest_value.text = base64.b64encode(cert_digest_value).decode('ascii')

        # IssuerSerial
        issuer_serial = etree.SubElement(cert, "{http://uri.etsi.org/01903/v1.3.2#}IssuerSerial")
        
        # X509IssuerName
        issuer_name = etree.SubElement(issuer_serial, "{http://www.w3.org/2000/09/xmldsig#}X509IssuerName")
        issuer_name.text = self.certificate.issuer.rfc4514_string()
        
        # X509SerialNumber
        serial_number = etree.SubElement(issuer_serial, "{http://www.w3.org/2000/09/xmldsig#}X509SerialNumber")
        serial_number.text = str(self.certificate.serial_number)

        return signed_properties

    def _create_key_info(self, certificate_id):
        """Crear elemento KeyInfo sin KeyValue (solo X.509 certificado para cumplir con SRI)"""
        key_info = etree.Element("{http://www.w3.org/2000/09/xmldsig#}KeyInfo")
        key_info.set("Id", certificate_id)

        x509_data = etree.SubElement(key_info, "{http://www.w3.org/2000/09/xmldsig#}X509Data")

        # X509Certificate - Solo certificado, sin KeyValue para cumplir con esquema SRI
        cert_der = self.certificate.public_bytes(serialization.Encoding.DER)
        x509_cert = etree.SubElement(x509_data, "{http://www.w3.org/2000/09/xmldsig#}X509Certificate")
        x509_cert.text = base64.b64encode(cert_der).decode('ascii')

        # NOTA: Eliminado KeyValue para cumplir con el esquema XML del SRI
        # El SRI requiere solo elementos X.509 en KeyInfo, no ds:KeyValue

        return key_info

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

        # Leer el XML desde el archivo con codificación UTF-8 explícita
        with open(xml_file, 'r', encoding='utf-8-sig') as f:  # utf-8-sig maneja BOM
            xml_content = f.read()

        # Asegurar que el contenido esté limpio (sin BOM)
        if xml_content.startswith('\ufeff'):
            xml_content = xml_content[1:]

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
