# 📊 ANÁLISIS DEL ARCHIVO ALCOLOCK - MAPEO DE DATOS
## Estructura de Datos Existente y Migración

---

## 🔍 RESUMEN DEL ARCHIVO EXCEL

El archivo **Alcolock_AWI__Claudia_Hau__vfernando.xlsx** contiene 25 hojas con información operacional completa:

### 📋 HOJAS IDENTIFICADAS Y SU PROPÓSITO

1. **ALC** (503 registros) - Alcoholímetros
   - ID, Serie, Estado, Marca, Modelo
   - Última calibración, Próxima calibración
   - Ubicación, Operación, Líder de operación

2. **VEH** (503 registros) - Vehículos
   - ID, Placa, Marca, Modelo
   - Tipo de vehículo, Operación
   - Usuario asignado (TRANSALTISA)

3. **COND** (503 registros) - Conductores
   - ID, Nombre, Licencia, Categoría
   - Estado, Operación asignada

4. **OPE** (103 registros) - Operaciones
   - ID, Nombre operación
   - Ruta (Lima Mina Lima, Lima Puerto Lima)
   - Zona (Centro, Sur, etc.)

5. **ANIO** - Años de operación (2021-2024)

6. **CAL 1-4** - Registros de calibración por año
   - Fechas de calibración
   - Certificados
   - Técnico responsable

7. **MAN 1-4** - Registros de mantenimiento por año
   - Tipo de mantenimiento
   - Fechas
   - Costos

8. **CAL TOTAL** (406 registros) - Historial completo de calibraciones

9. **MANT TOTAL** (407 registros) - Historial completo de mantenimientos

10. **INC FLOTA** (6830 registros) - Incidencias de flota
    - Cliente: 0001 (TRANSALTISA)
    - Eventos por vehículo
    - Periodo de selección

11. **DASH** - Configuración de dashboard
    - Estados de alcoholímetros
    - Métricas principales

12. **CONDUCTOR** - Estadísticas por conductor
    - Puntaje (1-5)
    - Viajes realizados
    - Duración de operación
    - Eventos de respaldo

13. **HANDSET** - Dispositivos móviles
    - Serie: LE0087691
    - Líder asignado: Alexandra
    - Estadísticas de uso

14. **OPERACION** - Métricas por operación
    - MINA 1 con puntaje 5.0
    - Líder: Alexandra Escudero

15. **TIPOS DE MANTENIMIENTO**
    ```
    PREVENTIVO:
    - Limpieza
    - Lubricación
    - Otros
    
    CORRECTIVO:
    - Cambio de bomba
    - Cambio de placa
    - Cambio de Batería LI
    - Cambio de carcasa
    - Otros
    ```

16. **EVENTOS** - Clasificación de eventos
    - Leve
    - Normal
    - Grave
    - Muy grave

17. **EVENTOS CRITICOS** - Registro de eventos críticos

18. **VIAJES** - Registro de viajes

---

## 🔄 MAPEO CON NUESTRA BASE DE DATOS

### Tabla de Correspondencia

| Excel Sheet | Nuestra Tabla | Campos a Migrar |
|------------|---------------|-----------------|
| **ALC** | `tb_alcoholimetros` | numero_serie, modelo, marca, estado, fecha_calibracion |
| **VEH** | `tb_vehiculos` | placa, marca, modelo, estado |
| **COND** | `tb_usuarios` (rol='conductor') | nombre, dni, licencia |
| **OPE** | Nueva: `tb_operaciones` | nombre, ruta, zona |
| **CAL TOTAL** | `tb_mantenimientos` (tipo='calibracion') | fecha, certificado, siguiente |
| **MANT TOTAL** | `tb_mantenimientos` | tipo, fecha, descripcion, costo |
| **INC FLOTA** | `tb_pruebas` + `tb_eventos` | fecha, tipo, vehiculo_id |
| **HANDSET** | `tb_dispositivos_moviles` | device_id, usuario_asignado |

---

## 📊 DATOS IMPORTANTES IDENTIFICADOS

### 🏢 Cliente Principal
- **Nombre**: TRANSALTISA
- **Código**: 0001
- **Flota**: 503 vehículos
- **Alcoholímetros**: 503 unidades
- **Conductores**: 503 registrados

### 📍 Operaciones Activas
1. **Lima Mina Lima** - Zona Centro
2. **Lima Puerto Lima** - Zona Sur
3. **MINA 1** - Alexandra Escudero

### 👥 Personal Clave
- **Líder de Operación**: Claudia Haug
- **Supervisor**: Alexandra Escudero
- **Conductor ejemplo**: Wilder Salas (Puntaje: 5.0)

### 📈 Métricas Operacionales
- **Puntaje promedio**: 4.5/5.0
- **Calibraciones al año**: ~100
- **Mantenimientos al año**: ~100
- **Eventos registrados**: 6830

---

## 🔧 SCRIPT DE IMPORTACIÓN PROPUESTO

