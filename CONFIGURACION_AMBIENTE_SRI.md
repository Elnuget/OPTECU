# CONFIGURACIÓN DE AMBIENTE SRI - ECUADOR

## ⚠️ IMPORTANTE: AMBIENTE ACTUAL
**CONFIGURADO PARA PRUEBAS** - No afecta sistema de producción del SRI

## 🔧 URLs por Ambiente

### 🧪 AMBIENTE DE PRUEBAS (ACTUAL)
```
URL_RECEPTION=https://celcer.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl
URL_AUTHORIZATION=https://celcer.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl
AMBIENTE=1
```

### 🏭 AMBIENTE DE PRODUCCIÓN (NO USAR AÚN)
```
URL_RECEPTION=https://cel.sri.gob.ec/comprobantes-electronicos-ws/RecepcionComprobantesOffline?wsdl
URL_AUTHORIZATION=https://cel.sri.gob.ec/comprobantes-electronicos-ws/AutorizacionComprobantesOffline?wsdl
AMBIENTE=2
```

## 🔍 Verificación de Ambiente

Para verificar el ambiente actual, ejecutar:

```bash
cd public/SriSignXml
python verificar_ambiente.py
```

## 🚨 Validaciones de Seguridad Implementadas

1. **Validación en PHP** (`SriPythonService.php`):
   - Verifica URLs antes de cada procesamiento
   - Previene envío accidental a producción
   - Log de seguridad en cada operación

2. **Validación en Python** (`sri_processor.py`):
   - Doble verificación en scripts Python
   - Excepción si detecta URLs de producción
   - Confirmación de ambiente en logs

3. **Script de Verificación** (`verificar_ambiente.py`):
   - Análisis completo de configuración
   - Reporte detallado del estado actual
   - Detección automática de inconsistencias

## 📋 Diferencias entre Ambientes

| Aspecto | Pruebas (celcer) | Producción (cel) |
|---------|------------------|------------------|
| **URL Base** | celcer.sri.gob.ec | cel.sri.gob.ec |
| **RUC requerido** | Puede ser ficticio | RUC real autorizado |
| **Certificado** | Pruebas o demo | Certificado real |
| **Facturación** | No afecta contabilidad | Facturación real |
| **Código ambiente** | 1 | 2 |

## 🔄 Cambio a Producción (FUTURO)

**⚠️ NO REALIZAR ESTOS PASOS AÚN**

Cuando sea autorizado el cambio a producción:

1. **Actualizar archivo `.env`**:
   ```bash
   cd public/SriSignXml
   # Editar .env y cambiar URLs a cel.sri.gob.ec
   # Cambiar AMBIENTE=2
   ```

2. **Verificar certificado**:
   - Usar certificado P12 real autorizado por el SRI
   - Contraseña real del certificado

3. **Validar RUC**:
   - Debe ser RUC real y autorizado para facturación electrónica

4. **Verificar ambiente**:
   ```bash
   python verificar_ambiente.py
   ```

5. **Prueba inicial**:
   - Procesar una factura de prueba
   - Verificar autorización en portal SRI
   - Confirmar que aparece en declaraciones

## 🛡️ Medidas de Seguridad

- ✅ Validaciones automáticas impiden cambio accidental
- ✅ Logs de seguridad en cada operación
- ✅ Verificación de URLs antes de cada envío
- ✅ Script de verificación independiente
- ✅ Documentación clara de diferencias

## 📞 Contacto para Cambio a Producción

Antes de cambiar a producción:
1. Confirmar autorización del SRI
2. Verificar certificado digital válido
3. Probar con factura de prueba
4. Documentar el cambio

---

**Fecha de configuración**: 28 de agosto de 2025  
**Estado actual**: PRUEBAS (SEGURO)  
**Próxima revisión**: Cuando se autorice producción
