/**
 * Librería para firma digital XML usando certificados P12 en JavaScript
 * Implementación XAdES-BES para SRI Ecuador
 * Requiere forge.js para manejo de certificados
 */

// Incluir forge.js si no está cargado
if (typeof forge === 'undefined') {
    console.error('forge.js no está cargado. Necesario para la firma digital.');
}

class FirmaDigitalXAdES {
    constructor() {
        this.certificado = null;
        this.clavePrivada = null;
    }

    /**
     * Cargar certificado P12 desde base64
     */
    async cargarCertificadoP12(certificadoP12Base64, password) {
        try {
            console.log('Cargando certificado P12 para XAdES-BES...');
            
            // Validar parámetros
            if (!certificadoP12Base64) {
                throw new Error('Certificado P12 no proporcionado (certificadoP12Base64 es undefined o vacío)');
            }
            
            if (!password) {
                throw new Error('Contraseña no proporcionada');
            }
            
            console.log('Parámetros válidos - certificado size:', certificadoP12Base64.length, 'password length:', password.length);
            
            // Decodificar base64
            const certificadoBinario = forge.util.decode64(certificadoP12Base64);
            
            // Crear ASN.1 desde los datos binarios
            const asn1 = forge.asn1.fromDer(certificadoBinario);
            
            // Leer el P12
            const p12 = forge.pkcs12.pkcs12FromAsn1(asn1, password);
            
            // Extraer certificado y clave privada
            const bags = p12.getBags({bagType: forge.pki.oids.certBag});
            if (bags[forge.pki.oids.certBag] && bags[forge.pki.oids.certBag].length > 0) {
                this.certificado = bags[forge.pki.oids.certBag][0].cert;
            }
            
            const keyBags = p12.getBags({bagType: forge.pki.oids.pkcs8ShroudedKeyBag});
            if (keyBags[forge.pki.oids.pkcs8ShroudedKeyBag] && keyBags[forge.pki.oids.pkcs8ShroudedKeyBag].length > 0) {
                this.clavePrivada = keyBags[forge.pki.oids.pkcs8ShroudedKeyBag][0].key;
            } else {
                // Intentar con keyBag normal
                const normalKeyBags = p12.getBags({bagType: forge.pki.oids.keyBag});
                if (normalKeyBags[forge.pki.oids.keyBag] && normalKeyBags[forge.pki.oids.keyBag].length > 0) {
                    this.clavePrivada = normalKeyBags[forge.pki.oids.keyBag][0].key;
                }
            }
            
            if (!this.certificado || !this.clavePrivada) {
                throw new Error('No se pudo extraer certificado o clave privada del archivo P12');
            }
            
            console.log('Certificado P12 cargado exitosamente para XAdES-BES');
            console.log('Certificado válido desde:', this.certificado.validity.notBefore);
            console.log('Certificado válido hasta:', this.certificado.validity.notAfter);
            
            return true;
            
        } catch (error) {
            console.error('Error al cargar certificado P12:', error);
            throw new Error('Error al cargar certificado P12: ' + error.message);
        }
    }

