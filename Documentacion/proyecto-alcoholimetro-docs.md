# 🚗 Sistema Integral de Alcoholímetros
## Documentación Completa del Proyecto

---

## 📋 Descripción General

Sistema completo para gestión, monitoreo y control de pruebas de alcoholimetría, diseñado para empresas de transporte, instituciones gubernamentales o empresas que requieran control de alcohol en sus operaciones.

## 🎯 Objetivos del Sistema

1. **Gestión de Pruebas**: Registro y control de todas las pruebas de alcoholimetría
2. **Monitoreo en Tiempo Real**: Dashboard para supervisión en vivo
3. **Reportes y Analytics**: Estadísticas y reportes detallados
4. **App Móvil**: Para operadores en campo
5. **Integración IoT**: Conexión con dispositivos alcoholímetros
6. **Cumplimiento Legal**: Documentación según normativas

## 🏗️ Arquitectura del Sistema

```
┌─────────────────────────────────────────────────────────┐
│                    FRONTEND WEB                         │
│         React + TypeScript + Material-UI                │
└────────────────────┬────────────────────────────────────┘
                     │
┌────────────────────▼────────────────────────────────────┐
│                    API GATEWAY                          │
│              Node.js + Express + JWT                    │
└────────────────────┬────────────────────────────────────┘
                     │
     ┌───────────────┼───────────────┬──────────────┐
     │               │               │              │
┌────▼────┐    ┌────▼────┐    ┌────▼────┐    ┌────▼────┐
│ Auth    │    │ Pruebas │    │Reportes │    │ IoT     │
│ Service │    │ Service │    │ Service │    │ Service │
└─────────┘    └─────────┘    └─────────┘    └─────────┘
     │               │               │              │
     └───────────────┼───────────────┴──────────────┘
                     │
┌────────────────────▼────────────────────────────────────┐
│                   BASE DE DATOS                         │
│              PostgreSQL + Redis Cache                   │
└──────────────────────────────────────────────────────────┘
```

## 💻 Stack Tecnológico

### Backend
- **Runtime**: Node.js 20 LTS
- **Framework**: Express.js / NestJS
- **Base de Datos**: PostgreSQL
- **Cache**: Redis
- **ORM**: Prisma / TypeORM
- **Autenticación**: JWT + Passport.js
- **WebSocket**: Socket.io (tiempo real)
- **Queue**: Bull (procesamiento asíncrono)

### Frontend Web
- **Framework**: React 18 + TypeScript
- **UI Library**: Material-UI v5
- **State Management**: Redux Toolkit / Zustand
- **Gráficos**: Recharts / Chart.js
- **Forms**: React Hook Form + Yup
- **HTTP Client**: Axios + React Query

### App Móvil
- **Framework**: React Native / Flutter
- **State**: Redux Persist
- **Navigation**: React Navigation v6
- **Camera**: React Native Camera
- **Bluetooth**: React Native BLE
- **Maps**: React Native Maps

### DevOps
- **Containers**: Docker + Docker Compose
- **CI/CD**: GitHub Actions
- **Monitoring**: Prometheus + Grafana
- **Logging**: Winston + ELK Stack

## 📂 Estructura de Directorios

