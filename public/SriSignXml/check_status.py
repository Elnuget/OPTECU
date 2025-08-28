import requests
import time

# Clave de acceso de la factura que enviamos
access_key = "2808202501172587499200110010010000014932047163719"

def check_invoice_status(access_key):
    """Consulta el estado de una factura por su clave de acceso"""
    try:
        print(f"🔍 Consultando estado para clave: {access_key}")
        
        response = requests.get(
            f"http://127.0.0.1:8000/invoice/status/{access_key}",
            headers={"Content-Type": "application/json"}
        )
        
        print(f"Status Code: {response.status_code}")
        
        if response.status_code == 200:
            result = response.json()
            print(f"📋 Respuesta completa: {result}")
            
            if 'result' in result:
                data = result['result']
                print(f"\n📊 ESTADO DE LA FACTURA:")
                print(f"🔑 Clave de Acceso: {data.get('accessKey', 'N/A')}")
                print(f"📊 Estado: {data.get('estado', 'N/A')}")
                print(f"✅ Autorizada: {data.get('isAuthorized', 'N/A')}")
                print(f"🔢 Número Autorización: {data.get('numeroAutorizacion', 'N/A')}")
                print(f"📅 Fecha Autorización: {data.get('fechaAutorizacion', 'N/A')}")
                print(f"🌍 Ambiente: {data.get('ambiente', 'N/A')}")
                
                mensajes = data.get('mensajes', [])
                if mensajes:
                    print(f"💬 Mensajes:")
                    for msg in mensajes:
                        print(f"  - {msg}")
                        
                if data.get('xmlAutorizado'):
                    print(f"📄 XML Autorizado disponible: Sí ({len(data['xmlAutorizado'])} caracteres)")
                else:
                    print(f"📄 XML Autorizado disponible: No")
                    
                return data.get('isAuthorized')
            else:
                print("❌ Error: No se encontró 'result' en la respuesta")
                return False
        else:
            print(f"❌ Error HTTP: {response.status_code}")
            print(f"Respuesta: {response.text}")
            return False
            
    except Exception as e:
        print(f"❌ Error al consultar: {e}")
        return False

def monitor_invoice_status(access_key, max_attempts=10, delay=30):
    """Monitorea el estado de la factura hasta que se autorice o se agote el tiempo"""
    print(f"🕐 Iniciando monitoreo de factura {access_key}")
    print(f"⏱️ Intentos máximos: {max_attempts}, Intervalo: {delay} segundos")
    
    for attempt in range(1, max_attempts + 1):
        print(f"\n🔄 Intento {attempt}/{max_attempts}")
        
        status = check_invoice_status(access_key)
        
        if status is True:
            print(f"🎉 ¡FACTURA AUTORIZADA! ✅")
            return True
        elif status is False:
            print(f"❌ Factura rechazada o con error")
            return False
        else:
            if attempt < max_attempts:
                print(f"⏳ Aún procesando... Esperando {delay} segundos para el siguiente intento")
                time.sleep(delay)
            else:
                print(f"⚠️ Se agotó el tiempo de espera. La factura aún está procesándose.")
                return None

if __name__ == "__main__":
    print("🚀 CONSULTA DE ESTADO DE FACTURA SRI")
    print("="*50)
    
    # Consulta inmediata
    print("📋 CONSULTA INMEDIATA:")
    check_invoice_status(access_key)
    
    print("\n" + "="*50)
    print("🔄 ¿Desea monitorear el estado? (s/n): ", end="")
    respuesta = input().lower().strip()
    
    if respuesta in ['s', 'si', 'sí', 'y', 'yes']:
        print("🕐 Iniciando monitoreo...")
        monitor_invoice_status(access_key)
    else:
        print("✋ Monitoreo cancelado. Puede usar este script más tarde para consultar el estado.")