    /**
     * Firmar XML para SRI Ecuador con formato XAdES-BES
     */
    async firmarXML(xmlContent) {
        try {
            if (!this.certificado || !this.clavePrivada) {
                throw new Error('Certificado no cargado');
            }

            console.log('Iniciando firma XAdES-BES para SRI Ecuador...');
            
            // Parsear XML
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(xmlContent, 'text/xml');
            
            if (xmlDoc.getElementsByTagName('parsererror').length > 0) {
                throw new Error('XML inválido');
            }
            
            const rootElement = xmlDoc.documentElement;
            
            // Generar IDs únicos
            const signatureId = 'Signature' + Math.floor(Math.random() * 1000000);
            const signedInfoId = 'Signature-SignedInfo' + Math.floor(Math.random() * 1000000);
            const signedPropsId = signatureId + '-SignedProperties' + Math.floor(Math.random() * 1000000);
            const objectId = signatureId + '-Object' + Math.floor(Math.random() * 1000000);
            const certificateId = 'Certificate' + Math.floor(Math.random() * 1000000);
            const signatureValueId = 'SignatureValue' + Math.floor(Math.random() * 1000000);
            const signedPropsRefId = 'SignedPropertiesID' + Math.floor(Math.random() * 1000000);
            const refId = 'Reference-ID-' + Math.floor(Math.random() * 1000000);
            
            // Crear elemento Signature con namespaces exactos del SRI Ecuador
            const signatureElement = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Signature');
            signatureElement.setAttribute('xmlns:ds', 'http://www.w3.org/2000/09/xmldsig#');
            signatureElement.setAttribute('xmlns:etsi', 'http://uri.etsi.org/01903/v1.3.2#');
            signatureElement.setAttribute('Id', signatureId);
            
            console.log('=== DEBUGGING NAMESPACES Y ESTRUCTURA ===');
            console.log('SignatureId generado:', signatureId);
            console.log('Namespace ds:', 'http://www.w3.org/2000/09/xmldsig#');
            console.log('Namespace etsi:', 'http://uri.etsi.org/01903/v1.3.2#');
            
            // SignedInfos
            const signedInfoElement = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignedInfo');
            signedInfoElement.setAttribute('Id', signedInfoId);
            
            // CanonicalizationMethod
            const canonicalizationMethod = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:CanonicalizationMethod');
            canonicalizationMethod.setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            signedInfoElement.appendChild(canonicalizationMethod);
            
            // SignatureMethod - RSA-SHA1 requerido por SRI
            const signatureMethod = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignatureMethod');
            signatureMethod.setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
            signedInfoElement.appendChild(signatureMethod);
            
            // Reference a SignedProperties PRIMERO (como en el ejemplo válido)
            const xadesReference = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Reference');
            xadesReference.setAttribute('Id', signedPropsRefId);
            xadesReference.setAttribute('Type', 'http://uri.etsi.org/01903#SignedProperties');
            xadesReference.setAttribute('URI', '#' + signedPropsId);
            
            const xadesDigestMethod = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestMethod');
            xadesDigestMethod.setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            xadesReference.appendChild(xadesDigestMethod);
            
            // Placeholder para el digest - se calculará después
            const xadesDigestValue = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestValue');
            xadesReference.appendChild(xadesDigestValue);
            
            signedInfoElement.appendChild(xadesReference);
            
            // Reference al certificado
            const certReference = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Reference');
            certReference.setAttribute('URI', '#' + certificateId);
            
            const certDigestMethod = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestMethod');
            certDigestMethod.setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            certReference.appendChild(certDigestMethod);
            
            // Placeholder para el digest del certificado
            const certDigestValue = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestValue');
            certReference.appendChild(certDigestValue);
            
            signedInfoElement.appendChild(certReference);
            
            // Reference al documento completo
            const docReference = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Reference');
            docReference.setAttribute('Id', refId);
            docReference.setAttribute('URI', '#comprobante');
            
            // Transforms para enveloped signature
            const transforms = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Transforms');
            const transform = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Transform');
            transform.setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
            transforms.appendChild(transform);
            docReference.appendChild(transforms);
            
            // DigestMethod - SHA1 requerido por SRI
            const digestMethod = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestMethod');
            digestMethod.setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            docReference.appendChild(digestMethod);
            
            // Calcular digest del documento sin la firma
            const serializer = new XMLSerializer();
            const documentToDigest = serializer.serializeToString(rootElement);
            const digest = forge.util.encode64(forge.md.sha1.create().update(documentToDigest, 'utf8').digest().getBytes());
            
            const digestValue = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestValue');
            digestValue.textContent = digest;
            docReference.appendChild(digestValue);
            
            signedInfoElement.appendChild(docReference);
            signatureElement.appendChild(signedInfoElement);
            
            // SignatureValue (se rellenará después)
            const signatureValue = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignatureValue');
            signatureValue.setAttribute('Id', signatureValueId);
            signatureElement.appendChild(signatureValue);
            
            // KeyInfo con el certificado
            const keyInfo = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:KeyInfo');
            keyInfo.setAttribute('Id', certificateId);
            
            const x509Data = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:X509Data');
            const x509Certificate = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:X509Certificate');
            
            // Convertir certificado a base64
            const certificadoDER = forge.asn1.toDer(forge.pki.certificateToAsn1(this.certificado)).getBytes();
            const certificadoBase64 = forge.util.encode64(certificadoDER);
            x509Certificate.textContent = certificadoBase64;
            
            x509Data.appendChild(x509Certificate);
            
            // Agregar KeyValue (clave pública) como en el ejemplo válido
            const keyValue = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:KeyValue');
            const rsaKeyValue = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:RSAKeyValue');
            
            const modulus = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Modulus');
            const exponent = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Exponent');
            
            // Extraer modulus y exponente de la clave pública
            const publicKey = this.certificado.publicKey;
            
            // Convertir BigInteger a string hexadecimal y asegurar longitud par
            let modulusHex = publicKey.n.toString(16);
            if (modulusHex.length % 2 !== 0) {
                modulusHex = '0' + modulusHex;
            }
            
            let exponentHex = publicKey.e.toString(16);
            if (exponentHex.length % 2 !== 0) {
                exponentHex = '0' + exponentHex;
            }
            
            // Usar el método correcto de Forge para convertir hex a bytes
            const modulusBytes = forge.util.hexToBytes(modulusHex);
            const exponentBytes = forge.util.hexToBytes(exponentHex);
            
            modulus.textContent = forge.util.encode64(modulusBytes);
            exponent.textContent = forge.util.encode64(exponentBytes);
            
            rsaKeyValue.appendChild(modulus);
            rsaKeyValue.appendChild(exponent);
            keyValue.appendChild(rsaKeyValue);
            
            keyInfo.appendChild(x509Data);
            keyInfo.appendChild(keyValue);
            signatureElement.appendChild(keyInfo);
            
            // Object con QualifyingProperties para XAdES-BES
            const objectElement = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Object');
            objectElement.setAttribute('Id', objectId);
            
            const qualifyingProperties = xmlDoc.createElementNS('http://uri.etsi.org/01903/v1.3.2#', 'etsi:QualifyingProperties');
            qualifyingProperties.setAttribute('Target', '#' + signatureId);
            
            // SignedProperties
            const signedProperties = xmlDoc.createElementNS('http://uri.etsi.org/01903/v1.3.2#', 'etsi:SignedProperties');
            signedProperties.setAttribute('Id', signedPropsId);
            
            // SignedSignatureProperties
            const signedSignatureProperties = xmlDoc.createElementNS('http://uri.etsi.org/01903/v1.3.2#', 'etsi:SignedSignatureProperties');
            
            // SigningTime con formato ISO 8601 EXACTO para SRI Ecuador
            const signingTime = xmlDoc.createElementNS('http://uri.etsi.org/01903/v1.3.2#', 'etsi:SigningTime');
            const now = new Date();
            // Ecuador está en UTC-5 (zona horaria fija)
            const ecuadorTime = new Date(now.getTime() - (5 * 60 * 60 * 1000));
            
            // Formato EXACTO del SRI: 2025-08-26T14:30:45-05:00
            const isoTime = ecuadorTime.getUTCFullYear() + '-' + 
                           String(ecuadorTime.getUTCMonth() + 1).padStart(2, '0') + '-' + 
                           String(ecuadorTime.getUTCDate()).padStart(2, '0') + 'T' + 
                           String(ecuadorTime.getUTCHours()).padStart(2, '0') + ':' + 
                           String(ecuadorTime.getUTCMinutes()).padStart(2, '0') + ':' + 
                           String(ecuadorTime.getUTCSeconds()).padStart(2, '0') + 
                           '-05:00'; // Zona horaria fija de Ecuador
            
            signingTime.textContent = isoTime;
            
            console.log('=== DEBUGGING SIGNING TIME ===');
            console.log('Fecha/hora actual:', now.toISOString());
            console.log('Fecha/hora Ecuador UTC:', ecuadorTime.toISOString());
            console.log('SigningTime formato SRI:', isoTime);
            console.log('=== FIN DEBUG SIGNING TIME ===');
            
            signedSignatureProperties.appendChild(signingTime);
            
            // SigningCertificate
            const signingCertificate = xmlDoc.createElementNS('http://uri.etsi.org/01903/v1.3.2#', 'etsi:SigningCertificate');
            const cert = xmlDoc.createElementNS('http://uri.etsi.org/01903/v1.3.2#', 'etsi:Cert');
            
            // CertDigest
            const certDigest = xmlDoc.createElementNS('http://uri.etsi.org/01903/v1.3.2#', 'etsi:CertDigest');
            const certDigestMethodElement = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestMethod');
            certDigestMethodElement.setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            certDigest.appendChild(certDigestMethodElement);
            
            // Calcular hash del certificado
            const certHash = forge.util.encode64(forge.md.sha1.create().update(certificadoDER).digest().getBytes());
            
            const certDigestValueElement = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestValue');
            certDigestValueElement.textContent = certHash;
            certDigest.appendChild(certDigestValueElement);
            cert.appendChild(certDigest);
            
            // IssuerSerial
            const issuerSerial = xmlDoc.createElementNS('http://uri.etsi.org/01903/v1.3.2#', 'etsi:IssuerSerial');
            const x509IssuerName = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:X509IssuerName');
            
            // Construir el IssuerName exacto según formato SRI
            let issuerName = '';
            const issuerFields = this.certificado.issuer.attributes;
            
            console.log('=== DEBUGGING ISSUER SERIAL ===');
            console.log('Campos del issuer disponibles:', issuerFields.map(f => ({
                shortName: f.shortName,
                name: f.name,
                value: f.value
            })));
            
            // Orden específico del SRI Ecuador (basado en ejemplos válidos)
            const cnField = issuerFields.find(f => f.shortName === 'CN');
            const cField = issuerFields.find(f => f.shortName === 'C');
            const lField = issuerFields.find(f => f.shortName === 'L');
            const oField = issuerFields.find(f => f.shortName === 'O');
            const ouField = issuerFields.find(f => f.shortName === 'OU');
            
            // Construir DN en orden estricto del SRI
            const parts = [];
            if (cnField) parts.push('CN=' + cnField.value);
            if (cField) parts.push('C=' + cField.value);
            if (lField) parts.push('L=' + lField.value);
            if (oField) parts.push('O=' + oField.value);
            if (ouField) parts.push('OU=' + ouField.value);
            
            issuerName = parts.join(',');
            
            console.log('IssuerName construido:', issuerName);
            console.log('Serial Number certificado:', this.certificado.serialNumber);
            console.log('=== FIN DEBUG ISSUER SERIAL ===');
            
            x509IssuerName.textContent = issuerName;
            issuerSerial.appendChild(x509IssuerName);
            
            const x509SerialNumber = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:X509SerialNumber');
            x509SerialNumber.textContent = this.certificado.serialNumber;
            issuerSerial.appendChild(x509SerialNumber);
            cert.appendChild(issuerSerial);
            
            signingCertificate.appendChild(cert);
            signedSignatureProperties.appendChild(signingCertificate);
            
            signedProperties.appendChild(signedSignatureProperties);
            qualifyingProperties.appendChild(signedProperties);
            objectElement.appendChild(qualifyingProperties);
            signatureElement.appendChild(objectElement);
            
            // Calcular digest de SignedProperties
            const signedPropsXML = serializer.serializeToString(signedProperties);
            const signedPropsDigest = forge.util.encode64(forge.md.sha1.create().update(signedPropsXML, 'utf8').digest().getBytes());
            xadesDigestValue.textContent = signedPropsDigest;
            
            // Calcular digest del KeyInfo para la referencia al certificado
            const keyInfoXML = serializer.serializeToString(keyInfo);
            const keyInfoDigest = forge.util.encode64(forge.md.sha1.create().update(keyInfoXML, 'utf8').digest().getBytes());
            certDigestValue.textContent = keyInfoDigest;
            
            // Crear la firma del SignedInfo
            const signedInfoXML = serializer.serializeToString(signedInfoElement);
            const md = forge.md.sha1.create();
            md.update(signedInfoXML, 'utf8');
            const signature = this.clavePrivada.sign(md);
            const signatureBase64 = forge.util.encode64(signature);
            
            // Formatear SignatureValue con saltos de línea (como en el ejemplo)
            let formattedSignature = '';
            for (let i = 0; i < signatureBase64.length; i += 76) {
                if (i > 0) formattedSignature += ' ';
                formattedSignature += signatureBase64.substr(i, 76);
            }
            
            signatureValue.textContent = ' ' + formattedSignature + ' ';
            
            // Agregar la firma al final del documento raíz
            rootElement.appendChild(signatureElement);
            
            // Serializar el XML firmado completo
            const xmlFirmado = serializer.serializeToString(xmlDoc);
            
            console.log('=== ANÁLISIS FINAL XML FIRMADO XAdES-BES ===');
            console.log('XML Size:', xmlFirmado.length);
            console.log('Contiene <ds:Signature:', xmlFirmado.includes('<ds:Signature'));
            console.log('Contiene <etsi:QualifyingProperties:', xmlFirmado.includes('<etsi:QualifyingProperties'));
            console.log('Contiene <etsi:SignedProperties:', xmlFirmado.includes('<etsi:SignedProperties'));
            console.log('Contiene <etsi:SigningTime:', xmlFirmado.includes('<etsi:SigningTime'));
            console.log('Contiene <etsi:SigningCertificate:', xmlFirmado.includes('<etsi:SigningCertificate'));
            console.log('Contiene <ds:KeyValue:', xmlFirmado.includes('<ds:KeyValue'));
            
            // Extraer secciones críticas para debugging
            const etsiStart = xmlFirmado.indexOf('<etsi:QualifyingProperties');
            const etsiEnd = xmlFirmado.indexOf('</etsi:QualifyingProperties>') + 29;
            if (etsiStart !== -1 && etsiEnd !== -1) {
                const etsiSection = xmlFirmado.substring(etsiStart, etsiEnd);
                console.log('=== SECCIÓN XAdES (CRÍTICA PARA SRI) ===');
                console.log(etsiSection);
                console.log('=== FIN SECCIÓN XAdES ===');
            }
            
            // Extraer SigningTime específico
            const signingTimeMatch = xmlFirmado.match(/<etsi:SigningTime[^>]*>([^<]+)<\/etsi:SigningTime>/);
            if (signingTimeMatch) {
                console.log('SigningTime extraído:', signingTimeMatch[1]);
            }
            
            // Extraer IssuerSerial específico
            const issuerNameMatch = xmlFirmado.match(/<ds:X509IssuerName[^>]*>([^<]+)<\/ds:X509IssuerName>/);
            const serialNumberMatch = xmlFirmado.match(/<ds:X509SerialNumber[^>]*>([^<]+)<\/ds:X509SerialNumber>/);
            if (issuerNameMatch && serialNumberMatch) {
                console.log('X509IssuerName extraído:', issuerNameMatch[1]);
                console.log('X509SerialNumber extraído:', serialNumberMatch[1]);
            }
            
            // Verificar estructura de Referencias
            const referencesCount = (xmlFirmado.match(/<ds:Reference/g) || []).length;
            console.log('Número de References:', referencesCount);
            console.log('=== ESTRUCTURA REFERENCES ===');
            if (xmlFirmado.includes('Type="http://uri.etsi.org/01903#SignedProperties"')) {
                console.log('✓ Reference a SignedProperties presente');
            } else {
                console.log('✗ Reference a SignedProperties FALTANTE');
            }
            if (xmlFirmado.includes('URI="#comprobante"')) {
                console.log('✓ Reference al documento presente');
            } else {
                console.log('✗ Reference al documento FALTANTE');
            }
            
            console.log('XML Preview (primeros 2000 caracteres):');
            console.log(xmlFirmado.substring(0, 2000));
            console.log('XML Final (últimos 1500 caracteres):');
            console.log(xmlFirmado.substring(xmlFirmado.length - 1500));
            console.log('=== FIN ANÁLISIS FINAL XML XAdES-BES ===');
            
            console.log('XML firmado exitosamente con XAdES-BES (formato SRI) para Ecuador');
            return xmlFirmado;
            
        } catch (error) {
            console.error('Error al firmar XML con XAdES-BES:', error);
            throw new Error('Error al firmar XML con XAdES-BES: ' + error.message);
        }
    }
}