```
sistema-alcoholimetros/
│
├── 📁 backend/
│   ├── src/
│   │   ├── modules/
│   │   │   ├── auth/
│   │   │   ├── tests/
│   │   │   ├── users/
│   │   │   ├── devices/
│   │   │   ├── reports/
│   │   │   └── notifications/
│   │   ├── common/
│   │   │   ├── guards/
│   │   │   ├── filters/
│   │   │   ├── pipes/
│   │   │   └── interceptors/
│   │   ├── database/
│   │   │   ├── migrations/
│   │   │   ├── seeds/
│   │   │   └── schemas/
│   │   ├── config/
│   │   └── main.ts
│   ├── tests/
│   ├── package.json
│   └── Dockerfile
│
├── 📁 frontend/
│   ├── src/
│   │   ├── components/
│   │   │   ├── common/
│   │   │   ├── dashboard/
│   │   │   ├── reports/
│   │   │   └── tests/
│   │   ├── pages/
│   │   ├── services/
│   │   ├── hooks/
│   │   ├── store/
│   │   ├── utils/
│   │   └── App.tsx
│   ├── public/
│   ├── package.json
│   └── Dockerfile
│
├── 📁 mobile/
│   ├── src/
│   │   ├── screens/
│   │   ├── components/
│   │   ├── navigation/
│   │   ├── services/
│   │   ├── store/
│   │   └── utils/
│   ├── android/
│   ├── ios/
│   └── package.json
│
├── 📁 iot-integration/
│   ├── drivers/
│   ├── protocols/
│   └── README.md
│
├── 📁 docs/
│   ├── api/
│   ├── user-manual/
│   └── technical/
│
├── 📁 scripts/
│   ├── setup.sh
│   ├── deploy.sh
│   └── backup.sh
│
├── docker-compose.yml
├── .env.example
├── README.md
└── LICENSE
```

## 🗄️ Modelo de Base de Datos

### Tablas Principales

