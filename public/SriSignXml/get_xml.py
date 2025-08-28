import requests
import json
import os
from datetime import datetime

def get_authorized_xml(access_key, save_to_file=True):
    """
    Obtiene el XML autorizado del SRI para una clave de acceso específica
    """
    try:
        print(f"🔍 Obteniendo XML autorizado para: {access_key}")
        
        # Consultar el estado de la factura
        response = requests.get(
            f"http://127.0.0.1:8000/invoice/status/{access_key}",
            headers={"Content-Type": "application/json"}
        )
        
        if response.status_code == 200:
            result = response.json()
            
            if 'result' in result and result['result'].get('xmlAutorizado'):
                xml_content = result['result']['xmlAutorizado']
                
                print(f"✅ XML autorizado obtenido exitosamente")
                print(f"📏 Tamaño: {len(xml_content)} caracteres")
                print(f"📊 Estado: {result['result'].get('estado', 'N/A')}")
                print(f"🔢 Número Autorización: {result['result'].get('numeroAutorizacion', 'N/A')}")
                print(f"📅 Fecha Autorización: {result['result'].get('fechaAutorizacion', 'N/A')}")
                
                if save_to_file:
                    # Crear nombre de archivo con timestamp
                    timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
                    filename = f"factura_autorizada_{access_key}_{timestamp}.xml"
                    
                    # Crear directorio si no existe
                    output_dir = "xml_autorizados"
                    os.makedirs(output_dir, exist_ok=True)
                    
                    filepath = os.path.join(output_dir, filename)
                    
                    # Guardar XML en archivo
                    with open(filepath, 'w', encoding='utf-8') as f:
                        f.write(xml_content)
                    
                    print(f"💾 XML guardado en: {filepath}")
                    
                    # También mostrar un fragmento del XML
                    print(f"\n📄 FRAGMENTO DEL XML AUTORIZADO:")
                    print("="*60)
                    print(xml_content[:500] + "..." if len(xml_content) > 500 else xml_content)
                    print("="*60)
                    
                return xml_content, filepath if save_to_file else None
            else:
                print(f"❌ No se encontró XML autorizado")
                print(f"📊 Estado actual: {result.get('result', {}).get('estado', 'DESCONOCIDO')}")
                return None, None
                
        else:
            print(f"❌ Error HTTP: {response.status_code}")
            print(f"Respuesta: {response.text}")
            return None, None
            
    except Exception as e:
        print(f"❌ Error: {e}")
        return None, None

def extract_xml_from_sign_response(response_data):
    """
    Extrae y guarda el XML firmado de la respuesta del endpoint /invoice/sign
    """
    try:
        if 'result' in response_data and response_data['result'].get('xmlFileSigned'):
            xml_content = response_data['result']['xmlFileSigned']
            access_key = response_data['result'].get('accessKey', 'unknown')
            
            # Crear nombre de archivo
            timestamp = datetime.now().strftime("%Y%m%d_%H%M%S")
            filename = f"factura_firmada_{access_key}_{timestamp}.xml"
            
            # Crear directorio si no existe
            output_dir = "xml_firmados"
            os.makedirs(output_dir, exist_ok=True)
            
            filepath = os.path.join(output_dir, filename)
            
            # Guardar XML en archivo
            with open(filepath, 'w', encoding='utf-8') as f:
                f.write(xml_content)
            
            print(f"💾 XML firmado guardado en: {filepath}")
            return filepath
            
    except Exception as e:
        print(f"❌ Error guardando XML firmado: {e}")
        return None

def show_xml_info(access_key):
    """
    Muestra información detallada del XML autorizado
    """
    try:
        response = requests.get(f"http://127.0.0.1:8000/invoice/status/{access_key}")
        
        if response.status_code == 200:
            result = response.json()['result']
            
            print(f"\n📋 INFORMACIÓN COMPLETA DE LA FACTURA")
            print("="*50)
            print(f"🔑 Clave de Acceso: {result.get('accessKey', 'N/A')}")
            print(f"📊 Estado: {result.get('estado', 'N/A')}")
            print(f"✅ Autorizada: {result.get('isAuthorized', 'N/A')}")
            print(f"🔢 Número Autorización: {result.get('numeroAutorizacion', 'N/A')}")
            print(f"📅 Fecha Autorización: {result.get('fechaAutorizacion', 'N/A')}")
            print(f"🌍 Ambiente: {result.get('ambiente', 'N/A')}")
            
            # Mostrar mensajes si los hay
            mensajes = result.get('mensajes', [])
            if mensajes:
                print(f"\n💬 MENSAJES DEL SRI:")
                for i, msg in enumerate(mensajes, 1):
                    print(f"  {i}. {msg}")
            
            # Información del XML
            xml_autorizado = result.get('xmlAutorizado')
            if xml_autorizado:
                print(f"\n📄 XML AUTORIZADO:")
                print(f"   📏 Tamaño: {len(xml_autorizado):,} caracteres")
                print(f"   🔍 Disponible para descarga: ✅")
            else:
                print(f"\n📄 XML AUTORIZADO: ❌ No disponible")
                
    except Exception as e:
        print(f"❌ Error obteniendo información: {e}")

if __name__ == "__main__":
    # Clave de acceso de nuestra factura autorizada
    access_key = "2808202501172587499200110010010000014932047163719"
    
    print("🚀 OBTENER XML AUTORIZADO DEL SRI")
    print("="*50)
    
    # Mostrar información detallada
    show_xml_info(access_key)
    
    print("\n" + "="*50)
    print("📥 DESCARGANDO XML AUTORIZADO...")
    
    # Obtener y guardar XML autorizado
    xml_content, filepath = get_authorized_xml(access_key, save_to_file=True)
    
    if xml_content:
        print(f"\n🎉 ¡XML autorizado obtenido exitosamente!")
        print(f"📁 Archivo guardado en: {filepath}")
        print(f"\n💡 SUGERENCIA:")
        print(f"   - Puedes abrir el archivo XML con cualquier editor de texto")
        print(f"   - El XML contiene la firma digital XAdES y la autorización del SRI")
        print(f"   - Este es el XML oficial que puedes usar para facturación")
    else:
        print(f"\n❌ No se pudo obtener el XML autorizado")
        
    print(f"\n📂 Archivos disponibles:")
    print(f"   - XML Firmados: ./xml_firmados/")
    print(f"   - XML Autorizados: ./xml_autorizados/")
