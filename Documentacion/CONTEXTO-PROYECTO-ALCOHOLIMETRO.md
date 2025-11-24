# 🔄 CONTEXTO DEL PROYECTO - SISTEMA INTEGRAL DE ALCOHOLÍMETROS
## Archivo de Continuidad para Claude

---

## 📋 RESUMEN EJECUTIVO

**Proyecto**: Sistema Integral de Alcoholímetros + App
**Cliente**: RocotoDigital
**GitHub**: https://github.com/rocotodigitalcom-collab/Sistema-Integral-de-Alcoholimetros
**Estado**: En desarrollo inicial
**Base de Datos**: MariaDB con 21 tablas, estructura multi-tenant

## 🎯 OBJETIVO DEL SISTEMA

Desarrollar una plataforma SaaS completa para gestión de pruebas de alcoholimetría que incluye:
- Sistema web administrativo
- App móvil para operadores
- Integración con dispositivos alcoholímetros
- Reportes y cumplimiento normativo
- Modelo de suscripción por planes

## 🏗️ ARQUITECTURA ACTUAL

### Base de Datos Existente
- **Motor**: MariaDB 10.11.13
- **Nombre BD**: juegosd2_alcoholimetro
- **Tablas**: 21 tablas con prefijo `tb_` + 2 tablas adicionales
- **Modelo**: Multi-tenant con aislamiento por `cliente_id`

### Estructura Multi-Tenant
```
Cada cliente (empresa) tiene:
- Sus propios usuarios
- Sus alcoholímetros
- Sus vehículos
- Sus pruebas
- Sus configuraciones
- Plan de suscripción con límites
```

### Planes de Suscripción Configurados
1. **Básico**: $49/mes - 500 pruebas
2. **Profesional**: $99/mes - 2000 pruebas  
3. **Empresarial**: $199/mes - 10000 pruebas
4. **Prueba**: $0/mes - 100 pruebas (demo)

## 📊 MÓDULOS DEL SISTEMA

### ✅ Módulos con Estructura en BD
1. **Gestión de Clientes** (tb_clientes)
2. **Gestión de Usuarios** (tb_usuarios) - 5 roles
3. **Gestión de Alcoholímetros** (tb_alcoholimetros)
4. **Registro de Pruebas** (tb_pruebas) - CORE
5. **Gestión de Vehículos** (tb_vehiculos)
6. **Sistema de Alertas** (tb_alertas)
7. **Auditoría** (tb_auditoria)
8. **Facturación** (tb_facturacion)
9. **Configuraciones** (tb_configuraciones)
10. **Dispositivos Móviles** (tb_dispositivos_moviles)
11. **Mantenimientos** (tb_mantenimientos)
12. **Documentos** (tb_documentos)
13. **Reportes** (tb_reportes)
14. **Notificaciones** (tb_notificaciones)
15. **Turnos** (tb_turnos)
16. **Sesiones** (tb_sesiones)
17. **Licencias** (tb_licencias_conductor)

### 🔄 Por Desarrollar
- [ ] API REST
- [ ] Frontend Web (React + TypeScript)
- [ ] App Móvil (React Native)
- [ ] Integración IoT con alcoholímetros
- [ ] Sistema de reportes
- [ ] Dashboard analytics
- [ ] Notificaciones push
- [ ] Sincronización offline

## 💾 DATOS DE PRUEBA EXISTENTES

```yaml
Cliente Demo:
  - Empresa: "Empresa Demo SA"
  - RUC: 20123456789
  - Plan: Prueba (15 días)
  - Estado: modo_demo = true

Usuarios Demo:
  - Admin: admin@empresademo.com / password
  - Conductor: conductor@empresademo.com / password
  - Supervisor: supervisor@empresademo.com / password

Recursos:
  - 2 Alcoholímetros (ALC-001, ALC-002)
  - 2 Vehículos (Toyota Hilux, Nissan Frontier)
  - 2 Turnos (Mañana 6-14h, Tarde 14-22h)
  - 2 Alertas configuradas
```

## 🔐 CARACTERÍSTICAS DE SEGURIDAD IMPLEMENTADAS

1. **Autenticación**:
   - Passwords con hash bcrypt ($2y$10$)
   - Tokens de sesión únicos
   - Token API por cliente

2. **Auditoría**:
   - Registro de todas las acciones
   - IP y User Agent tracking
   - Valores anteriores/nuevos en cambios

3. **Integridad**:
   - Hash de verificación en pruebas
   - Foreign keys en todas las relaciones
   - Campos de timestamp automáticos

## 🚀 STACK TECNOLÓGICO PROPUESTO

### Backend
- **Runtime**: Node.js 20 LTS
- **Framework**: Express.js + TypeScript
- **ORM**: Prisma (por configurar con BD existente)
- **Auth**: JWT + Passport.js
- **WebSocket**: Socket.io
- **Cache**: Redis
- **Queue**: Bull

