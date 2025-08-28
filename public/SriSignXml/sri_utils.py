#!/usr/bin/env python3
"""
Utilidades para gestión de certificados P12 y configuración SRI
Este script proporciona funciones auxiliares para el procesamiento SRI
"""

import os
import sys
import json
import shutil
from pathlib import Path

def copiar_certificado(origen, destino, password):
    """
    Copia un certificado P12 al directorio de procesamiento
    NOTA: Ya no se crea archivo .env - configuración hardcodeada por seguridad
    
    Args:
        origen: Ruta del certificado original
        destino: Ruta donde copiar el certificado  
        password: Contraseña del certificado (se pasa como parámetro)
        
    Returns:
        dict: Resultado de la operación
    """
    try:
        # Verificar que el archivo origen existe
        if not os.path.exists(origen):
            return {
                'success': False,
                'message': f'Certificado origen no encontrado: {origen}'
            }
        
        # Crear directorio destino si no existe
        dest_dir = os.path.dirname(destino)
        if not os.path.exists(dest_dir):
            os.makedirs(dest_dir, exist_ok=True)
        
        # Copiar certificado
        shutil.copy2(origen, destino)
        
        return {
            'success': True,
            'message': 'Certificado copiado exitosamente (sin archivo .env por seguridad)',
            'certificado_path': destino
        }
        
    except Exception as e:
        return {
            'success': False,
            'message': f'Error copiando certificado: {str(e)}'
        }

def verificar_dependencias():
    """
    Verificar que todas las dependencias Python están disponibles
    
    Returns:
        dict: Estado de las dependencias
    """
    dependencias = [
        'lxml',
        'cryptography', 
        'requests',
        'python-dotenv',
        'fastapi'
    ]
    
    resultado = {
        'success': True,
        'dependencias': {},
        'faltantes': []
    }
    
    for dep in dependencias:
        try:
            __import__(dep.replace('-', '_'))
            resultado['dependencias'][dep] = 'OK'
        except ImportError:
            resultado['dependencias'][dep] = 'FALTANTE'
            resultado['faltantes'].append(dep)
            resultado['success'] = False
    
    return resultado

def verificar_estructura_directorios():
    """
    Verificar que la estructura de directorios está correcta
    
    Returns:
        dict: Estado de la estructura
    """
    base_dir = os.path.dirname(__file__)
    directorios_requeridos = [
        'app',
        'app/lib',
        'app/models', 
        'app/routes',
        'app/utils'
    ]
    
    resultado = {
        'success': True,
        'directorios': {},
        'faltantes': []
    }
    
    for directorio in directorios_requeridos:
        path_completo = os.path.join(base_dir, directorio)
        if os.path.exists(path_completo):
            resultado['directorios'][directorio] = 'OK'
        else:
            resultado['directorios'][directorio] = 'FALTANTE'
            resultado['faltantes'].append(directorio)
            resultado['success'] = False
    
    return resultado

def instalar_dependencias():
    """
    Intentar instalar dependencias faltantes usando pip
    
    Returns:
        dict: Resultado de la instalación
    """
    try:
        import subprocess
        
        dependencias = [
            'lxml',
            'cryptography', 
            'requests',
            'python-dotenv',
            'fastapi',
            'uvicorn'
        ]
        
        for dep in dependencias:
            result = subprocess.run([
                sys.executable, '-m', 'pip', 'install', dep
            ], capture_output=True, text=True)
            
            if result.returncode != 0:
                return {
                    'success': False,
                    'message': f'Error instalando {dep}: {result.stderr}'
                }
        
        return {
            'success': True,
            'message': 'Todas las dependencias instaladas correctamente'
        }
        
    except Exception as e:
        return {
            'success': False,
            'message': f'Error en instalación: {str(e)}'
        }

def main():
    """Función principal del script de utilidades"""
    if len(sys.argv) < 2:
        print("Uso: python sri_utils.py <comando> [argumentos]")
        print("Comandos disponibles:")
        print("  copiar_cert <origen> <destino> <password>")
        print("  verificar_deps")
        print("  verificar_estructura") 
        print("  instalar_deps")
        sys.exit(1)
    
    comando = sys.argv[1]
    
    try:
        if comando == 'copiar_cert':
            if len(sys.argv) < 5:
                print("Uso: python sri_utils.py copiar_cert <origen> <destino> <password>")
                sys.exit(1)
            
            origen = sys.argv[2]
            destino = sys.argv[3]
            password = sys.argv[4]
            
            resultado = copiar_certificado(origen, destino, password)
            print(json.dumps(resultado, ensure_ascii=False, indent=2))
            
        elif comando == 'verificar_deps':
            resultado = verificar_dependencias()
            print(json.dumps(resultado, ensure_ascii=False, indent=2))
            
        elif comando == 'verificar_estructura':
            resultado = verificar_estructura_directorios()
            print(json.dumps(resultado, ensure_ascii=False, indent=2))
            
        elif comando == 'instalar_deps':
            resultado = instalar_dependencias()
            print(json.dumps(resultado, ensure_ascii=False, indent=2))
            
        else:
            print(f"Comando desconocido: {comando}")
            sys.exit(1)
            
    except Exception as e:
        error_result = {
            'success': False,
            'message': f'Error ejecutando comando {comando}: {str(e)}'
        }
        print(json.dumps(error_result, ensure_ascii=False, indent=2))
        sys.exit(1)

if __name__ == "__main__":
    main()