// Función global para procesar firma con certificado P12 usando XAdES-BES
window.procesarFirmaConP12XAdES = async function(facturaId, password) {
    try {
        console.log('=== INICIO PROCESO FIRMA DIGITAL XAdES-BES ===');
        console.log('Factura ID:', facturaId);
        
        // Actualizar progreso
        actualizarProgreso(0, 'Preparando datos para firma XAdES-BES...');
        
        // Obtener datos del servidor
        console.log('Solicitando datos al servidor...');
        const response = await fetch(`/facturas/${facturaId}/preparar-xml-firma`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                password: password
            })
        });

        if (!response.ok) {
            const errorText = await response.text();
            throw new Error(`Error del servidor: ${response.status} - ${errorText}`);
        }

        const data = await response.json();
        console.log('Datos recibidos del servidor:', data);

        if (!data.success) {
            throw new Error(data.message || 'Error al preparar datos');
        }
        
        // Validar que los datos necesarios estén presentes
        if (!data.data) {
            throw new Error('El servidor no proporcionó datos válidos');
        }
        
        if (!data.data.certificado_p12_base64) {
            throw new Error('El servidor no proporcionó el certificado P12');
        }
        
        if (!data.data.xml_content) {
            throw new Error('El servidor no proporcionó el XML sin firmar');
        }
        
        console.log('Datos validados - certificado_p12_base64 size:', data.data.certificado_p12_base64.length, 'xml_content size:', data.data.xml_content.length);

        actualizarProgreso(25, 'Cargando certificado digital...');
        
        // Crear instancia de firma XAdES
        const firmaXAdES = new FirmaDigitalXAdES();
        
        // Cargar certificado P12
        await firmaXAdES.cargarCertificadoP12(data.data.certificado_p12_base64, password);
        
        actualizarProgreso(50, 'Generando firma digital XAdES-BES...');
        
        // Firmar XML con XAdES-BES
        const xmlFirmado = await firmaXAdES.firmarXML(data.data.xml_content);
        
        console.log('XML firmado exitosamente, tamaño:', xmlFirmado.length);
        
        actualizarProgreso(75, 'Enviando documento firmado...');
        
        console.log('Enviando XML firmado al servidor...');
        
        // Enviar XML firmado al servidor
        const envioResponse = await fetch(`/facturas/${facturaId}/procesar-xml-firmado-js`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                xml_firmado: xmlFirmado
            })
        });

        console.log('Respuesta envío:', envioResponse.status, envioResponse.statusText);
        
        if (!envioResponse.ok) {
            const errorData = await envioResponse.json();
            throw new Error(errorData.message || 'Error al procesar XML firmado');
        }

        const resultado = await envioResponse.json();
        console.log('Respuesta del servidor:', resultado);
        
        actualizarProgreso(100, 'Proceso completado exitosamente');
        
        return resultado;
        
    } catch (error) {
        console.error(' Error al procesar XML firmado:', error);
        console.error(' === ERROR EN PROCESO FIRMA DIGITAL XAdES-BES ===');
        console.error(' Error completo:', error);
        console.error(' Stack trace:', error.stack);
        
        actualizarProgreso(0, 'Error en firma digital: ' + error.message);
        throw error;
    }
};

// Mantener compatibilidad con función anterior
window.procesarFirmaConP12 = window.procesarFirmaConP12XAdES;
