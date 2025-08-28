#!/usr/bin/env python3
"""
Verificador de Ambiente SRI
Este script verifica que todos los componentes estén configurados para PRUEBAS
"""

import os
import sys
import json
from dotenv import dotenv_values

def verificar_ambiente_sri():
    """
    Verificación completa del ambiente SRI
    """
    resultado = {
        'ambiente': 'DESCONOCIDO',
        'es_pruebas': False,
        'urls_validas': False,
        'errores': [],
        'warnings': []
    }
    
    try:
        # 1. Verificar archivo .env
        env_path = os.path.join(os.path.dirname(__file__), '.env')
        if not os.path.exists(env_path):
            resultado['errores'].append('Archivo .env no encontrado')
            return resultado
        
        config = {**dotenv_values(env_path)}
        
        # 2. Verificar URLs
        url_reception = config.get('URL_RECEPTION', '')
        url_authorization = config.get('URL_AUTHORIZATION', '')
        
        if not url_reception or not url_authorization:
            resultado['errores'].append('URLs de SRI no configuradas')
            return resultado
        
        # 3. Validar que sean URLs de pruebas
        urls_pruebas = [
            'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl',
            'https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl'
        ]
        
        if url_reception == urls_pruebas[0] and url_authorization == urls_pruebas[1]:
            resultado['ambiente'] = 'PRUEBAS'
            resultado['es_pruebas'] = True
            resultado['urls_validas'] = True
        elif 'celcer.sri.gob.ec' in url_reception and 'celcer.sri.gob.ec' in url_authorization:
            resultado['ambiente'] = 'PRUEBAS'
            resultado['es_pruebas'] = True
            resultado['urls_validas'] = True
            resultado['warnings'].append('URLs de pruebas válidas pero formato diferente')
        elif 'cel.sri.gob.ec' in url_reception or 'cel.sri.gob.ec' in url_authorization:
            resultado['ambiente'] = 'PRODUCCION'
            resultado['es_pruebas'] = False
            resultado['urls_validas'] = False
            resultado['errores'].append('⚠️ PELIGRO: URLs de PRODUCCIÓN detectadas')
        else:
            resultado['ambiente'] = 'DESCONOCIDO'
            resultado['es_pruebas'] = False
            resultado['urls_validas'] = False
            resultado['errores'].append('URLs no reconocidas')
        
        # 4. Verificar otros parámetros
        ambiente = config.get('AMBIENTE', '1')
        if ambiente != '1':
            resultado['warnings'].append(f'Variable AMBIENTE configurada como {ambiente}, debería ser 1 para pruebas')
        
        # 5. Información adicional
        resultado['configuracion'] = {
            'url_reception': url_reception,
            'url_authorization': url_authorization,
            'ambiente': ambiente,
            'tiene_password': bool(config.get('PASSWORD'))
        }
        
        return resultado
        
    except Exception as e:
        resultado['errores'].append(f'Error verificando ambiente: {str(e)}')
        return resultado

def mostrar_reporte(resultado):
    """Mostrar reporte legible"""
    print("=" * 60)
    print("🔍 VERIFICACIÓN DE AMBIENTE SRI")
    print("=" * 60)
    
    # Estado principal
    if resultado['es_pruebas']:
        print("✅ AMBIENTE: PRUEBAS (SEGURO)")
    else:
        print("❌ AMBIENTE: " + resultado['ambiente'] + " (REVISAR)")
    
    # URLs
    if resultado['urls_validas']:
        print("✅ URLs: Configuradas para pruebas")
    else:
        print("❌ URLs: NO válidas para pruebas")
    
    # Errores
    if resultado['errores']:
        print("\n🚨 ERRORES CRÍTICOS:")
        for error in resultado['errores']:
            print(f"   - {error}")
    
    # Warnings
    if resultado['warnings']:
        print("\n⚠️  ADVERTENCIAS:")
        for warning in resultado['warnings']:
            print(f"   - {warning}")
    
    # Configuración actual
    if 'configuracion' in resultado:
        config = resultado['configuracion']
        print("\n📋 CONFIGURACIÓN ACTUAL:")
        print(f"   Recepción: {config['url_reception']}")
        print(f"   Autorización: {config['url_authorization']}")
        print(f"   Ambiente: {config['ambiente']}")
        print(f"   Password configurado: {'Sí' if config['tiene_password'] else 'No'}")
    
    print("\n" + "=" * 60)
    
    # Recomendaciones
    if not resultado['es_pruebas']:
        print("🔧 ACCIÓN REQUERIDA:")
        print("   Actualizar .env con URLs de pruebas:")
        print("   URL_RECEPTION=https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl")
        print("   URL_AUTHORIZATION=https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl")
        print("   AMBIENTE=1")
    else:
        print("✅ CONFIGURACIÓN CORRECTA PARA PRUEBAS")
    
    print("=" * 60)

def main():
    """Función principal"""
    if len(sys.argv) > 1 and sys.argv[1] == '--json':
        # Salida JSON para scripts
        resultado = verificar_ambiente_sri()
        print(json.dumps(resultado, ensure_ascii=False, indent=2))
    else:
        # Salida legible para humanos
        resultado = verificar_ambiente_sri()
        mostrar_reporte(resultado)
        
        # Exit code según el resultado
        sys.exit(0 if resultado['es_pruebas'] else 1)

if __name__ == "__main__":
    main()
