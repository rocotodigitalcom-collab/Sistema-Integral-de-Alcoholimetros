# 📊 ANÁLISIS COMPLETO DE BASE DE DATOS
## Sistema Integral de Alcoholímetros - Documentación Técnica

---

## 🗄️ INFORMACIÓN GENERAL

- **Base de Datos**: `juegosd2_alcoholimetro`
- **Motor**: MariaDB 10.11.13
- **Collation**: utf8mb4_unicode_ci / latin1_swedish_ci (mixto)
- **Total de Tablas**: 21 tablas
- **Fecha de Análisis**: Noviembre 24, 2024

## 🏗️ ARQUITECTURA DEL SISTEMA

### Modelo Multi-Tenant
El sistema está diseñado como **multi-tenant** con aislamiento por `cliente_id`:
- Cada cliente (empresa) tiene su propio conjunto de datos
- Los clientes están asociados a planes de suscripción
- Sistema de límites por plan (usuarios, alcoholímetros, pruebas)

### Módulos Principales Identificados

1. **Gestión de Clientes y Planes** (SaaS)
2. **Gestión de Usuarios y Roles**
3. **Gestión de Alcoholímetros**
4. **Gestión de Pruebas**
5. **Gestión de Vehículos**
6. **Sistema de Alertas y Notificaciones**
7. **Reportes y Auditoría**
8. **Facturación y Suscripciones**
9. **Dispositivos Móviles**
10. **Mantenimientos y Calibraciones**

## 📋 ANÁLISIS DETALLADO DE TABLAS

### 1. NÚCLEO DEL SISTEMA

#### 📌 tb_clientes
**Propósito**: Tabla central para empresas/clientes del sistema
```sql
Campos Clave:
- id (PK)
- nombre_empresa
- ruc (UNIQUE)
- plan_id (FK -> tb_planes)
- estado: activo|inactivo|suspendido|prueba
- limite_usuarios: 5 (default)
- limite_alcoholimetros: 10 (default)
- token_api (UNIQUE)
- modo_demo: boolean
```
**Datos de Ejemplo**: 1 cliente en modo prueba (Empresa Demo SA)

#### 📌 tb_planes
**Propósito**: Planes de suscripción SaaS
```sql
Planes Actuales:
1. Básico: $49/mes - 500 pruebas, 3 usuarios, 5 alcoholímetros
2. Profesional: $99/mes - 2000 pruebas, 10 usuarios, 20 alcoholímetros
3. Empresarial: $199/mes - 10000 pruebas, 50 usuarios, 100 alcoholímetros
4. Prueba: $0/mes - 100 pruebas, 2 usuarios, 3 alcoholímetros
```
**Características por Plan**:
- reportes_avanzados
- soporte_prioritario
- acceso_api
- almacenamiento_fotos (MB)

### 2. GESTIÓN DE USUARIOS

#### 📌 tb_usuarios
**Propósito**: Usuarios del sistema por cliente
```sql
Roles:
- admin: Administrador del cliente
- supervisor: Supervisa pruebas
- conductor: Sujeto de pruebas
- tecnico: Mantenimiento de equipos
- auditor: Solo lectura/reportes
```
**Seguridad**: Passwords con hash bcrypt ($2y$10$...)
**Usuarios Demo**: 3 usuarios (admin, conductor, supervisor)

#### 📌 users
**Propósito**: Usuarios del sistema principal (no multi-tenant)
```sql
Roles:
- admin: Administrador del sistema
- user: Usuario regular
```
**Nota**: Parece ser una tabla legacy o para super-administradores

### 3. GESTIÓN DE ALCOHOLÍMETROS

#### 📌 tb_alcoholimetros
**Propósito**: Dispositivos alcoholímetros registrados
```sql
Estados:
- activo
- mantenimiento
- inactivo
- calibracion

Campos Importantes:
- numero_serie (UNIQUE por cliente)
- fecha_calibracion
- proxima_calibracion
- codigo_ecu: Para integración con vehículos
- qr_code: Para identificación rápida
```
**Dispositivos Demo**: 2 alcoholímetros activos

### 4. GESTIÓN DE PRUEBAS

#### 📌 tb_pruebas
**Propósito**: Registro de pruebas de alcoholimetría (TABLA CORE)
```sql
Campos Críticos:
- nivel_alcohol: DECIMAL(5,3)
- limite_permisible: DECIMAL(5,3) default 0.000
- resultado: aprobado|reprobado
- hash_verificacion: Integridad de datos

Geolocalización:
- latitud/longitud: DECIMAL(10,8)/(11,8)
- direccion_geocodificada

Evidencia:
- foto_evidencia: VARCHAR(255)
- observaciones: TEXT

Datos Ambientales:
- temperatura_ambiente
- humedad_ambiente

Sincronización:
- sync_movil: boolean
- dispositivo_movil: identificador
```
**Estado Actual**: Tabla vacía (sin pruebas registradas)

### 5. GESTIÓN DE VEHÍCULOS Y CONDUCTORES