```sql
-- Usuarios del Sistema
CREATE TABLE users (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    role ENUM('admin', 'supervisor', 'operator') NOT NULL,
    company_id UUID REFERENCES companies(id),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Conductores/Personas a Evaluar
CREATE TABLE drivers (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    license_number VARCHAR(50) UNIQUE NOT NULL,
    document_type VARCHAR(20) NOT NULL,
    document_number VARCHAR(50) UNIQUE NOT NULL,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    birth_date DATE NOT NULL,
    phone VARCHAR(20),
    email VARCHAR(255),
    company_id UUID REFERENCES companies(id),
    photo_url VARCHAR(500),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Dispositivos Alcoholímetros
CREATE TABLE devices (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    serial_number VARCHAR(100) UNIQUE NOT NULL,
    model VARCHAR(100) NOT NULL,
    manufacturer VARCHAR(100) NOT NULL,
    calibration_date DATE NOT NULL,
    next_calibration DATE NOT NULL,
    location_id UUID REFERENCES locations(id),
    status ENUM('active', 'maintenance', 'calibration', 'inactive') DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Pruebas de Alcoholimetría
CREATE TABLE alcohol_tests (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    test_number VARCHAR(50) UNIQUE NOT NULL,
    driver_id UUID REFERENCES drivers(id) NOT NULL,
    device_id UUID REFERENCES devices(id) NOT NULL,
    operator_id UUID REFERENCES users(id) NOT NULL,
    location_id UUID REFERENCES locations(id) NOT NULL,
    test_datetime TIMESTAMP NOT NULL,
    alcohol_level DECIMAL(5,3) NOT NULL, -- mg/L o g/L
    result ENUM('passed', 'failed', 'invalid') NOT NULL,
    test_type ENUM('pre_trip', 'random', 'post_incident', 'reasonable_suspicion') NOT NULL,
    notes TEXT,
    photo_proof_url VARCHAR(500),
    signature_url VARCHAR(500),
    gps_latitude DECIMAL(10, 8),
    gps_longitude DECIMAL(11, 8),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Empresas/Organizaciones
CREATE TABLE companies (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    name VARCHAR(255) NOT NULL,
    tax_id VARCHAR(50) UNIQUE NOT NULL,
    address TEXT,
    phone VARCHAR(20),
    email VARCHAR(255),
    contact_person VARCHAR(255),
    subscription_type ENUM('basic', 'professional', 'enterprise') DEFAULT 'basic',
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ubicaciones/Puntos de Control
CREATE TABLE locations (
    id UUID PRIMARY KEY DEFAULT gen_random_uuid(),
    company_id UUID REFERENCES companies(id),
    name VARCHAR(255) NOT NULL,
    address TEXT,
    gps_latitude DECIMAL(10, 8),
    gps_longitude DECIMAL(11, 8),
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

## 🔧 APIs Principales

### Autenticación
```typescript
POST   /api/auth/login
POST   /api/auth/refresh
POST   /api/auth/logout
POST   /api/auth/forgot-password
POST   /api/auth/reset-password
```

### Gestión de Pruebas
```typescript
GET    /api/tests                 // Listar pruebas con filtros
POST   /api/tests                 // Crear nueva prueba
GET    /api/tests/:id            // Obtener detalle de prueba
PUT    /api/tests/:id            // Actualizar prueba
DELETE /api/tests/:id            // Eliminar prueba
GET    /api/tests/stats          // Estadísticas
POST   /api/tests/:id/upload     // Subir foto/firma
```

### Conductores
```typescript
GET    /api/drivers              // Listar conductores
POST   /api/drivers              // Registrar conductor
GET    /api/drivers/:id         // Obtener conductor
PUT    /api/drivers/:id         // Actualizar conductor
GET    /api/drivers/:id/history // Historial de pruebas
```

### Dispositivos
```typescript
GET    /api/devices              // Listar dispositivos
POST   /api/devices              // Registrar dispositivo
PUT    /api/devices/:id         // Actualizar dispositivo
POST   /api/devices/:id/calibrate // Registrar calibración
GET    /api/devices/:id/status  // Estado en tiempo real
```

### Reportes
```typescript
GET    /api/reports/daily       // Reporte diario
GET    /api/reports/monthly     // Reporte mensual
GET    /api/reports/drivers     // Reporte por conductor
GET    /api/reports/compliance  // Cumplimiento normativo
POST   /api/reports/export      // Exportar a PDF/Excel
```

## 📱 Funcionalidades de la App Móvil

### Para Operadores
1. **Login Seguro**: Autenticación biométrica opcional
2. **Escaneo QR/Barcode**: Para licencias de conducir
3. **Captura de Prueba**:
   - Conexión Bluetooth con alcoholímetro
   - Lectura automática de resultado
   - Foto del conductor
   - Firma digital
   - GPS automático
4. **Modo Offline**: Sincronización cuando hay conexión
5. **Historial**: Ver pruebas anteriores

### Para Supervisores
1. **Dashboard Móvil**: Estadísticas en tiempo real
2. **Alertas**: Notificaciones de pruebas fallidas
3. **Reportes Rápidos**: Generación instantánea
4. **Gestión de Operadores**: Asignación de tareas

## 🎨 Interfaces de Usuario (Web)

### 1. Dashboard Principal
- Widgets de estadísticas en tiempo real
- Gráfico de pruebas del día
- Alertas y notificaciones
- Mapa de ubicaciones activas

### 2. Módulo de Pruebas
- Tabla con filtros avanzados
- Vista detallada de cada prueba
- Formulario de nueva prueba
- Exportación de datos

### 3. Gestión de Conductores
- Listado con búsqueda
- Perfil completo del conductor
- Historial de pruebas
- Estadísticas individuales

### 4. Centro de Reportes
- Generador de reportes personalizados
- Plantillas predefinidas
- Programación de envío automático
- Visualizaciones interactivas

### 5. Configuración
- Gestión de usuarios
- Configuración de dispositivos
- Parámetros del sistema
- Integración con otros sistemas

## 🔐 Seguridad

### Medidas Implementadas
1. **Autenticación JWT** con refresh tokens
2. **Encriptación** de datos sensibles (AES-256)
3. **HTTPS** obligatorio en producción
4. **Rate Limiting** para prevenir ataques
5. **Validación** de entrada en todos los endpoints
6. **Logs de Auditoría** para todas las acciones
7. **Backup Automático** diario
8. **RBAC** (Role-Based Access Control)

## 📊 Características Avanzadas

### 1. Integración IoT
- Protocolo MQTT para dispositivos
- Actualización OTA (Over-The-Air)
- Monitoreo de salud del dispositivo

### 2. Machine Learning
- Predicción de patrones de riesgo
- Detección de anomalías
- Optimización de rutas de control

### 3. Blockchain (Opcional)
- Registro inmutable de pruebas
- Cadena de custodia digital
- Smart contracts para cumplimiento

### 4. Integraciones
- ERP (SAP, Oracle)
- Sistemas de RRHH
- Plataformas de transporte
- APIs gubernamentales

## 🚀 Plan de Desarrollo

### Fase 1: MVP (4-6 semanas)
- [ ] Setup del proyecto
- [ ] Autenticación básica
- [ ] CRUD de pruebas
- [ ] Dashboard simple
- [ ] App móvil básica

### Fase 2: Funcionalidades Core (6-8 semanas)
- [ ] Sistema completo de reportes
- [ ] Integración con dispositivos
- [ ] Modo offline
- [ ] Notificaciones push
- [ ] Gestión avanzada de usuarios

### Fase 3: Características Avanzadas (8-10 semanas)
- [ ] Analytics avanzado
- [ ] Machine Learning
- [ ] Integraciones externas
- [ ] Optimización de rendimiento
- [ ] Testing exhaustivo

### Fase 4: Producción (2-4 semanas)
- [ ] Deployment en cloud
- [ ] Configuración de CI/CD
- [ ] Monitoreo y alertas
- [ ] Documentación completa
- [ ] Capacitación de usuarios

## 🧪 Testing

### Estrategia de Pruebas
```
- Unit Tests: 80% cobertura mínima
- Integration Tests: APIs críticas
- E2E Tests: Flujos principales
- Performance Tests: Carga y estrés
- Security Tests: Penetration testing
```

## 📈 KPIs del Sistema

1. **Disponibilidad**: 99.9% uptime
2. **Rendimiento**: <200ms respuesta promedio
3. **Escalabilidad**: 10,000 pruebas/día
4. **Precisión**: 100% en registro de datos
5. **Seguridad**: 0 brechas de seguridad

## 🌍 Consideraciones Legales

### Cumplimiento Normativo
- GDPR / Ley de Protección de Datos
- Normativas de transporte local
- Estándares de alcoholimetría
- Certificaciones requeridas

### Documentación Legal
- Consentimiento informado
- Cadena de custodia
- Reportes oficiales
- Audit trails

## 💰 Modelo de Negocio

### Planes de Suscripción

#### Plan Básico
- Hasta 100 pruebas/mes
- 1 ubicación
- 5 usuarios
- Reportes básicos

#### Plan Profesional
- Hasta 1,000 pruebas/mes
- 5 ubicaciones
- 25 usuarios
- Reportes avanzados
- API access

#### Plan Enterprise
- Pruebas ilimitadas
- Ubicaciones ilimitadas
- Usuarios ilimitados
- Personalización completa
- Soporte dedicado

## 📞 Soporte y Mantenimiento

### SLA (Service Level Agreement)
- Soporte 24/7 para Enterprise
- Horario laboral para otros planes
- Tiempo de respuesta: 2-24 horas
- Actualizaciones mensuales
- Backup diario automático

## 🎯 Próximos Pasos

1. **Validar Requerimientos**: Confirmar funcionalidades con stakeholders
2. **Diseño UI/UX**: Crear mockups y prototipos
3. **Setup Inicial**: Configurar repositorio y herramientas
4. **Desarrollo Iterativo**: Sprints de 2 semanas
5. **Testing Continuo**: QA en cada sprint
6. **Deployment Gradual**: Staging → Beta → Producción

---

## 📚 Referencias y Recursos

- [Documentación de React](https://reactjs.org/docs)
- [Node.js Best Practices](https://github.com/goldbergyoni/nodebestpractices)
- [PostgreSQL Documentation](https://www.postgresql.org/docs/)
- [React Native Documentation](https://reactnative.dev/docs/getting-started)
- [Docker Documentation](https://docs.docker.com/)
- [OWASP Security Guidelines](https://owasp.org/www-project-top-ten/)

---

**Última actualización**: Noviembre 2024
**Versión**: 1.0.0
**Autor**: Sistema de Documentación Automática