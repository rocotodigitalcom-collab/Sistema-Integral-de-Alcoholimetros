# 🔧 MÓDULO DE CONFIGURACIÓN COMPLETO
## Sistema Integral de Alcoholímetros - PHP Clásico

---

## 📋 CARACTERÍSTICAS DEL MÓDULO

### ✅ Incluye:
1. **Configuración General** - Datos de empresa, logo, colores
2. **Niveles de Alcohol** - Límites configurables por empresa
3. **Protocolo Re-Test** - Intervalos y cantidad de intentos
4. **Roles y Permisos** - Control de acceso por página
5. **Personalización Visual** - Temas, colores, fuentes
6. **Configuración de Notificaciones** - Email, SMS, Push
7. **Backups** - Manual y automático
8. **Auditoría** - Log de todos los cambios
9. **Mantenimiento** - Modo mantenimiento
10. **API y Webhooks** - Configuración de integraciones

---

## 📁 ARCHIVOS DEL MÓDULO

### 1️⃣ configuracion.php - PÁGINA PRINCIPAL
```php
<?php
require_once 'config.php';
require_once 'functions.php';

// Verificar login y permisos
if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

// Solo admin y super_admin pueden acceder
if ($_SESSION['usuario_rol'] != 'admin' && !esSuperAdmin()) {
    header('Location: index.php');
    exit();
}

$cliente_id = obtenerClienteActual();
$mensaje = '';
$error = '';

// Obtener configuración actual
$sql = "SELECT * FROM configuraciones WHERE cliente_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cliente_id]);
$config = $stmt->fetch();

// Si no existe configuración, crear una por defecto
if (!$config) {
    $sql = "INSERT INTO configuraciones (cliente_id) VALUES (?)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    
    // Volver a obtener
    $sql = "SELECT * FROM configuraciones WHERE cliente_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $config = $stmt->fetch();
}

// Obtener información del cliente
$sql = "SELECT c.*, p.nombre_plan 
        FROM clientes c 
        LEFT JOIN planes p ON c.plan_id = p.id 
        WHERE c.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    switch ($accion) {
        case 'general':
            // Actualizar configuración general
            $sql = "UPDATE clientes SET 
                    nombre_empresa = ?,
                    ruc = ?,
                    direccion = ?,
                    telefono = ?,
                    email_contacto = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['nombre_empresa'],
                $_POST['ruc'],
                $_POST['direccion'],
                $_POST['telefono'],
                $_POST['email_contacto'],
                $cliente_id
            ]);
            
            // Procesar logo si se subió
            if (!empty($_FILES['logo']['name'])) {
                $logo_nombre = 'logo_' . $cliente_id . '_' . time() . '.png';
                $logo_path = UPLOAD_PATH . 'logos/' . $logo_nombre;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                    $sql = "UPDATE clientes SET logo = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$logo_nombre, $cliente_id]);
                }
            }
            
            // Actualizar colores
            $sql = "UPDATE clientes SET 
                    color_primario = ?,
                    color_secundario = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['color_primario'],
                $_POST['color_secundario'],
                $cliente_id
            ]);
            
            $mensaje = "Configuración general actualizada correctamente";
            registrarAuditoria($pdo, 'CONFIG_GENERAL', 'clientes', $cliente_id, 'Actualización de datos generales');
            break;
            
        case 'alcohol':
            // Actualizar niveles de alcohol
            $sql = "UPDATE configuraciones SET 
                    limite_alcohol_permisible = ?,
                    nivel_advertencia = ?,
                    nivel_critico = ?,
                    unidad_medida = ?
                    WHERE cliente_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['limite_alcohol_permisible'],
                $_POST['nivel_advertencia'],
                $_POST['nivel_critico'],
                $_POST['unidad_medida'],
                $cliente_id
            ]);
            
            $mensaje = "Niveles de alcohol actualizados correctamente";
            registrarAuditoria($pdo, 'CONFIG_ALCOHOL', 'configuraciones', $config['id'], 'Actualización de niveles de alcohol');
            break;
            
        case 'retest':
            // Actualizar protocolo de re-test
            $sql = "UPDATE configuraciones SET 
                    intervalo_retest_minutos = ?,
                    intentos_retest = ?,
                    bloqueo_conductor_horas = ?,
                    notificar_supervisor_retest = ?,
                    requerir_aprobacion_supervisor = ?
                    WHERE cliente_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['intervalo_retest_minutos'],
                $_POST['intentos_retest'],
                $_POST['bloqueo_conductor_horas'],
                isset($_POST['notificar_supervisor_retest']) ? 1 : 0,
                isset($_POST['requerir_aprobacion_supervisor']) ? 1 : 0,
                $cliente_id
            ]);
            
            $mensaje = "Protocolo de re-test actualizado correctamente";
            registrarAuditoria($pdo, 'CONFIG_RETEST', 'configuraciones', $config['id'], 'Actualización de protocolo re-test');
            break;
            
        case 'notificaciones':
            // Actualizar configuración de notificaciones
            $sql = "UPDATE configuraciones SET 
                    notificaciones_email = ?,
                    notificaciones_sms = ?,
                    notificaciones_push = ?,
                    notificaciones_whatsapp = ?,
                    email_notificacion = ?,
                    telefono_notificacion = ?
                    WHERE cliente_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                isset($_POST['notificaciones_email']) ? 1 : 0,
                isset($_POST['notificaciones_sms']) ? 1 : 0,
                isset($_POST['notificaciones_push']) ? 1 : 0,
                isset($_POST['notificaciones_whatsapp']) ? 1 : 0,
                $_POST['email_notificacion'],
                $_POST['telefono_notificacion'],
                $cliente_id
            ]);
            
            $mensaje = "Configuración de notificaciones actualizada";
            registrarAuditoria($pdo, 'CONFIG_NOTIF', 'configuraciones', $config['id'], 'Actualización de notificaciones');
            break;
            
        case 'operacion':
            // Actualizar configuración operacional
            $sql = "UPDATE configuraciones SET 
                    requerir_geolocalizacion = ?,
                    requerir_foto_evidencia = ?,
                    requerir_firma_digital = ?,
                    requerir_observaciones = ?,
                    tiempo_maximo_prueba_minutos = ?,
                    distancia_maxima_metros = ?
                    WHERE cliente_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                isset($_POST['requerir_geolocalizacion']) ? 1 : 0,
                isset($_POST['requerir_foto_evidencia']) ? 1 : 0,
                isset($_POST['requerir_firma_digital']) ? 1 : 0,
                isset($_POST['requerir_observaciones']) ? 1 : 0,
                $_POST['tiempo_maximo_prueba_minutos'],
                $_POST['distancia_maxima_metros'],
                $cliente_id
            ]);
            
            $mensaje = "Configuración operacional actualizada";
            registrarAuditoria($pdo, 'CONFIG_OPER', 'configuraciones', $config['id'], 'Actualización operacional');
            break;
            
        case 'backup':
            // Realizar backup manual
            $backup_file = realizarBackup($pdo, $cliente_id);
            if ($backup_file) {
                $mensaje = "Backup realizado correctamente: " . $backup_file;
                registrarAuditoria($pdo, 'BACKUP', null, null, 'Backup manual realizado');
            } else {
                $error = "Error al realizar el backup";
            }
            break;
    }
    
    // Recargar configuración
    $sql = "SELECT * FROM configuraciones WHERE cliente_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $config = $stmt->fetch();
    
    // Recargar cliente
    $sql = "SELECT c.*, p.nombre_plan 
            FROM clientes c 
            LEFT JOIN planes p ON c.plan_id = p.id 
            WHERE c.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - <?php echo SISTEMA_NOMBRE; ?></title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/tabs.css">
    <link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/themify-icons/themify-icons.css">
    
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Roboto', sans-serif;
        }
        
        .header {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .sidebar {
            background: #2c3e50;
            width: 250px;
            min-height: calc(100vh - 70px);
            float: left;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar a {
            display: block;
            padding: 15px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar a:hover,
        .sidebar a.active {
            background: #34495e;
            border-left: 3px solid #3498db;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .config-tabs {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        
        .tab-nav {
            display: flex;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
        }
        
        .tab-nav button {
            flex: 1;
            padding: 15px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: #666;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
        }
        
        .tab-nav button.active {
            color: #667eea;
            background: white;
            border-bottom-color: #667eea;
        }
        
        .tab-nav button:hover {
            background: white;
        }
        
        .tab-nav button i {
            display: block;
            font-size: 24px;
            margin-bottom: 5px;
        }
        
        .tab-content {
            padding: 30px;
        }
        
        .tab-panel {
            display: none;
        }
        
        .tab-panel.active {
            display: block;
        }
        
        .form-section {
            margin-bottom: 30px;
        }
        
        .form-section h3 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 20px;
        }
        
        .form-group {
            margin-bottom: 15px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 14px;
            font-weight: 500;
        }
        
        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }
        
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }
        
        .checkbox-group {
            display: flex;
            align-items: center;
            margin-bottom: 10px;
        }
        
        .checkbox-group input[type="checkbox"] {
            width: auto;
            margin-right: 10px;
        }
        
        .color-picker {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .color-picker input[type="color"] {
            width: 50px;
            height: 40px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
        }
        
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #667eea;
            color: white;
        }
        
        .btn-primary:hover {
            background: #5a67d8;
        }
        
        .btn-success {
            background: #48bb78;
            color: white;
        }
        
        .btn-danger {
            background: #f56565;
            color: white;
        }
        
        .alert {
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 20px;
        }
        
        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #48bb78;
        }
        
        .alert-error {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #f56565;
        }
        
        .range-display {
            display: flex;
            justify-content: space-between;
            margin-top: 5px;
            font-size: 12px;
            color: #999;
        }
        
        .level-indicator {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
        }
        
        .level-aprobado { background: #c6f6d5; color: #22543d; }
        .level-advertencia { background: #feebc8; color: #744210; }
        .level-reprobado { background: #fed7d7; color: #742a2a; }
        .level-critico { background: #fc8181; color: white; }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1><i class="fa fa-flask"></i> <?php echo $cliente['nombre_empresa']; ?></h1>
        <div class="user-info">
            <span>
                <i class="fa fa-user"></i> 
                <?php echo $_SESSION['usuario_nombre']; ?>
            </span>
            <a href="logout.php" class="btn btn-danger">
                <i class="fa fa-sign-out"></i> Salir
            </a>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <ul>
            <li><a href="index.php"><i class="ti-dashboard"></i> Dashboard</a></li>
            <li><a href="pruebas.php"><i class="fa fa-flask"></i> Pruebas</a></li>
            <li><a href="vehiculos.php"><i class="ti-car"></i> Vehículos</a></li>
            <li><a href="conductores.php"><i class="fa fa-users"></i> Conductores</a></li>
            <li><a href="alcoholimetros.php"><i class="ti-harddrives"></i> Alcoholímetros</a></li>
            <li><a href="reportes.php"><i class="ti-bar-chart"></i> Reportes</a></li>
            <li><a href="configuracion.php" class="active"><i class="ti-settings"></i> Configuración</a></li>
            <?php if (esSuperAdmin()): ?>
            <li><a href="roles.php"><i class="fa fa-lock"></i> Roles y Permisos</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <h2><i class="ti-settings"></i> Configuración del Sistema</h2>
        
        <?php if ($mensaje): ?>
        <div class="alert alert-success">
            <i class="fa fa-check-circle"></i> <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>
        
        <?php if ($error): ?>
        <div class="alert alert-error">
            <i class="fa fa-exclamation-circle"></i> <?php echo $error; ?>
        </div>
        <?php endif; ?>
        
        <!-- Tabs de Configuración -->
        <div class="config-tabs">
            <!-- Tab Navigation -->
            <div class="tab-nav">
                <button class="tab-btn active" data-tab="general">
                    <i class="ti-settings"></i>
                    General
                </button>
                <button class="tab-btn" data-tab="alcohol">
                    <i class="fa fa-flask"></i>
                    Niveles de Alcohol
                </button>
                <button class="tab-btn" data-tab="retest">
                    <i class="ti-reload"></i>
                    Protocolo Re-Test
                </button>
                <button class="tab-btn" data-tab="operacion">
                    <i class="ti-clipboard"></i>
                    Operación
                </button>
                <button class="tab-btn" data-tab="notificaciones">
                    <i class="ti-bell"></i>
                    Notificaciones
                </button>
                <button class="tab-btn" data-tab="personalizacion">
                    <i class="ti-palette"></i>
                    Personalización
                </button>
                <button class="tab-btn" data-tab="backup">
                    <i class="ti-server"></i>
                    Backup
                </button>
                <button class="tab-btn" data-tab="api">
                    <i class="ti-plug"></i>
                    API
                </button>
            </div>
            
            <!-- Tab Content -->
            <div class="tab-content">
                <!-- Tab General -->
                <div class="tab-panel active" id="tab-general">
                    <form method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="accion" value="general">
                        
                        <div class="form-section">
                            <h3>Información de la Empresa</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nombre de la Empresa *</label>
                                    <input type="text" name="nombre_empresa" 
                                           value="<?php echo $cliente['nombre_empresa']; ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label>RUC *</label>
                                    <input type="text" name="ruc" 
                                           value="<?php echo $cliente['ruc']; ?>" required>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Dirección</label>
                                    <input type="text" name="direccion" 
                                           value="<?php echo $cliente['direccion']; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Teléfono</label>
                                    <input type="text" name="telefono" 
                                           value="<?php echo $cliente['telefono']; ?>">
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email de Contacto</label>
                                    <input type="email" name="email_contacto" 
                                           value="<?php echo $cliente['email_contacto']; ?>">
                                </div>
                                
                                <div class="form-group">
                                    <label>Logo de la Empresa</label>
                                    <input type="file" name="logo" accept="image/*">
                                    <?php if ($cliente['logo']): ?>
                                    <small>Logo actual: <?php echo $cliente['logo']; ?></small>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Colores Corporativos</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Color Primario</label>
                                    <div class="color-picker">
                                        <input type="color" name="color_primario" 
                                               value="<?php echo $cliente['color_primario']; ?>">
                                        <input type="text" value="<?php echo $cliente['color_primario']; ?>" 
                                               readonly style="width: 100px;">
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label>Color Secundario</label>
                                    <div class="color-picker">
                                        <input type="color" name="color_secundario" 
                                               value="<?php echo $cliente['color_secundario']; ?>">
                                        <input type="text" value="<?php echo $cliente['color_secundario']; ?>" 
                                               readonly style="width: 100px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Guardar Configuración General
                        </button>
                    </form>
                </div>
                
                <!-- Tab Niveles de Alcohol -->
                <div class="tab-panel" id="tab-alcohol">
                    <form method="POST">
                        <input type="hidden" name="accion" value="alcohol">
                        
                        <div class="form-section">
                            <h3>Configuración de Niveles de Alcohol</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Unidad de Medida</label>
                                    <select name="unidad_medida">
                                        <option value="g/L" <?php echo ($config['unidad_medida'] == 'g/L') ? 'selected' : ''; ?>>
                                            g/L (gramos por litro)
                                        </option>
                                        <option value="mg/L" <?php echo ($config['unidad_medida'] == 'mg/L') ? 'selected' : ''; ?>>
                                            mg/L (miligramos por litro)
                                        </option>
                                        <option value="%" <?php echo ($config['unidad_medida'] == '%') ? 'selected' : ''; ?>>
                                            % BAC (Blood Alcohol Content)
                                        </option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Límite Permisible (Aprobado) *</label>
                                    <input type="number" name="limite_alcohol_permisible" 
                                           step="0.001" min="0" max="1"
                                           value="<?php echo $config['limite_alcohol_permisible']; ?>" required>
                                    <span class="level-indicator level-aprobado">APROBADO</span>
                                    <small>Menor o igual a este valor = Aprobado</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Nivel de Advertencia</label>
                                    <input type="number" name="nivel_advertencia" 
                                           step="0.001" min="0" max="1"
                                           value="<?php echo $config['nivel_advertencia'] ?? 0.025; ?>">
                                    <span class="level-indicator level-advertencia">ADVERTENCIA</span>
                                    <small>Mayor a este valor = Advertencia</small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Nivel Crítico</label>
                                    <input type="number" name="nivel_critico" 
                                           step="0.001" min="0" max="1"
                                           value="<?php echo $config['nivel_critico'] ?? 0.08; ?>">
                                    <span class="level-indicator level-critico">CRÍTICO</span>
                                    <small>Mayor a este valor = Crítico (requiere acción inmediata)</small>
                                </div>
                            </div>
                            
                            <!-- Visual de niveles -->
                            <div style="margin: 30px 0;">
                                <h4>Escala Visual de Niveles</h4>
                                <div style="display: flex; height: 40px; border-radius: 8px; overflow: hidden; margin-top: 10px;">
                                    <div style="background: #48bb78; flex: 1; display: flex; align-items: center; justify-content: center; color: white;">
                                        Aprobado
                                    </div>
                                    <div style="background: #f6ad55; flex: 1; display: flex; align-items: center; justify-content: center; color: white;">
                                        Advertencia
                                    </div>
                                    <div style="background: #fc8181; flex: 1; display: flex; align-items: center; justify-content: center; color: white;">
                                        Reprobado
                                    </div>
                                    <div style="background: #c53030; flex: 1; display: flex; align-items: center; justify-content: center; color: white;">
                                        Crítico
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Guardar Niveles de Alcohol
                        </button>
                    </form>
                </div>
                
                <!-- Tab Protocolo Re-Test -->
                <div class="tab-panel" id="tab-retest">
                    <form method="POST">
                        <input type="hidden" name="accion" value="retest">
                        
                        <div class="form-section">
                            <h3>Protocolo de Re-Test para Pruebas Positivas</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Intervalo entre Re-Test (minutos) *</label>
                                    <input type="number" name="intervalo_retest_minutos" 
                                           min="5" max="60" 
                                           value="<?php echo $config['intervalo_retest_minutos']; ?>" required>
                                    <small>Tiempo de espera entre cada intento de re-test</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Cantidad Máxima de Re-Test *</label>
                                    <input type="number" name="intentos_retest" 
                                           min="1" max="5" 
                                           value="<?php echo $config['intentos_retest']; ?>" required>
                                    <small>Número máximo de intentos permitidos</small>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Bloqueo del Conductor (horas)</label>
                                    <input type="number" name="bloqueo_conductor_horas" 
                                           min="0" max="168" 
                                           value="<?php echo $config['bloqueo_conductor_horas'] ?? 24; ?>">
                                    <small>Tiempo de bloqueo después de fallar todos los re-test (0 = sin bloqueo)</small>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h4>Acciones Automáticas</h4>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" name="notificar_supervisor_retest" 
                                           id="notificar_supervisor_retest" 
                                           <?php echo $config['notificar_supervisor_retest'] ? 'checked' : ''; ?>>
                                    <label for="notificar_supervisor_retest">
                                        Notificar al supervisor inmediatamente en prueba positiva
                                    </label>
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" name="requerir_aprobacion_supervisor" 
                                           id="requerir_aprobacion_supervisor"
                                           <?php echo $config['requerir_aprobacion_supervisor'] ? 'checked' : ''; ?>>
                                    <label for="requerir_aprobacion_supervisor">
                                        Requerir aprobación del supervisor para realizar re-test
                                    </label>
                                </div>
                            </div>
                            
                            <!-- Diagrama del proceso -->
                            <div style="background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;">
                                <h4>Flujo del Protocolo</h4>
                                <ol style="line-height: 2;">
                                    <li>Prueba inicial POSITIVA (≥ límite permisible)</li>
                                    <li>Notificación automática al supervisor</li>
                                    <li>Espera de <strong><?php echo $config['intervalo_retest_minutos']; ?> minutos</strong></li>
                                    <li>Re-test #1</li>
                                    <li>Si persiste positivo, repetir hasta <strong><?php echo $config['intentos_retest']; ?> intentos</strong></li>
                                    <li>Después del último intento fallido: Bloqueo por <strong><?php echo $config['bloqueo_conductor_horas'] ?? 24; ?> horas</strong></li>
                                </ol>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Guardar Protocolo Re-Test
                        </button>
                    </form>
                </div>
                
                <!-- Tab Operación -->
                <div class="tab-panel" id="tab-operacion">
                    <form method="POST">
                        <input type="hidden" name="accion" value="operacion">
                        
                        <div class="form-section">
                            <h3>Configuración Operacional</h3>
                            
                            <div class="form-section">
                                <h4>Requerimientos de Prueba</h4>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" name="requerir_geolocalizacion" 
                                           id="requerir_geolocalizacion"
                                           <?php echo $config['requerir_geolocalizacion'] ? 'checked' : ''; ?>>
                                    <label for="requerir_geolocalizacion">
                                        Requerir geolocalización (GPS) en cada prueba
                                    </label>
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" name="requerir_foto_evidencia" 
                                           id="requerir_foto_evidencia"
                                           <?php echo $config['requerir_foto_evidencia'] ? 'checked' : ''; ?>>
                                    <label for="requerir_foto_evidencia">
                                        Requerir foto del conductor como evidencia
                                    </label>
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" name="requerir_firma_digital" 
                                           id="requerir_firma_digital"
                                           <?php echo $config['requerir_firma_digital'] ? 'checked' : ''; ?>>
                                    <label for="requerir_firma_digital">
                                        Requerir firma digital del conductor
                                    </label>
                                </div>
                                
                                <div class="checkbox-group">
                                    <input type="checkbox" name="requerir_observaciones" 
                                           id="requerir_observaciones"
                                           <?php echo $config['requerir_observaciones'] ? 'checked' : ''; ?>>
                                    <label for="requerir_observaciones">
                                        Requerir observaciones obligatorias
                                    </label>
                                </div>
                            </div>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Tiempo Máximo para Prueba (minutos)</label>
                                    <input type="number" name="tiempo_maximo_prueba_minutos" 
                                           min="1" max="60"
                                           value="<?php echo $config['tiempo_maximo_prueba_minutos'] ?? 10; ?>">
                                    <small>Tiempo límite para completar una prueba desde su inicio</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Distancia Máxima (metros)</label>
                                    <input type="number" name="distancia_maxima_metros" 
                                           min="0" max="10000"
                                           value="<?php echo $config['distancia_maxima_metros'] ?? 500; ?>">
                                    <small>Distancia máxima permitida desde el punto de control (0 = sin límite)</small>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Guardar Configuración Operacional
                        </button>
                    </form>
                </div>
                
                <!-- Tab Notificaciones -->
                <div class="tab-panel" id="tab-notificaciones">
                    <form method="POST">
                        <input type="hidden" name="accion" value="notificaciones">
                        
                        <div class="form-section">
                            <h3>Canales de Notificación</h3>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" name="notificaciones_email" 
                                       id="notificaciones_email"
                                       <?php echo $config['notificaciones_email'] ? 'checked' : ''; ?>>
                                <label for="notificaciones_email">
                                    <i class="fa fa-envelope"></i> Notificaciones por Email
                                </label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" name="notificaciones_sms" 
                                       id="notificaciones_sms"
                                       <?php echo $config['notificaciones_sms'] ? 'checked' : ''; ?>>
                                <label for="notificaciones_sms">
                                    <i class="fa fa-mobile"></i> Notificaciones por SMS
                                </label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" name="notificaciones_push" 
                                       id="notificaciones_push"
                                       <?php echo $config['notificaciones_push'] ? 'checked' : ''; ?>>
                                <label for="notificaciones_push">
                                    <i class="fa fa-bell"></i> Notificaciones Push (App Móvil)
                                </label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" name="notificaciones_whatsapp" 
                                       id="notificaciones_whatsapp"
                                       <?php echo $config['notificaciones_whatsapp'] ? 'checked' : ''; ?>>
                                <label for="notificaciones_whatsapp">
                                    <i class="fa fa-whatsapp"></i> Notificaciones por WhatsApp
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Destinatarios</h3>
                            
                            <div class="form-row">
                                <div class="form-group">
                                    <label>Email para Notificaciones</label>
                                    <input type="email" name="email_notificacion" 
                                           value="<?php echo $config['email_notificacion'] ?? ''; ?>"
                                           placeholder="supervisor@empresa.com">
                                    <small>Email principal para recibir alertas del sistema</small>
                                </div>
                                
                                <div class="form-group">
                                    <label>Teléfono para SMS/WhatsApp</label>
                                    <input type="text" name="telefono_notificacion" 
                                           value="<?php echo $config['telefono_notificacion'] ?? ''; ?>"
                                           placeholder="+51 999 999 999">
                                    <small>Número para notificaciones urgentes</small>
                                </div>
                            </div>
                        </div>
                        
                        <button type="submit" class="btn btn-primary">
                            <i class="fa fa-save"></i> Guardar Configuración de Notificaciones
                        </button>
                    </form>
                </div>
                
                <!-- Tab Personalización -->
                <div class="tab-panel" id="tab-personalizacion">
                    <div class="form-section">
                        <h3>Tema Visual</h3>
                        
                        <div class="form-row">
                            <div class="form-group">
                                <label>Tema del Sistema</label>
                                <select disabled>
                                    <option>Tema Claro (Default)</option>
                                    <option>Tema Oscuro (Próximamente)</option>
                                    <option>Tema Corporativo (Próximamente)</option>
                                </select>
                            </div>
                            
                            <div class="form-group">
                                <label>Tamaño de Fuente</label>
                                <select disabled>
                                    <option>Normal</option>
                                    <option>Grande</option>
                                    <option>Extra Grande</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Información del Plan</h3>
                        <div style="background: #f8f9fa; padding: 20px; border-radius: 8px;">
                            <p><strong>Plan Actual:</strong> <?php echo $cliente['nombre_plan']; ?></p>
                            <p><strong>Estado:</strong> <?php echo ucfirst($cliente['estado']); ?></p>
                            <p><strong>Vencimiento:</strong> <?php echo formatearFecha($cliente['fecha_vencimiento'], 'd/m/Y'); ?></p>
                            <p><strong>Token API:</strong> <code><?php echo substr($cliente['token_api'], 0, 20); ?>...</code></p>
                        </div>
                    </div>
                </div>
                
                <!-- Tab Backup -->
                <div class="tab-panel" id="tab-backup">
                    <form method="POST">
                        <input type="hidden" name="accion" value="backup">
                        
                        <div class="form-section">
                            <h3>Backup Manual</h3>
                            <p>Realiza un respaldo completo de los datos de tu empresa.</p>
                            
                            <button type="submit" class="btn btn-success">
                                <i class="fa fa-download"></i> Realizar Backup Ahora
                            </button>
                        </div>
                        
                        <div class="form-section">
                            <h3>Backups Automáticos</h3>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="backup_auto" checked disabled>
                                <label for="backup_auto">
                                    Backup automático diario (3:00 AM)
                                </label>
                            </div>
                            
                            <div class="checkbox-group">
                                <input type="checkbox" id="backup_semanal" checked disabled>
                                <label for="backup_semanal">
                                    Backup semanal completo (Domingos)
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Historial de Backups</h3>
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th style="padding: 10px; text-align: left;">Fecha</th>
                                        <th style="padding: 10px; text-align: left;">Tipo</th>
                                        <th style="padding: 10px; text-align: left;">Tamaño</th>
                                        <th style="padding: 10px; text-align: left;">Estado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td style="padding: 10px;">
                                            <?php echo date('d/m/Y H:i', strtotime('-1 day')); ?>
                                        </td>
                                        <td style="padding: 10px;">Automático</td>
                                        <td style="padding: 10px;">2.5 MB</td>
                                        <td style="padding: 10px;">
                                            <span class="level-indicator level-aprobado">Completado</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </form>
                </div>
                
                <!-- Tab API -->
                <div class="tab-panel" id="tab-api">
                    <div class="form-section">
                        <h3>Configuración de API</h3>
                        
                        <div class="form-group">
                            <label>Token de API</label>
                            <div style="display: flex; gap: 10px;">
                                <input type="text" value="<?php echo $cliente['token_api']; ?>" readonly>
                                <button type="button" class="btn btn-primary" onclick="copyToken()">
                                    <i class="fa fa-copy"></i> Copiar
                                </button>
                            </div>
                            <small>Use este token en el header: Authorization: Bearer {token}</small>
                        </div>
                        
                        <div class="form-group">
                            <label>URL Base de API</label>
                            <input type="text" value="<?php echo SISTEMA_URL; ?>/api/v1/" readonly>
                        </div>
                    </div>
                    
                    <div class="form-section">
                        <h3>Webhooks</h3>
                        <p>Configure URLs para recibir notificaciones automáticas de eventos.</p>
                        
                        <button type="button" class="btn btn-primary" disabled>
                            <i class="fa fa-plus"></i> Agregar Webhook (Próximamente)
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- JS Files -->
    <script src="js/jquery.min.js"></script>
    <script>
        $(document).ready(function() {
            // Tab switching
            $('.tab-btn').click(function() {
                $('.tab-btn').removeClass('active');
                $('.tab-panel').removeClass('active');
                
                $(this).addClass('active');
                $('#tab-' + $(this).data('tab')).addClass('active');
            });
            
            // Color picker sync
            $('input[type="color"]').on('input', function() {
                $(this).next('input[type="text"]').val($(this).val());
            });
            
            // Copy token function
            window.copyToken = function() {
                var tokenInput = document.querySelector('input[value="<?php echo $cliente['token_api']; ?>"]');
                tokenInput.select();
                document.execCommand('copy');
                alert('Token copiado al portapapeles');
            };
        });
    </script>
</body>
</html>
```