#### 📌 tb_vehiculos
**Propósito**: Flota vehicular
```sql
Estados:
- activo
- mantenimiento
- inactivo
```
**Vehículos Demo**: 2 vehículos (Toyota Hilux, Nissan Frontier)

#### 📌 tb_licencias_conductor
**Propósito**: Licencias de conducir
```sql
Estados:
- vigente
- vencida
- suspendida
```
**Licencias Demo**: 1 licencia vigente

### 6. SISTEMA DE NOTIFICACIONES

#### 📌 tb_alertas
**Propósito**: Configuración de alertas automáticas
```sql
Tipos:
- calibracion
- prueba_reprobada
- vencimiento_licencia
- pago

Acciones:
- email
- push
- ambas
```
**Alertas Configuradas**: 2 (calibración próxima, prueba reprobada)

#### 📌 tb_notificaciones
**Propósito**: Notificaciones enviadas
```sql
Tipos:
- sistema
- alerta
- recordatorio
- pago
```

### 7. AUDITORÍA Y SEGURIDAD

#### 📌 tb_auditoria
**Propósito**: Log de todas las acciones del sistema
```sql
Registra:
- accion
- tabla_afectada
- valores_anteriores (JSON)
- valores_nuevos (JSON)
- ip_address
- user_agent
```

#### 📌 tb_sesiones
**Propósito**: Control de sesiones activas
```sql
Incluye:
- token_sesion (UNIQUE)
- dispositivo
- ip_address
- fecha_expiracion
```

### 8. CONFIGURACIÓN Y PERSONALIZACIÓN

#### 📌 tb_configuraciones
**Propósito**: Configuración por cliente
```sql
Parámetros:
- limite_alcohol_permisible: 0.000 default
- requerir_geolocalizacion: true default
- requerir_foto_evidencia: false default
- notificaciones_email/push
- timezone: America/Lima
- idioma: es|en
```

#### 📌 system_config
**Propósito**: Configuración global del sistema
```sql
Tipos de configuración:
- string
- number
- boolean
- json
```

### 9. FACTURACIÓN

#### 📌 tb_facturacion
**Propósito**: Control de pagos y suscripciones
```sql
Estados de Pago:
- pendiente
- pagado
- vencido
- cancelado
```

### 10. DOCUMENTACIÓN Y REPORTES

#### 📌 tb_documentos
**Propósito**: Almacenamiento de documentos
```sql
Tipos:
- manual
- procedimiento
- contrato
- certificado
- otro
```

#### 📌 tb_reportes
**Propósito**: Reportes generados
```sql
Tipos:
- conductor
- alcoholimetro
- general
- personalizado
- legal

Estados:
- generando
- completado
- error
```

### 11. GESTIÓN OPERACIONAL

#### 📌 tb_turnos
**Propósito**: Turnos de trabajo
```sql
Configuración:
- hora_inicio/hora_fin
- dias_semana: "1,2,3,4,5" (L-V)
```
**Turnos Demo**: Mañana (6-14h), Tarde (14-22h)

#### 📌 tb_mantenimientos
**Propósito**: Mantenimientos de alcoholímetros
```sql
Tipos:
- calibracion
- limpieza
- reparacion
- general

Estados:
- programado
- en_proceso
- completado
- cancelado
```

### 12. INTEGRACIÓN MÓVIL

#### 📌 tb_dispositivos_moviles
**Propósito**: Dispositivos móviles registrados
```sql
Estados:
- activo
- inactivo
- bloqueado

Incluye:
- token_firebase: Para push notifications
- ultima_conexion
- app_version
```

## 🔗 RELACIONES PRINCIPALES (FOREIGN KEYS)

### Relaciones Multi-Tenant (cliente_id)
```
tb_clientes (1) --> (N) tb_usuarios
tb_clientes (1) --> (N) tb_alcoholimetros
tb_clientes (1) --> (N) tb_vehiculos
tb_clientes (1) --> (N) tb_pruebas
tb_clientes (1) --> (N) tb_alertas
tb_clientes (1) --> (N) tb_configuraciones
tb_clientes (1) --> (N) tb_notificaciones
tb_clientes (1) --> (N) tb_reportes
tb_clientes (1) --> (N) tb_turnos
tb_clientes (1) --> (N) tb_dispositivos_moviles
tb_clientes (1) --> (N) tb_documentos
```

### Relaciones de Pruebas (Core)
```
tb_pruebas --> tb_alcoholimetros (dispositivo usado)
tb_pruebas --> tb_usuarios (conductor)
tb_pruebas --> tb_usuarios (supervisor)
tb_pruebas --> tb_vehiculos (opcional)
tb_pruebas --> tb_clientes
```

### Relaciones de Planes y Facturación
```
tb_clientes --> tb_planes
tb_facturacion --> tb_clientes
tb_facturacion --> tb_planes
```

## 🎯 CARACTERÍSTICAS TÉCNICAS IDENTIFICADAS

### 1. Seguridad
- ✅ Passwords hasheados con bcrypt
- ✅ Tokens de API únicos por cliente
- ✅ Sistema de auditoría completo
- ✅ Control de sesiones
- ✅ Hash de verificación en pruebas