### Frontend
- **Framework**: React 18 + TypeScript
- **UI**: Material-UI v5
- **State**: Redux Toolkit
- **Charts**: Recharts
- **Forms**: React Hook Form

### Mobile
- **Framework**: React Native
- **State**: Redux Persist
- **Maps**: React Native Maps
- **Bluetooth**: React Native BLE

### DevOps
- **Containers**: Docker
- **CI/CD**: GitHub Actions
- **Cloud**: Por definir (AWS/GCP/Azure)

## 📝 TAREAS INMEDIATAS

### Prioridad Alta
1. ⚠️ Crear datos de prueba en `tb_pruebas` (tabla vacía)
2. ⚠️ Unificar collation de BD a utf8mb4_unicode_ci
3. ⚠️ Decidir sobre tabla `users` vs `tb_usuarios`

### Siguiente Sprint
1. Configurar Prisma con BD existente
2. Crear API REST básica
3. Implementar autenticación JWT
4. Crear endpoints CRUD para pruebas
5. Desarrollar dashboard inicial

## 🎨 DECISIONES DE DISEÑO TOMADAS

1. **Multi-tenancy**: Por `cliente_id` (row-level)
2. **Roles**: 5 niveles (admin, supervisor, conductor, tecnico, auditor)
3. **Geolocalización**: Requerida por defecto
4. **Evidencia**: Foto opcional, configurable
5. **Límites**: Por plan de suscripción
6. **Sincronización**: Soporte offline planificado

## ⚡ ENDPOINTS API PLANIFICADOS

```typescript
// Autenticación
POST   /api/auth/login
POST   /api/auth/refresh
POST   /api/auth/logout

// Pruebas (CORE)
GET    /api/tests
POST   /api/tests
GET    /api/tests/:id
PUT    /api/tests/:id
DELETE /api/tests/:id

// Conductores
GET    /api/drivers
POST   /api/drivers
GET    /api/drivers/:id
GET    /api/drivers/:id/history

// Alcoholímetros
GET    /api/devices
POST   /api/devices
PUT    /api/devices/:id
POST   /api/devices/:id/calibrate

// Reportes
GET    /api/reports/daily
GET    /api/reports/monthly
POST   /api/reports/export
```

## 🔧 CONFIGURACIÓN DE DESARROLLO

### Variables de Entorno Necesarias
```env
# Base de Datos
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=juegosd2_alcoholimetro
DB_USERNAME=
DB_PASSWORD=

# JWT
JWT_SECRET=
JWT_EXPIRES_IN=7d

# App
APP_PORT=3000
APP_ENV=development
APP_URL=http://localhost:3000

# Redis
REDIS_HOST=localhost
REDIS_PORT=6379

# Firebase (Push Notifications)
FIREBASE_PROJECT_ID=
FIREBASE_PRIVATE_KEY=
FIREBASE_CLIENT_EMAIL=
```

## 📚 DOCUMENTACIÓN GENERADA

1. **proyecto-alcoholimetro-docs.md**: Documentación completa del proyecto
2. **analisis-database-alcoholimetro.md**: Análisis detallado de la BD
3. **backend-package.json**: Dependencias del backend
4. **ESTE ARCHIVO**: Contexto para continuidad

## 🎯 CÓMO USAR ESTE CONTEXTO

**En tu próxima conversación con Claude, simplemente comparte este archivo y di:**

> "Aquí está el contexto de mi proyecto de alcoholímetros. Continuamos trabajando en [especifica la tarea]"

**Ejemplo:**
> "Aquí está el contexto de mi proyecto. Necesito crear los endpoints de la API para el módulo de pruebas"

## 📌 NOTAS IMPORTANTES

1. **Base de Datos**: Ya existe y tiene estructura completa
2. **Datos Demo**: Ya configurados para testing
3. **GitHub**: Repositorio creado pero vacío
4. **Prioridad**: Sistema de pruebas (tb_pruebas) es el core
5. **Modelo de Negocio**: SaaS con planes mensuales

## 🔄 HISTORIAL DE SESIONES

### Sesión 1 (Nov 24, 2024)
- ✅ Análisis completo de base de datos
- ✅ Documentación del proyecto
- ✅ Creación de archivos de contexto
- ✅ Identificación de estructura multi-tenant
- ✅ Plan de desarrollo propuesto

### Próximas Sesiones
- [ ] Configuración de backend con Express
- [ ] Integración de Prisma con BD existente
- [ ] Creación de API REST
- [ ] Desarrollo de autenticación
- [ ] Frontend inicial

---

**🔑 PALABRAS CLAVE PARA BÚSQUEDA:**
alcoholimetro, breathalyzer, multi-tenant, SaaS, pruebas, RocotoDigital, MariaDB, Node.js, React, React Native

**📅 Última Actualización**: Noviembre 24, 2024
**🏷️ Versión**: 1.0.0
**👤 Desarrollador**: Sistema en colaboración con Claude