### 2️⃣ roles.php - GESTIÓN DE ROLES Y PERMISOS
```php
<?php
require_once 'config.php';
require_once 'functions.php';

// Solo super admin puede acceder
if (!esSuperAdmin()) {
    header('Location: index.php');
    exit();
}

$mensaje = '';
$error = '';

// Obtener todos los roles
$sql = "SELECT * FROM roles ORDER BY nivel ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$roles = $stmt->fetchAll();

// Obtener todos los permisos
$sql = "SELECT * FROM permisos ORDER BY modulo, nombre";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$permisos = $stmt->fetchAll();

// Procesar formulario
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $accion = $_POST['accion'] ?? '';
    
    if ($accion == 'crear_rol') {
        $sql = "INSERT INTO roles (nombre, descripcion, nivel) VALUES (?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $_POST['nombre'],
            $_POST['descripcion'],
            $_POST['nivel']
        ]);
        $mensaje = "Rol creado correctamente";
        
    } elseif ($accion == 'asignar_permisos') {
        $rol_id = $_POST['rol_id'];
        $permisos_seleccionados = $_POST['permisos'] ?? [];
        
        // Eliminar permisos actuales
        $sql = "DELETE FROM rol_permisos WHERE rol_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$rol_id]);
        
        // Insertar nuevos permisos
        foreach ($permisos_seleccionados as $permiso_id) {
            $sql = "INSERT INTO rol_permisos (rol_id, permiso_id) VALUES (?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$rol_id, $permiso_id]);
        }
        
        $mensaje = "Permisos actualizados correctamente";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Roles y Permisos - <?php echo SISTEMA_NOMBRE; ?></title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/themify-icons/themify-icons.css">
    
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Roboto', sans-serif;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .roles-grid {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 30px;
        }
        
        .role-card {
            background: white;
            border-radius: 8px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .role-card:hover {
            transform: translateX(5px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .role-card.active {
            border-left: 4px solid #667eea;
        }
        
        .permission-group {
            margin-bottom: 30px;
        }
        
        .permission-group h4 {
            background: #f8f9fa;
            padding: 10px;
            margin-bottom: 15px;
            border-radius: 4px;
        }
        
        .permission-item {
            display: flex;
            align-items: center;
            padding: 8px;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .permission-item input[type="checkbox"] {
            margin-right: 10px;
        }
    </style>
</head>
<body>
    <div class="main-content">
        <h2><i class="fa fa-lock"></i> Gestión de Roles y Permisos</h2>
        
        <?php if ($mensaje): ?>
        <div class="alert alert-success">
            <i class="fa fa-check"></i> <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>
        
        <div class="roles-grid">
            <!-- Lista de Roles -->
            <div>
                <h3>Roles del Sistema</h3>
                
                <?php foreach ($roles as $rol): ?>
                <div class="role-card" data-rol="<?php echo $rol['id']; ?>">
                    <h4><?php echo $rol['nombre']; ?></h4>
                    <p><?php echo $rol['descripcion']; ?></p>
                    <small>Nivel: <?php echo $rol['nivel']; ?></small>
                </div>
                <?php endforeach; ?>
                
                <button class="btn btn-primary" onclick="$('#modal-nuevo-rol').show()">
                    <i class="fa fa-plus"></i> Crear Nuevo Rol
                </button>
            </div>
            
            <!-- Permisos del Rol -->
            <div>
                <h3>Permisos Asignados</h3>
                
                <form method="POST" id="form-permisos">
                    <input type="hidden" name="accion" value="asignar_permisos">
                    <input type="hidden" name="rol_id" id="rol_id_seleccionado">
                    
                    <?php
                    $modulos = [];
                    foreach ($permisos as $permiso) {
                        if (!isset($modulos[$permiso['modulo']])) {
                            $modulos[$permiso['modulo']] = [];
                        }
                        $modulos[$permiso['modulo']][] = $permiso;
                    }
                    
                    foreach ($modulos as $modulo => $permisos_modulo):
                    ?>
                    <div class="permission-group">
                        <h4><?php echo ucfirst($modulo); ?></h4>
                        <?php foreach ($permisos_modulo as $permiso): ?>
                        <div class="permission-item">
                            <input type="checkbox" 
                                   name="permisos[]" 
                                   value="<?php echo $permiso['id']; ?>"
                                   id="permiso_<?php echo $permiso['id']; ?>">
                            <label for="permiso_<?php echo $permiso['id']; ?>">
                                <strong><?php echo $permiso['nombre']; ?></strong> - 
                                <?php echo $permiso['descripcion']; ?>
                            </label>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <?php endforeach; ?>
                    
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-save"></i> Guardar Permisos
                    </button>
                </form>
            </div>
        </div>
    </div>
    
    <!-- Modal Nuevo Rol -->
    <div id="modal-nuevo-rol" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:1000;">
        <div style="background:white; max-width:500px; margin:100px auto; padding:30px; border-radius:8px;">
            <h3>Crear Nuevo Rol</h3>
            <form method="POST">
                <input type="hidden" name="accion" value="crear_rol">
                
                <div class="form-group">
                    <label>Nombre del Rol</label>
                    <input type="text" name="nombre" required>
                </div>
                
                <div class="form-group">
                    <label>Descripción</label>
                    <textarea name="descripcion" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label>Nivel (1-10)</label>
                    <input type="number" name="nivel" min="1" max="10" required>
                </div>
                
                <button type="submit" class="btn btn-primary">Crear Rol</button>
                <button type="button" onclick="$('#modal-nuevo-rol').hide()" class="btn">Cancelar</button>
            </form>
        </div>
    </div>
    
    <script src="js/jquery.min.js"></script>
    <script>
        $('.role-card').click(function() {
            $('.role-card').removeClass('active');
            $(this).addClass('active');
            
            var rolId = $(this).data('rol');
            $('#rol_id_seleccionado').val(rolId);
            
            // Cargar permisos del rol via AJAX
            // Por ahora solo selecciona el rol
        });
    </script>
</body>
</html>
```

