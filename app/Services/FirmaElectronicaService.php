<?php

namespace App\Services;

use RobRichards\XMLSecLibs\XMLSecurityDSig;
use RobRichards\XMLSecLibs\XMLSecurityKey;
use DOMDocument;
use Exception;
use Log;

class FirmaElectronicaService
{
    private $certificadoPath;
    private $claveCertificado;
    private $certificadoData;

    public function __construct($p12Path, $password)
    {
        \Log::info('FirmaElectronicaService: Iniciando servicio híbrido', [
            'ruta' => $p12Path,
            'archivo_existe' => file_exists($p12Path),
            'tamano_archivo' => file_exists($p12Path) ? filesize($p12Path) : 'N/A',
            'password_length' => strlen($password)
        ]);

        if (!file_exists($p12Path)) {
            throw new Exception("No se encontró el archivo del certificado en la ruta: $p12Path");
        }

        // Guardar la ruta y contraseña para usar en los métodos específicos
        $this->certificadoPath = $p12Path;
        $this->claveCertificado = $password;

        // No cargar el certificado aquí - se hará en cada método según sea necesario
        \Log::info('FirmaElectronicaService: Servicio híbrido inicializado correctamente');
    }

    /**
     * Configura OpenSSL para soportar certificados legacy (OpenSSL 3.0+)
     * Esto es necesario para certificados P12 antiguos que usan algoritmos MD5/SHA1
     */
    private function configurarOpenSSLParaLegacy()
    {
        // Ruta al archivo de configuración legacy
        $configPath = __DIR__ . '/../../openssl_legacy.cnf';

        if (!file_exists($configPath)) {
            // Si no existe el archivo personalizado, intentar usar el de XAMPP
            $xamppConfig = 'C:\xampp\apache\conf\openssl.cnf';
            if (file_exists($xamppConfig)) {
                $configPath = $xamppConfig;
            } else {
                \Log::warning('FirmaElectronicaService: No se encontró archivo de configuración OpenSSL');
                return;
            }
        }

        // Establecer variable de entorno para que OpenSSL use esta configuración
        putenv("OPENSSL_CONF=$configPath");

        \Log::info('FirmaElectronicaService: Configuración OpenSSL legacy aplicada', [
            'config_file' => $configPath
        ]);
    }

    /**
     * Intenta parsear el certificado P12 usando métodos alternativos
     * para compatibilidad con OpenSSL 3.0+
     */
    private function intentarParseoAlternativo($p12Content, $password)
    {
        \Log::info('FirmaElectronicaService: Intentando métodos alternativos de parseo');

        // Método 1: Usar openssl.cnf de sistema con configuración forzada
        putenv("OPENSSL_CONF=C:\xampp\apache\conf\openssl.cnf");
        $certData = [];
        if (openssl_pkcs12_read($p12Content, $certData, $password)) {
            $this->certificadoData = $certData;
            \Log::info('FirmaElectronicaService: Método alternativo 1 exitoso');
            return true;
        }

        // Método 2: Intentar sin configuración específica
        putenv("OPENSSL_CONF=");
        $certData = [];
        if (openssl_pkcs12_read($p12Content, $certData, $password)) {
            $this->certificadoData = $certData;
            \Log::info('FirmaElectronicaService: Método alternativo 2 exitoso');
            return true;
        }

        // Método 3: Intentar con variaciones de contraseña
        $variations = [
            $password . "\n",
            $password . "\r\n",
            $password . "\r",
            trim($password)
        ];

        foreach ($variations as $variation) {
            if ($variation !== $password) {
                $certData = [];
                if (openssl_pkcs12_read($p12Content, $certData, $variation)) {
                    $this->certificadoData = $certData;
                    \Log::info('FirmaElectronicaService: Método alternativo 3 exitoso con variación de contraseña');
                    return true;
                }
            }
        }

        // Método 4: Intentar usando comandos de sistema si están disponibles
        if ($this->intentarParseoConComando($p12Content, $password)) {
            \Log::info('FirmaElectronicaService: Método alternativo 4 exitoso con comandos de sistema');
            return true;
        }

        \Log::error('FirmaElectronicaService: Todos los métodos alternativos fallaron');
        return false;
    }

