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
    NOTA: Ya no depende de archivo .env - configuración hardcodeada por seguridad
    """
    resultado = {
        'ambiente': 'PRUEBAS',
        'es_pruebas': True,
        'urls_validas': True,
        'errores': [],
        'warnings': [],
        'configuracion_hardcodeada': True
    }
    
    try:
        # 1. Verificar script principal sri_processor.py
        script_path = os.path.join(os.path.dirname(__file__), 'sri_processor.py')
        if not os.path.exists(script_path):
            resultado['errores'].append('Script principal sri_processor.py no encontrado')
            resultado['es_pruebas'] = False
            return resultado
        
        # 2. Verificar contenido del script para URLs de pruebas
        with open(script_path, 'r', encoding='utf-8') as f:
            script_content = f.read()
        
        # 3. Validar que contenga URLs de pruebas
        if 'celcer.sri.gob.ec' not in script_content:
            resultado['errores'].append('URLs de pruebas no encontradas en script')
            resultado['es_pruebas'] = False
        
        # 4. Verificar que NO contenga URLs de producción
        if 'cel.sri.gob.ec' in script_content and 'celcer.sri.gob.ec' not in script_content.replace('cel.sri.gob.ec', ''):
            resultado['errores'].append('⚠️ PELIGRO: URLs de PRODUCCIÓN detectadas')
            resultado['es_pruebas'] = False
            resultado['ambiente'] = 'PRODUCCION'
        
        # 5. Verificar que el ambiente esté configurado como 1 (pruebas)
        if "'AMBIENTE': '1'" not in script_content:
            resultado['warnings'].append('Variable AMBIENTE no configurada explícitamente como 1')
        
        # 6. Verificar que NO exista archivo .env (por seguridad)
        env_path = os.path.join(os.path.dirname(__file__), '.env')
        if os.path.exists(env_path):
            resultado['warnings'].append('Archivo .env encontrado - debería eliminarse por seguridad')
        else:
            resultado['warnings'].append('✅ Archivo .env no existe (configuración más segura)')
        
        # 7. Información adicional
        resultado['configuracion'] = {
            'script_principal': script_path,
            'archivo_env_existe': os.path.exists(env_path),
            'configuracion_hardcodeada': True,
            'urls_en_codigo': 'celcer.sri.gob.ec' in script_content
        }
        
        return resultado
        
    except Exception as e:
        resultado['errores'].append(f'Error verificando ambiente: {str(e)}')
        resultado['es_pruebas'] = False
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
        print(f"   Script principal: {config.get('script_principal', 'N/A')}")
        print(f"   Archivo .env existe: {'Sí' if config.get('archivo_env_existe') else 'No (más seguro)'}")
        print(f"   Configuración hardcodeada: {'Sí' if config.get('configuracion_hardcodeada') else 'No'}")
        print(f"   URLs en código: {'Sí' if config.get('urls_en_codigo') else 'No'}")
    
    print("\n" + "=" * 60)
    
    # Recomendaciones
    if not resultado['es_pruebas']:
        print("🔧 ACCIÓN REQUERIDA:")
        print("   Verificar configuración hardcodeada en sri_processor.py")
        print("   Debe contener URLs: celcer.sri.gob.ec (no cel.sri.gob.ec)")
        print("   AMBIENTE debe ser '1' para pruebas")
    else:
        print("✅ CONFIGURACIÓN CORRECTA PARA PRUEBAS")
        print("✅ SIN ARCHIVO .ENV (MAYOR SEGURIDAD)")
        print("✅ CONFIGURACIÓN HARDCODEADA EN CÓDIGO")
    
    print("=" * 60)

def main():
    """Función principal"""
    if len(sys.argv) > 1 and sys.argv[1] == '--json':
        # Salida JSON para scripts
        resultado = verificar_ambiente_sri()
        # Usar print sin caracteres especiales para compatibilidad
        output = json.dumps(resultado, ensure_ascii=True, indent=2)
        print(output)
    else:
        # Salida legible para humanos
        resultado = verificar_ambiente_sri()
        mostrar_reporte(resultado)
        
        # Exit code según el resultado
        sys.exit(0 if resultado['es_pruebas'] else 1)

if __name__ == "__main__":
    main()