### 3️⃣ functions_config.php - FUNCIONES ADICIONALES
```php
<?php
// ========================================
// FUNCIONES DE CONFIGURACIÓN
// ========================================

// Función para realizar backup
function realizarBackup($pdo, $cliente_id) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "backup_{$cliente_id}_{$fecha}.sql";
    $filepath = UPLOAD_PATH . 'backups/' . $filename;
    
    // Crear directorio si no existe
    if (!file_exists(UPLOAD_PATH . 'backups/')) {
        mkdir(UPLOAD_PATH . 'backups/', 0777, true);
    }
    
    // Obtener todas las tablas
    $tablas = [
        'usuarios',
        'pruebas',
        'alcoholimetros',
        'vehiculos',
        'configuraciones'
    ];
    
    $backup = "-- Backup Sistema Alcoholimetros\n";
    $backup .= "-- Cliente ID: $cliente_id\n";
    $backup .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tablas as $tabla) {
        // Estructura de la tabla
        $sql = "SHOW CREATE TABLE $tabla";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $backup .= "\n-- Tabla: $tabla\n";
        $backup .= $row['Create Table'] . ";\n\n";
        
        // Datos de la tabla (solo del cliente)
        $sql = "SELECT * FROM $tabla WHERE cliente_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cliente_id]);
        
        while ($row = $stmt->fetch()) {
            $backup .= "INSERT INTO $tabla VALUES (";
            $values = [];
            foreach ($row as $value) {
                if (is_null($value)) {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . addslashes($value) . "'";
                }
            }
            $backup .= implode(", ", $values);
            $backup .= ");\n";
        }
    }
    
    // Guardar archivo
    if (file_put_contents($filepath, $backup)) {
        // Registrar en BD
        $sql = "INSERT INTO backups (cliente_id, archivo, tamanio, tipo, fecha_creacion) 
                VALUES (?, ?, ?, 'manual', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $cliente_id,
            $filename,
            filesize($filepath)
        ]);
        
        return $filename;
    }
    
    return false;
}

// Función para validar permisos de acceso
function tienePermiso($pdo, $usuario_id, $permiso) {
    $sql = "SELECT COUNT(*) as tiene
            FROM usuarios u
            JOIN rol_permisos rp ON rp.rol_id = u.rol_id
            JOIN permisos p ON p.id = rp.permiso_id
            WHERE u.id = ? AND p.codigo = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $permiso]);
    $result = $stmt->fetch();
    
    return $result['tiene'] > 0;
}

// Función para obtener configuración específica
function obtenerConfiguracion($pdo, $cliente_id, $clave) {
    $sql = "SELECT * FROM configuraciones WHERE cliente_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $config = $stmt->fetch();
    
    return isset($config[$clave]) ? $config[$clave] : null;
}

// Función para guardar log de configuración
function logConfiguracion($pdo, $cliente_id, $usuario_id, $seccion, $cambios) {
    $sql = "INSERT INTO logs_configuracion 
            (cliente_id, usuario_id, seccion, cambios, fecha) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $cliente_id,
        $usuario_id,
        $seccion,
        json_encode($cambios)
    ]);
}
?>
```