    /**
     * Intenta parsear el certificado usando comandos de OpenSSL del sistema
     */
    private function intentarParseoConComando($p12Content, $password)
    {
        try {
            // Crear archivo temporal para el certificado
            $tempP12 = tempnam(sys_get_temp_dir(), 'cert_');
            file_put_contents($tempP12, $p12Content);

            // Crear directorio temporal para extraer
            $tempDir = sys_get_temp_dir() . '/openssl_extract_' . uniqid();
            mkdir($tempDir);

            // Comando para extraer certificado y clave privada
            $command = "openssl pkcs12 -in \"$tempP12\" -out \"$tempDir/cert.pem\" -nokeys -passin pass:\"$password\" 2>&1";

            exec($command, $output, $returnCode);

            if ($returnCode === 0) {
                // Extraer clave privada
                $keyCommand = "openssl pkcs12 -in \"$tempP12\" -out \"$tempDir/key.pem\" -nocerts -passin pass:\"$password\" -passout pass:\"$password\" 2>&1";
                exec($keyCommand, $keyOutput, $keyReturnCode);

                if ($keyReturnCode === 0) {
                    // Leer los archivos extraídos
                    $certContent = file_get_contents("$tempDir/cert.pem");
                    $keyContent = file_get_contents("$tempDir/key.pem");

                    if ($certContent && $keyContent) {
                        $this->certificadoData = [
                            'cert' => $certContent,
                            'pkey' => $keyContent
                        ];

                        // Limpiar archivos temporales
                        unlink($tempP12);
                        unlink("$tempDir/cert.pem");
                        unlink("$tempDir/key.pem");
                        rmdir($tempDir);

                        return true;
                    }
                }
            }

            // Limpiar archivos temporales en caso de error
            if (file_exists($tempP12)) unlink($tempP12);
            if (file_exists("$tempDir/cert.pem")) unlink("$tempDir/cert.pem");
            if (file_exists("$tempDir/key.pem")) unlink("$tempDir/key.pem");
            if (file_exists($tempDir)) rmdir($tempDir);

        } catch (Exception $e) {
            \Log::error('FirmaElectronicaService: Error en parseo con comandos', [
                'error' => $e->getMessage()
            ]);
        }

        return false;
    }

    /**
     * Destructor para limpiar archivos temporales
     */
    public function __destruct()
    {
        if (isset($this->tempConfigFile) && file_exists($this->tempConfigFile)) {
            unlink($this->tempConfigFile);
            putenv("OPENSSL_CONF=");
        }
    }

