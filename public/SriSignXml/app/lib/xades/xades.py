import os
import subprocess
import logging


class Xades(object):
    def sign(self, xml_no_signed_path, xml_signed_path, file_pk12_path, password):
        JAR_PATH = 'FirmaElectronica/FirmaElectronica.jar'
        JAVA_CMD = 'java'
        path_jar_to_sign = os.path.join(os.path.dirname(__file__), JAR_PATH)
        
        print(f"☕ JAR Path: {path_jar_to_sign}")
        print(f"🗃️ ¿JAR existe? {os.path.exists(path_jar_to_sign)}")
        
        try:
            command = [
                JAVA_CMD,
                '-jar',
                path_jar_to_sign,
                xml_no_signed_path,
                file_pk12_path,
                password,
                xml_signed_path
            ]
            
            print(f"🚀 Ejecutando comando: {' '.join(command[:6])} [PASSWORD_HIDDEN] {command[6] if len(command) > 6 else ''}")
            
            subprocess.check_output(command)
            print("✅ subprocess.check_output ejecutado sin errores")
        except subprocess.CalledProcessError as e:
            returnCode = e.returncode
            output = e.output
            print(f"❌ Error en subprocess - Código: {returnCode}")
            print(f"❌ Output del error: {output}")
            logging.error('Llamada a proceso JAVA codigo: %s' % returnCode)
            logging.error('Error: %s' % output)

        p = subprocess.Popen(
            command,
            stdout=subprocess.PIPE,
            stderr=subprocess.STDOUT
        )

        res = p.communicate()
        print(f"📤 Resultado final del proceso Java: {res[0]}")
        return res[0]