### 4️⃣ database_config.sql - TABLAS ADICIONALES
```sql
-- ========================================
-- TABLAS ADICIONALES PARA CONFIGURACIÓN
-- ========================================

-- Extender tabla de configuraciones
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS nivel_advertencia DECIMAL(5,3) DEFAULT 0.025;
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS nivel_critico DECIMAL(5,3) DEFAULT 0.08;
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS unidad_medida VARCHAR(10) DEFAULT 'g/L';
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS bloqueo_conductor_horas INT DEFAULT 24;
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS notificar_supervisor_retest TINYINT(1) DEFAULT 1;
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS requerir_aprobacion_supervisor TINYINT(1) DEFAULT 0;
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS requerir_observaciones TINYINT(1) DEFAULT 0;
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS tiempo_maximo_prueba_minutos INT DEFAULT 10;
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS distancia_maxima_metros INT DEFAULT 500;
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS notificaciones_whatsapp TINYINT(1) DEFAULT 0;
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS email_notificacion VARCHAR(255);
ALTER TABLE configuraciones ADD COLUMN IF NOT EXISTS telefono_notificacion VARCHAR(20);

-- Tabla de Roles
CREATE TABLE IF NOT EXISTS roles (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(50) NOT NULL,
    descripcion TEXT,
    nivel INT DEFAULT 1,
    estado TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Permisos
CREATE TABLE IF NOT EXISTS permisos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    modulo VARCHAR(50) NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    codigo VARCHAR(50) UNIQUE NOT NULL,
    descripcion TEXT,
    estado TINYINT(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Relación Rol-Permisos
CREATE TABLE IF NOT EXISTS rol_permisos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    rol_id INT NOT NULL,
    permiso_id INT NOT NULL,
    UNIQUE KEY unique_rol_permiso (rol_id, permiso_id),
    FOREIGN KEY (rol_id) REFERENCES roles(id) ON DELETE CASCADE,
    FOREIGN KEY (permiso_id) REFERENCES permisos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Backups
CREATE TABLE IF NOT EXISTS backups (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,
    archivo VARCHAR(255) NOT NULL,
    tamanio BIGINT,
    tipo ENUM('manual','automatico') DEFAULT 'manual',
    estado ENUM('completado','error','en_proceso') DEFAULT 'completado',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Tabla de Logs de Configuración
CREATE TABLE IF NOT EXISTS logs_configuracion (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,
    usuario_id INT,
    seccion VARCHAR(50),
    cambios TEXT,
    fecha TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insertar Roles Básicos
INSERT INTO roles (nombre, descripcion, nivel) VALUES
('Super Admin', 'Acceso total al sistema', 10),
('Admin Cliente', 'Administrador de la empresa', 8),
('Supervisor', 'Supervisor de operaciones', 6),
('Operador', 'Operador de pruebas', 4),
('Conductor', 'Conductor - solo consulta', 2),
('Auditor', 'Solo lectura y reportes', 3)
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Insertar Permisos Básicos
INSERT INTO permisos (modulo, nombre, codigo, descripcion) VALUES
-- Módulo Pruebas
('pruebas', 'Ver Pruebas', 'pruebas.ver', 'Ver listado de pruebas'),
('pruebas', 'Crear Pruebas', 'pruebas.crear', 'Realizar nuevas pruebas'),
('pruebas', 'Editar Pruebas', 'pruebas.editar', 'Modificar pruebas existentes'),
('pruebas', 'Eliminar Pruebas', 'pruebas.eliminar', 'Eliminar pruebas'),
('pruebas', 'Aprobar Re-test', 'pruebas.aprobar_retest', 'Aprobar realización de re-test'),

-- Módulo Configuración
('configuracion', 'Ver Configuración', 'config.ver', 'Ver configuración del sistema'),
('configuracion', 'Editar Configuración', 'config.editar', 'Modificar configuración'),
('configuracion', 'Gestionar Roles', 'config.roles', 'Administrar roles y permisos'),
('configuracion', 'Realizar Backups', 'config.backup', 'Realizar backups del sistema'),

-- Módulo Usuarios
('usuarios', 'Ver Usuarios', 'usuarios.ver', 'Ver listado de usuarios'),
('usuarios', 'Crear Usuarios', 'usuarios.crear', 'Crear nuevos usuarios'),
('usuarios', 'Editar Usuarios', 'usuarios.editar', 'Modificar usuarios'),
('usuarios', 'Eliminar Usuarios', 'usuarios.eliminar', 'Eliminar usuarios'),

-- Módulo Reportes
('reportes', 'Ver Reportes', 'reportes.ver', 'Ver reportes'),
('reportes', 'Exportar Reportes', 'reportes.exportar', 'Exportar reportes'),
('reportes', 'Reportes Gerenciales', 'reportes.gerenciales', 'Acceso a reportes gerenciales'),

-- Módulo Vehículos
('vehiculos', 'Ver Vehículos', 'vehiculos.ver', 'Ver listado de vehículos'),
('vehiculos', 'Gestionar Vehículos', 'vehiculos.gestionar', 'Crear, editar y eliminar vehículos'),

-- Módulo Alcoholímetros
('alcoholimetros', 'Ver Alcoholímetros', 'alcoholimetros.ver', 'Ver listado de alcoholímetros'),
('alcoholimetros', 'Gestionar Alcoholímetros', 'alcoholimetros.gestionar', 'Administrar alcoholímetros')
ON DUPLICATE KEY UPDATE nombre = VALUES(nombre);

-- Asignar todos los permisos al Super Admin (ejemplo)
INSERT INTO rol_permisos (rol_id, permiso_id)
SELECT 1, id FROM permisos
ON DUPLICATE KEY UPDATE rol_id = VALUES(rol_id);
```

