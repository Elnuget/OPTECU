/**
 * Librería para firma digital XML usando certificados P12 en JavaScript
 * Requiere forge.js para manejo de certificados
 */

// Incluir forge.js si no está cargado
if (typeof forge === 'undefined') {
    console.error('forge.js no está cargado. Necesario para la firma digital.');
}

class FirmaDigitalJS {
    constructor() {
        this.certificado = null;
        this.clavePrivada = null;
    }

    /**
     * Cargar certificado P12 desde base64
     */
    async cargarCertificadoP12(certificadoP12Base64, password) {
        try {
            console.log('Cargando certificado P12...');
            
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
                throw new Error('No se pudo extraer el certificado o la clave privada del archivo P12');
            }
            
            console.log('Certificado P12 cargado exitosamente');
            return true;
            
        } catch (error) {
            console.error('Error al cargar certificado P12:', error);
            throw new Error('Error al cargar el certificado P12: ' + error.message);
        }
    }

    /**
     * Firmar XML
     */
    async firmarXML(xmlContent) {
        try {
            console.log('Iniciando firma XML...');
            
            if (!this.certificado || !this.clavePrivada) {
                throw new Error('Certificado no cargado. Use cargarCertificadoP12() primero.');
            }
            
            // Parsear XML
            const parser = new DOMParser();
            const xmlDoc = parser.parseFromString(xmlContent, 'text/xml');
            
            if (xmlDoc.getElementsByTagName('parsererror').length > 0) {
                throw new Error('XML inválido');
            }
            
            const rootElement = xmlDoc.documentElement;
            
            // Agregar Id al elemento raíz si no existe
            if (!rootElement.hasAttribute('Id')) {
                rootElement.setAttribute('Id', 'comprobante');
            }
            
            // Crear elemento Signature
            const signatureElement = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Signature');
            signatureElement.setAttribute('Id', 'Signature' + Date.now());
            
            // Crear SignedInfo
            const signedInfoElement = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignedInfo');
            
            // CanonicalizationMethod
            const canonicalizationMethod = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:CanonicalizationMethod');
            canonicalizationMethod.setAttribute('Algorithm', 'http://www.w3.org/TR/2001/REC-xml-c14n-20010315');
            signedInfoElement.appendChild(canonicalizationMethod);
            
            // SignatureMethod
            const signatureMethod = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignatureMethod');
            signatureMethod.setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#rsa-sha1');
            signedInfoElement.appendChild(signatureMethod);
            
            // Reference
            const reference = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Reference');
            reference.setAttribute('URI', '#comprobante');
            
            // Transforms
            const transforms = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Transforms');
            const transform = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:Transform');
            transform.setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#enveloped-signature');
            transforms.appendChild(transform);
            reference.appendChild(transforms);
            
            // DigestMethod
            const digestMethod = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestMethod');
            digestMethod.setAttribute('Algorithm', 'http://www.w3.org/2000/09/xmldsig#sha1');
            reference.appendChild(digestMethod);
            
            // Calcular digest del documento
            const serializer = new XMLSerializer();
            const documentToDigest = serializer.serializeToString(rootElement);
            const digest = forge.util.encode64(forge.md.sha1.create().update(documentToDigest, 'utf8').digest().getBytes());
            
            const digestValue = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:DigestValue');
            digestValue.textContent = digest;
            reference.appendChild(digestValue);
            
            signedInfoElement.appendChild(reference);
            signatureElement.appendChild(signedInfoElement);
            
            // Crear la firma del SignedInfo
            const signedInfoXML = serializer.serializeToString(signedInfoElement);
            const md = forge.md.sha1.create();
            md.update(signedInfoXML, 'utf8');
            const signature = this.clavePrivada.sign(md);
            const signatureBase64 = forge.util.encode64(signature);
            
            // SignatureValue
            const signatureValue = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:SignatureValue');
            signatureValue.textContent = signatureBase64;
            signatureElement.appendChild(signatureValue);
            
            // KeyInfo
            const keyInfo = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:KeyInfo');
            const x509Data = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:X509Data');
            const x509Certificate = xmlDoc.createElementNS('http://www.w3.org/2000/09/xmldsig#', 'ds:X509Certificate');
            
            // Convertir certificado a base64
            const certificadoDER = forge.asn1.toDer(forge.pki.certificateToAsn1(this.certificado)).getBytes();
            const certificadoBase64 = forge.util.encode64(certificadoDER);
            x509Certificate.textContent = certificadoBase64;
            
            x509Data.appendChild(x509Certificate);
            keyInfo.appendChild(x509Data);
            signatureElement.appendChild(keyInfo);
            
            // Agregar la firma al final del documento
            rootElement.appendChild(signatureElement);
            
            // Serializar el XML firmado
            const xmlFirmado = serializer.serializeToString(xmlDoc);
            
            console.log('XML firmado exitosamente');
            return xmlFirmado;
            
        } catch (error) {
            console.error('Error al firmar XML:', error);
            throw new Error('Error al firmar XML: ' + error.message);
        }
    }
}

