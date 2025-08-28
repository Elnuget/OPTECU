import os
import tempfile
from app.lib.xades.xades import Xades

# Crear archivos temporales de prueba
xml_content = '''<?xml version="1.0" encoding="UTF-8"?>
<factura id="comprobante" version="1.0.0">
  <infoTributaria>
    <ambiente>1</ambiente>
    <tipoEmision>1</tipoEmision>
    <razonSocial>Carlos Alberto Angulo Pizarro</razonSocial>
    <ruc>1725874992001</ruc>
    <claveAcceso>2708202501172587499200110010010000014931129592311</claveAcceso>
    <codDoc>01</codDoc>
    <estab>001</estab>
    <ptoEmi>001</ptoEmi>
    <secuencial>000001493</secuencial>
    <dirMatriz>Calle: E3J Numero: S56-65 Interseccion: S57 P INOCENCIO JACOME</dirMatriz>
  </infoTributaria>
</factura>'''

# Crear archivo XML temporal
with tempfile.NamedTemporaryFile(mode='w', suffix='.xml', delete=False, encoding='utf-8') as xml_file:
    xml_file.write(xml_content)
    xml_unsigned_path = xml_file.name

# Crear archivo de salida
xml_signed_path = xml_unsigned_path.replace('.xml', '_signed.xml')

print(f"📄 XML sin firmar: {xml_unsigned_path}")
print(f"📄 XML firmado: {xml_signed_path}")
print(f"🔑 Certificado: {os.path.abspath('app/signature.p12')}")
print(f"🔒 Contraseña: orionRigel15")

try:
    # Verificar que el certificado existe
    cert_path = os.path.abspath('app/signature.p12')
    if not os.path.exists(cert_path):
        print(f"❌ Error: No se encuentra el certificado en {cert_path}")
        exit(1)
    
    print(f"✅ Certificado encontrado: {cert_path}")
    print(f"📊 Tamaño del certificado: {os.path.getsize(cert_path)} bytes")
    
    # Intentar firmar
    print("\n🔧 Iniciando proceso de firma...")
    xades = Xades()
    result = xades.sign(
        xml_unsigned_path,
        xml_signed_path,
        cert_path,
        "orionRigel15"
    )
    
    print(f"📤 Resultado de la firma: {result}")
    
    # Verificar si se creó el archivo firmado
    if os.path.exists(xml_signed_path):
        print(f"✅ Archivo firmado creado exitosamente!")
        print(f"📊 Tamaño del archivo firmado: {os.path.getsize(xml_signed_path)} bytes")
        
        # Mostrar las primeras líneas del archivo firmado
        with open(xml_signed_path, 'r', encoding='utf-8') as f:
            content = f.read()[:500]
            print(f"📄 Primeras líneas del XML firmado:\n{content}...")
    else:
        print(f"❌ No se creó el archivo firmado")
        
except Exception as e:
    print(f"❌ Error durante la firma: {e}")
    import traceback
    traceback.print_exc()

finally:
    # Limpiar archivos temporales
    try:
        if os.path.exists(xml_unsigned_path):
            os.unlink(xml_unsigned_path)
        if os.path.exists(xml_signed_path):
            os.unlink(xml_signed_path)
    except:
        pass