    public function firmarXML($xmlContent)
    {
        try {
            // Intentar primero con el servicio Python (más compatible)
            $resultadoPython = $this->firmarConPython($xmlContent);

            if ($resultadoPython !== false) {
                \Log::info('FirmaElectronicaService: Firma realizada exitosamente con Python');
                return $resultadoPython;
            }

            // Si Python falla, intentar con PHP (método original)
            \Log::info('FirmaElectronicaService: Python falló, intentando con PHP');
            return $this->firmarConPHP($xmlContent);

        } catch (Exception $e) {
            \Log::error('FirmaElectronicaService: Error en firma XML', [
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }

    /**
     * Firmar XML usando servicio Python (más compatible con certificados antiguos)
     */
    private function firmarConPython($xmlContent)
    {
        try {
            // Crear archivo temporal para el XML
            $tempXmlFile = tempnam(sys_get_temp_dir(), 'xml_firma_');
            file_put_contents($tempXmlFile, $xmlContent);

            // Ruta al script Python
            $pythonScript = __DIR__ . '/../../firma_service.py';

            // Preparar comando con ruta del certificado y contraseña
            $command = 'py "' . $pythonScript . '" "' . $this->certificadoPath . '" "' . $this->claveCertificado . '" "' . $tempXmlFile . '"';

            // Ejecutar comando
            $output = shell_exec($command);

            // Limpiar archivo temporal
            unlink($tempXmlFile);

            if ($output === null) {
                return false;
            }

            // Parsear resultado JSON
            $result = json_decode($output, true);

            if ($result && isset($result['success']) && $result['success']) {
                return $result['signed_xml'];
            }

            return false;

        } catch (Exception $e) {
            \Log::warning('FirmaElectronicaService: Error en firma con Python', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Firmar XML usando PHP (método original con XMLSecLibs)
     */
    private function firmarConPHP($xmlContent)
    {
        try {
            // Cargar el certificado usando PHP en este método
            $certificadoData = $this->cargarCertificadoPHP();

            $doc = new DOMDocument();
            $doc->loadXML($xmlContent);

            $objDSig = new XMLSecurityDSig();
            $objDSig->setCanonicalMethod(XMLSecurityDSig::C14N);

            // Agregar referencia al documento completo
            $objDSig->addReference(
                $doc,
                XMLSecurityDSig::SHA1,
                ['http://www.w3.org/2000/09/xmldsig#enveloped-signature'],
                ['force_uri' => true]
            );

            $objKey = new XMLSecurityKey(XMLSecurityKey::RSA_SHA1, ['type' => 'private']);
            $objKey->loadKey($certificadoData['pkey']);

            $objDSig->sign($objKey);
            $objDSig->add509Cert($certificadoData['cert']);

            // Crear la firma
            $signatureNode = $objDSig->appendSignature($doc->documentElement);

            // Agregar propiedades XAdES-BES
            $this->addXAdESProperties($doc, $signatureNode, $certificadoData['cert']);

            return $doc->saveXML();

        } catch (\Exception $e) {
            \Log::error('Error en firmarConPHP: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Cargar certificado usando PHP (con todos los métodos alternativos)
     */
    private function cargarCertificadoPHP()
    {
        $p12Content = file_get_contents($this->certificadoPath);
        if ($p12Content === false) {
            throw new Exception("No se pudo leer el contenido del archivo del certificado: " . $this->certificadoPath);
        }

        // Configurar OpenSSL para certificados legacy (OpenSSL 3.0+)
        $this->configurarOpenSSLParaLegacy();

        $certificadoData = [];
        $result = false;

        // Método 1: Intentar con configuración legacy
        $result = openssl_pkcs12_read($p12Content, $certificadoData, $this->claveCertificado);

        if (!$result) {
            \Log::info('FirmaElectronicaService: Método legacy falló, intentando método alternativo');

            // Método 2: Intentar con opciones específicas
            $certificadoData = [];
            $result = $this->intentarParseoAlternativo($p12Content, $this->claveCertificado);
        }

        if (!$result) {
            $openssl_error = openssl_error_string();
            \Log::error('FirmaElectronicaService: Error al leer certificado P12 en firmarConPHP', [
                'error_openssl' => $openssl_error,
                'ruta' => $this->certificadoPath,
                'tamano_archivo' => strlen($p12Content),
                'version_openssl' => OPENSSL_VERSION_TEXT,
                'version_php' => PHP_VERSION
            ]);

            throw new Exception("No se pudo leer el certificado P12 con PHP. Error OpenSSL: $openssl_error");
        }

        if (!isset($certificadoData['cert']) || !isset($certificadoData['pkey'])) {
            throw new Exception("El archivo P12 no contiene un certificado válido o clave privada");
        }

        return $certificadoData;
    }

    private function getCertDigest($certificadoDigital)
    {
        $cert = openssl_x509_read($certificadoDigital);
        return base64_encode(openssl_x509_fingerprint($cert, "sha1", true));
    }

    private function getIssuerName($certificadoDigital)
    {
        $certData = openssl_x509_parse($certificadoDigital);
        $issuer = $certData['issuer'];
        $issuerParts = [];
        foreach ($issuer as $key => $value) {
            $issuerParts[] = "$key=$value";
        }
        return implode(',', $issuerParts);
    }

    private function getSerialNumber($certificadoDigital)
    {
        $certData = openssl_x509_parse($certificadoDigital);
        return $certData['serialNumber'];
    }

    private function addXAdESProperties(DOMDocument $doc, $signatureNode, $certificadoDigital)
    {
        $xadesNamespace = 'http://uri.etsi.org/01903/v1.3.2#';

        // Crear Object para XAdES
        $objectNode = $doc->createElement('ds:Object');
        $signatureNode->appendChild($objectNode);

        $qualifyingProperties = $doc->createElementNS($xadesNamespace, 'etsi:QualifyingProperties');
        $qualifyingProperties->setAttribute('Target', '#' . $signatureNode->getAttribute('Id'));
        $objectNode->appendChild($qualifyingProperties);

        // SignedProperties
        $signedProperties = $doc->createElementNS($xadesNamespace, 'etsi:SignedProperties');
        $signedProperties->setAttribute('Id', 'SignedProperties-' . $signatureNode->getAttribute('Id'));
        $qualifyingProperties->appendChild($signedProperties);

        // SignedSignatureProperties
        $signedSignatureProperties = $doc->createElementNS($xadesNamespace, 'etsi:SignedSignatureProperties');
        $signedProperties->appendChild($signedSignatureProperties);

        // SigningTime
        $signingTime = $doc->createElementNS($xadesNamespace, 'etsi:SigningTime', date('Y-m-d\TH:i:sP'));
        $signedSignatureProperties->appendChild($signingTime);

        // SigningCertificate
        $certDataParsed = openssl_x509_parse($certificadoDigital);
        $signingCertificate = $doc->createElementNS($xadesNamespace, 'etsi:SigningCertificate');
        $signedSignatureProperties->appendChild($signingCertificate);

        $cert = $doc->createElementNS($xadesNamespace, 'etsi:Cert');
        $signingCertificate->appendChild($cert);

        $certDigest = $doc->createElementNS($xadesNamespace, 'etsi:CertDigest');
        $cert->appendChild($certDigest);

        $digestMethod = $doc->createElementNS(XMLSecurityDSig::XMLDSIGNS, 'ds:DigestMethod');
        $digestMethod->setAttribute('Algorithm', XMLSecurityDSig::SHA1);
        $certDigest->appendChild($digestMethod);

        // Calcular digest del certificado
        openssl_x509_export($certificadoDigital, $certContent);
        $certContent = str_replace(['-----BEGIN CERTIFICATE-----', '-----END CERTIFICATE-----', "\n"], '', $certContent);
        $certDigestValue = base64_encode(hash('sha1', base64_decode($certContent), true));
        $digestValue = $doc->createElementNS(XMLSecurityDSig::XMLDSIGNS, 'ds:DigestValue', $certDigestValue);
        $certDigest->appendChild($digestValue);

        $issuerSerial = $doc->createElementNS($xadesNamespace, 'etsi:IssuerSerial');
        $cert->appendChild($issuerSerial);

        // Issuer Name
        $issuerName = [];
        foreach ($certDataParsed['issuer'] as $key => $value) {
            $issuerName[] = "$key=$value";
        }
        $x509IssuerName = $doc->createElementNS(XMLSecurityDSig::XMLDSIGNS, 'ds:X509IssuerName', implode(',', $issuerName));
        $issuerSerial->appendChild($x509IssuerName);

        $x509SerialNumber = $doc->createElementNS(XMLSecurityDSig::XMLDSIGNS, 'ds:X509SerialNumber', $certDataParsed['serialNumber']);
        $issuerSerial->appendChild($x509SerialNumber);
    }
}