// Función global para procesar firma con certificado P12
window.procesarFirmaConP12 = async function(facturaId, password) {
    try {
        console.log('=== INICIO PROCESO FIRMA DIGITAL JS ===');
        console.log('Factura ID:', facturaId);
        
        // Actualizar progreso
        actualizarProgreso(0, 'Preparando datos para firma...');
        
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
                password_certificado: password
            })
        });
        
        console.log('Respuesta del servidor:', response.status, response.statusText);
        
        if (!response.ok) {
            const errorData = await response.json();
            console.error('Error del servidor:', errorData);
            throw new Error(errorData.message || 'Error al preparar datos para firma');
        }
        
        const datos = await response.json();
        console.log('Datos recibidos:', {
            success: datos.success,
            xml_size: datos.data?.xml_content?.length || 0,
            cert_size: datos.data?.certificado_p12_base64?.length || 0
        });
        
        if (!datos.success) {
            throw new Error(datos.message || 'Error al obtener datos para firma');
        }
        
        actualizarProgreso(30, 'Cargando certificado digital...');
        
        // Crear instancia de firma digital
        const firmaDigital = new FirmaDigitalJS();
        
        // Cargar certificado P12
        console.log('Cargando certificado P12...');
        await firmaDigital.cargarCertificadoP12(datos.data.certificado_p12_base64, password);
        console.log('Certificado P12 cargado exitosamente');
        
        actualizarProgreso(60, 'Firmando XML digitalmente...');
        
        // Firmar XML
        console.log('Iniciando firma XML...');
        const xmlFirmado = await firmaDigital.firmarXML(datos.data.xml_content);
        console.log('XML firmado exitosamente, tamaño:', xmlFirmado.length);
        
        actualizarProgreso(80, 'Enviando XML firmado al servidor...');
        
        // Enviar XML firmado de vuelta al servidor
        console.log('Enviando XML firmado al servidor...');
        const responseEnvio = await fetch(`/facturas/${facturaId}/procesar-xml-firmado-js`, {
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
        
        console.log('Respuesta envío:', responseEnvio.status, responseEnvio.statusText);
        
        if (!responseEnvio.ok) {
            const errorData = await responseEnvio.json();
            console.error('Error al procesar XML firmado:', errorData);
            throw new Error(errorData.message || 'Error al procesar XML firmado');
        }
        
        const resultado = await responseEnvio.json();
        console.log('Resultado final:', resultado);
        
        actualizarProgreso(100, 'Proceso completado exitosamente');
        console.log('=== FIN PROCESO FIRMA DIGITAL JS ===');
        
        return resultado;
        
    } catch (error) {
        console.error('=== ERROR EN PROCESO FIRMA DIGITAL JS ===');
        console.error('Error completo:', error);
        console.error('Stack trace:', error.stack);
        throw error;
    }
};

// Función auxiliar para actualizar progreso (debe existir en el contexto)
function actualizarProgreso(porcentaje, mensaje) {
    const progressBar = document.querySelector('.progress-bar');
    const progressText = document.querySelector('.progress-text');
    const estadoProceso = document.getElementById('estado_proceso');
    
    if (progressBar) progressBar.style.width = porcentaje + '%';
    if (progressText) progressText.textContent = porcentaje + '%';
    if (estadoProceso) estadoProceso.textContent = mensaje;
    
    // Solo log importantes, no de progreso
    if (porcentaje === 100 || porcentaje === 0) {
        console.log(`Proceso: ${porcentaje === 100 ? 'Completado' : 'Iniciado'} - ${mensaje}`);
    }
}