### 2. Multi-tenancy
- ✅ Aislamiento por cliente_id
- ✅ Límites configurables por plan
- ✅ Modo demo disponible

### 3. Geolocalización
- ✅ Coordenadas GPS en pruebas
- ✅ Geocodificación de direcciones
- ✅ Configuración requerir_geolocalizacion

### 4. Sincronización Móvil
- ✅ Campo sync_movil en pruebas
- ✅ Registro de dispositivos móviles
- ✅ Token Firebase para push

### 5. Trazabilidad
- ✅ Auditoría completa
- ✅ Hash de verificación
- ✅ Timestamps en todas las tablas

### 6. Datos Ambientales
- ✅ Temperatura ambiente
- ✅ Humedad ambiente
- ℹ️ Importante para calibración

## 🚨 OBSERVACIONES Y RECOMENDACIONES

### ⚠️ Problemas Detectados

1. **Inconsistencia de Collation**
   - Mezcla de latin1_swedish_ci y utf8mb4_unicode_ci
   - Recomendación: Unificar a utf8mb4_unicode_ci

2. **Tabla Legacy**
   - Tabla `users` parece duplicar funcionalidad con `tb_usuarios`
   - Considerar consolidación o eliminación

3. **Sin Datos de Pruebas**
   - La tabla core `tb_pruebas` está vacía
   - Necesario crear datos de prueba para testing

### ✅ Puntos Fuertes

1. **Estructura Multi-tenant Robusta**
2. **Sistema de Auditoría Completo**
3. **Modelo de Suscripción SaaS bien definido**
4. **Soporte para Geolocalización y Evidencia**
5. **Preparado para Sincronización Móvil**

### 🔄 Mejoras Sugeridas

1. **Índices Adicionales**
   - Añadir índice en tb_pruebas.fecha_prueba para reportes
   - Índice en tb_auditoria.accion para búsquedas

2. **Campos Adicionales Sugeridos**
   ```sql
   tb_pruebas:
   - firma_digital_conductor
   - firma_digital_supervisor
   - video_evidencia (opcional)
   
   tb_alcoholimetros:
   - firmware_version
   - ultima_actualizacion
   - conectividad (bluetooth/usb/wifi)
   ```

3. **Normalización**
   - Considerar tabla separada para coordenadas GPS
   - Tabla para tipos de prueba (pre-operacional, aleatorio, etc.)

## 📊 ESTADÍSTICAS DE DATOS ACTUALES

```
✅ Con Datos:
- tb_clientes: 1 registro (Demo)
- tb_planes: 4 planes configurados
- tb_usuarios: 3 usuarios demo
- tb_alcoholimetros: 2 dispositivos
- tb_vehiculos: 2 vehículos
- tb_turnos: 2 turnos
- tb_alertas: 2 alertas configuradas
- tb_configuraciones: 1 configuración
- tb_licencias_conductor: 1 licencia
- users: 3 usuarios sistema

⚠️ Sin Datos (Vacías):
- tb_pruebas (CRÍTICO - tabla principal)
- tb_auditoria
- tb_dispositivos_moviles
- tb_documentos
- tb_facturacion
- tb_mantenimientos
- tb_notificaciones
- tb_reportes
- tb_sesiones
- system_config
```

## 🎯 PRÓXIMOS PASOS RECOMENDADOS

1. **Inmediato**:
   - Crear datos de prueba para `tb_pruebas`
   - Unificar collation de base de datos
   - Definir uso de tabla `users` vs `tb_usuarios`

2. **Corto Plazo**:
   - Implementar API REST basada en esta estructura
   - Crear seeders para datos de desarrollo
   - Implementar triggers para auditoría automática

3. **Mediano Plazo**:
   - Optimizar índices para consultas frecuentes
   - Implementar particionamiento para tb_pruebas
   - Añadir campos para blockchain/inmutabilidad

## 💾 SCRIPT DE MIGRACIÓN SUGERIDO

```sql
-- Unificar collation
ALTER DATABASE juegosd2_alcoholimetro 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Añadir índices de rendimiento
CREATE INDEX idx_pruebas_fecha_resultado 
ON tb_pruebas(fecha_prueba, resultado);

CREATE INDEX idx_auditoria_accion_fecha 
ON tb_auditoria(accion, fecha_accion);

-- Trigger de auditoría ejemplo
DELIMITER $$
CREATE TRIGGER tr_pruebas_audit
AFTER INSERT ON tb_pruebas
FOR EACH ROW
BEGIN
    INSERT INTO tb_auditoria (
        cliente_id, usuario_id, accion, 
        tabla_afectada, registro_id
    ) VALUES (
        NEW.cliente_id, NEW.supervisor_id, 
        'INSERT', 'tb_pruebas', NEW.id
    );
END$$
DELIMITER ;
```

---

**Documento generado para mantener contexto entre sesiones**
**Última actualización**: Noviembre 24, 2024
**Versión**: 1.0.0