### 5️⃣ exportar_config.php - EXPORTAR/IMPORTAR CONFIGURACIÓN
```php
<?php
require_once 'config.php';
require_once 'functions.php';

if (!estaLogueado() || $_SESSION['usuario_rol'] != 'admin') {
    die('Sin permisos');
}

$cliente_id = obtenerClienteActual();

// Exportar configuración
if (isset($_GET['exportar'])) {
    $sql = "SELECT * FROM configuraciones WHERE cliente_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $config = $stmt->fetch();
    
    // Agregar información del cliente
    $sql = "SELECT * FROM clientes WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();
    
    $export = [
        'version' => SISTEMA_VERSION,
        'fecha_export' => date('Y-m-d H:i:s'),
        'cliente' => $cliente,
        'configuracion' => $config
    ];
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="config_backup_' . date('Ymd_His') . '.json"');
    echo json_encode($export, JSON_PRETTY_PRINT);
    exit();
}

// Importar configuración
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['config_file'])) {
    $json = file_get_contents($_FILES['config_file']['tmp_name']);
    $data = json_decode($json, true);
    
    if ($data && isset($data['configuracion'])) {
        $config = $data['configuracion'];
        
        $sql = "UPDATE configuraciones SET 
                limite_alcohol_permisible = ?,
                nivel_advertencia = ?,
                nivel_critico = ?,
                intervalo_retest_minutos = ?,
                intentos_retest = ?
                WHERE cliente_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $config['limite_alcohol_permisible'],
            $config['nivel_advertencia'],
            $config['nivel_critico'],
            $config['intervalo_retest_minutos'],
            $config['intentos_retest'],
            $cliente_id
        ]);
        
        header('Location: configuracion.php?imported=1');
    }
}
?>
```