```javascript
// Importador de datos desde Excel a nuestro sistema

class AlcolockDataImporter {
  
  async importarDatos(excelFile) {
    const workbook = await this.leerExcel(excelFile);
    
    // 1. Crear empresa cliente
    const cliente = await this.crearCliente({
      nombre_empresa: 'TRANSALTISA',
      ruc: '20100000001', // Placeholder
      plan_id: 3, // Empresarial
      limite_usuarios: 600,
      limite_alcoholimetros: 600
    });
    
    // 2. Importar alcoholímetros
    const alcoholimetros = await this.importarAlcolimetros(
      workbook.ALC, 
      cliente.id
    );
    
    // 3. Importar vehículos
    const vehiculos = await this.importarVehiculos(
      workbook.VEH, 
      cliente.id
    );
    
    // 4. Importar conductores
    const conductores = await this.importarConductores(
      workbook.COND, 
      cliente.id
    );
    
    // 5. Importar calibraciones
    await this.importarCalibraciones(
      workbook['CAL TOTAL'], 
      alcoholimetros
    );
    
    // 6. Importar mantenimientos
    await this.importarMantenimientos(
      workbook['MANT TOTAL'], 
      alcoholimetros
    );
    
    // 7. Configurar operaciones
    await this.configurarOperaciones(
      workbook.OPE, 
      cliente.id
    );
    
    return {
      cliente,
      alcoholimetros: alcoholimetros.length,
      vehiculos: vehiculos.length,
      conductores: conductores.length
    };
  }
  
  async importarAlcolimetros(data, clienteId) {
    const alcoholimetros = [];
    
    for (const row of data) {
      if (row.ID && row.Serie) {
        const alc = await db.tb_alcoholimetros.create({
          cliente_id: clienteId,
          numero_serie: row.Serie,
          modelo: row.Modelo || 'Sin especificar',
          marca: row.Marca || 'Sin especificar',
          estado: this.mapearEstado(row.Estado),
          fecha_calibracion: row['Última calibración'],
          proxima_calibracion: row['Próxima calibración']
        });
        alcoholimetros.push(alc);
      }
    }
    
    return alcoholimetros;
  }
  
  mapearEstado(estadoExcel) {
    const mapeo = {
      'Activo': 'activo',
      'Inactivo': 'inactivo',
      'Mantenimiento': 'mantenimiento',
      'Calibración': 'calibracion'
    };
    return mapeo[estadoExcel] || 'activo';
  }
}
```

---

## 📋 CHECKLIST DE MIGRACIÓN

### Pre-Migración
- [ ] Backup de base de datos actual
- [ ] Validar formato de datos Excel
- [ ] Crear mapeo de campos personalizado
- [ ] Definir valores por defecto
- [ ] Preparar ambiente de staging

### Durante la Migración
- [ ] Importar datos maestros (empresa, usuarios)
- [ ] Importar catálogos (alcoholímetros, vehículos)
- [ ] Importar históricos (calibraciones, mantenimientos)
- [ ] Validar integridad referencial
- [ ] Generar log de importación

### Post-Migración
- [ ] Validar conteos totales
- [ ] Verificar relaciones
- [ ] Pruebas de funcionalidad
- [ ] Generar reporte de migración
- [ ] Capacitar usuarios en datos migrados

---

## 🎯 INSIGHTS DEL ANÁLISIS

### Funcionalidades Clave Identificadas

1. **Score/Puntaje de Conductores**
   - Sistema de puntuación 1-5
   - Basado en comportamiento
   - Afecta asignaciones

2. **Clasificación de Eventos**
   - 4 niveles de severidad
   - Eventos críticos separados
   - Requiere protocolo de escalamiento

3. **Gestión de Operaciones**
   - Múltiples rutas
   - Zonas geográficas
   - Líderes asignados

4. **Tracking de Handsets**
   - Dispositivos específicos
   - Asignación a líderes
   - Métricas de uso

5. **Mantenimiento Categorizado**
   - Preventivo vs Correctivo
   - Tipos específicos
   - Tracking de costos

---

## 🔮 RECOMENDACIONES PARA EL SISTEMA

### Basadas en los Datos Analizados

1. **Implementar Sistema de Scoring**
   ```javascript
   // Score automático basado en:
   - Pruebas aprobadas/reprobadas
   - Puntualidad
   - Incidentes
   - Kilometraje seguro
   ```

2. **Dashboard Multinivel**
   ```yaml
   Nivel 1 - Ejecutivo:
     - KPIs globales
     - Tendencias
   
   Nivel 2 - Operacional:
     - Por operación/ruta
     - Por líder
   
   Nivel 3 - Detalle:
     - Por conductor
     - Por vehículo
     - Por dispositivo
   ```

3. **Alertas Inteligentes**
   - Predicción de mantenimientos
   - Alertas de calibración con anticipación
   - Detección de patrones anómalos

4. **Integración con ECU**
   - Lectura de datos del vehículo
   - Bloqueo remoto si positivo
   - Telemetría en tiempo real

5. **Gestión de Flotas Grandes**
   - Bulk operations
   - Importación masiva
   - Asignaciones por lotes

---

## 📈 MÉTRICAS EXTRAÍDAS

### Volúmenes de Datos
- **Registros totales**: ~15,000
- **Promedio eventos/día**: 18-20
- **Calibraciones/año**: 100
- **Mantenimientos/año**: 100

### Patrones Identificados
- Calibración cada 365 días
- Mantenimiento preventivo trimestral
- Picos de actividad en horario 6-14h
- Mayor actividad en rutas Lima-Mina

---

## 💡 FEATURES ADICIONALES SUGERIDOS

Basados en el análisis del Excel:

1. **Módulo de Rutas**
   - Definición de rutas fijas
   - Tiempos estimados
   - Puntos de control

2. **Gestión de Handsets**
   - Inventario de dispositivos
   - Asignación dinámica
   - Tracking de uso

3. **Centro de Costos**
   - Costos por mantenimiento
   - Costos por calibración
   - ROI por dispositivo

4. **Análisis Predictivo**
   - Predicción de fallas
   - Optimización de rutas
   - Sugerencias de mantenimiento

5. **Portal del Conductor**
   - Ver su score
   - Historial personal
   - Próximas asignaciones

---

**Documento de análisis para migración de datos**
**Fecha**: Noviembre 24, 2024
**Archivo analizado**: Alcolock_AWI__Claudia_Hau__vfernando.xlsx
**Registros identificados**: ~15,000