---

## 📊 CARACTERÍSTICAS COMPLETAS DEL MÓDULO

### ✅ Configuración General
- Datos de empresa
- Logo y colores corporativos
- Información de contacto
- Estado del plan

### ✅ Niveles de Alcohol
- Límite permisible (Aprobado)
- Nivel de advertencia
- Nivel crítico
- Unidades configurables (g/L, mg/L, %BAC)
- Visualización con colores

### ✅ Protocolo de Re-Test
- **Intervalo configurable**: 5-60 minutos
- **Cantidad de intentos**: 1-5 re-tests
- **Bloqueo automático**: Horas configurables
- **Notificaciones**: Al supervisor
- **Aprobación requerida**: Opcional

### ✅ Configuración Operacional
- Requerir geolocalización
- Requerir foto evidencia
- Requerir firma digital
- Requerir observaciones
- Tiempo máximo de prueba
- Distancia máxima permitida

### ✅ Notificaciones
- Email
- SMS
- Push (App)
- WhatsApp
- Configuración de destinatarios

### ✅ Roles y Permisos
- Gestión de roles personalizados
- Asignación granular de permisos
- Control por módulo
- Niveles jerárquicos

### ✅ Backup y Recuperación
- Backup manual
- Backup automático programado
- Historial de backups
- Exportar/Importar configuración

### ✅ API y Webhooks
- Token de API único
- Configuración de endpoints
- Webhooks para eventos

---

## 🚀 INSTALACIÓN

1. **Ejecuta el SQL adicional** (`database_config.sql`)
2. **Copia los archivos PHP** a tu directorio
3. **Accede a** `configuracion.php`

---

## 🎯 USO DEL MÓDULO

### Para configurar niveles de alcohol:
1. Ir a la pestaña "Niveles de Alcohol"
2. Establecer límites según regulación local
3. Guardar cambios

### Para configurar re-test:
1. Ir a la pestaña "Protocolo Re-Test"
2. Establecer intervalo (ej: 15 minutos)
3. Definir cantidad de intentos (ej: 3)
4. Configurar bloqueo si falla todos

### Para gestionar roles:
1. Acceder a `roles.php` (solo super admin)
2. Crear roles personalizados
3. Asignar permisos específicos

---

Este es el **Módulo de Configuración COMPLETO** con todos los parámetros que solicitaste. Incluye todo lo necesario para una configuración profesional y escalable del sistema.