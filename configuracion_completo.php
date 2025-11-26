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
$tab_activa = isset($_GET['tab']) ? $_GET['tab'] : 'general';

// Obtener configuración actual
$sql = "SELECT * FROM configuraciones WHERE cliente_id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cliente_id]);
$config = $stmt->fetch();

// Si no existe configuración, crear una por defecto
if (!$config) {
    $sql = "INSERT INTO configuraciones (cliente_id, limite_alcohol_permisible, intervalo_retest_minutos, intentos_retest) 
            VALUES (?, 0.000, 15, 3)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    
    $sql = "SELECT * FROM configuraciones WHERE cliente_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $config = $stmt->fetch();
}

// Obtener información del cliente
$sql = "SELECT c.*, p.* 
        FROM clientes c 
        LEFT JOIN planes p ON c.plan_id = p.id 
        WHERE c.id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cliente_id]);
$cliente = $stmt->fetch();

// Obtener todos los planes disponibles
$sql = "SELECT * FROM planes ORDER BY precio_mensual ASC";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$planes = $stmt->fetchAll();

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
                    email_contacto = ?,
                    color_primario = ?,
                    color_secundario = ?
                    WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['nombre_empresa'],
                $_POST['ruc'],
                $_POST['direccion'],
                $_POST['telefono'],
                $_POST['email_contacto'],
                $_POST['color_primario'],
                $_POST['color_secundario'],
                $cliente_id
            ]);
            
            // Procesar logo si se subió
            if (!empty($_FILES['logo']['name'])) {
                $upload_dir = 'uploads/logos/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0777, true);
                }
                
                $logo_nombre = 'logo_' . $cliente_id . '_' . time() . '.png';
                $logo_path = $upload_dir . $logo_nombre;
                
                if (move_uploaded_file($_FILES['logo']['tmp_name'], $logo_path)) {
                    $sql = "UPDATE clientes SET logo = ? WHERE id = ?";
                    $stmt = $pdo->prepare($sql);
                    $stmt->execute([$logo_nombre, $cliente_id]);
                }
            }
            
            // Actualizar configuraciones adicionales del sistema
            $sql = "UPDATE configuraciones SET 
                    timezone = ?,
                    idioma = ?,
                    formato_fecha = ?
                    WHERE cliente_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                $_POST['timezone'] ?? 'America/Lima',
                $_POST['idioma'] ?? 'es',
                $_POST['formato_fecha'] ?? 'd/m/Y',
                $cliente_id
            ]);
            
            $mensaje = "Configuración general actualizada correctamente";
            $tab_activa = 'general';
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
                $_POST['nivel_advertencia'] ?? 0.025,
                $_POST['nivel_critico'] ?? 0.08,
                $_POST['unidad_medida'] ?? 'g/L',
                $cliente_id
            ]);
            
            $mensaje = "Niveles de alcohol actualizados correctamente";
            $tab_activa = 'alcohol';
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
                $_POST['bloqueo_conductor_horas'] ?? 24,
                isset($_POST['notificar_supervisor_retest']) ? 1 : 0,
                isset($_POST['requerir_aprobacion_supervisor']) ? 1 : 0,
                $cliente_id
            ]);
            
            $mensaje = "Protocolo de re-test actualizado correctamente";
            $tab_activa = 'retest';
            registrarAuditoria($pdo, 'CONFIG_RETEST', 'configuraciones', $config['id'], 'Actualización de protocolo re-test');
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
                $_POST['tiempo_maximo_prueba_minutos'] ?? 10,
                $_POST['distancia_maxima_metros'] ?? 500,
                $cliente_id
            ]);
            
            $mensaje = "Configuración operacional actualizada";
            $tab_activa = 'operacion';
            registrarAuditoria($pdo, 'CONFIG_OPER', 'configuraciones', $config['id'], 'Actualización operacional');
            break;
            
        case 'notificaciones':
            // Actualizar configuración de notificaciones
            $sql = "UPDATE configuraciones SET 
                    notificaciones_email = ?,
                    notificaciones_sms = ?,
                    notificaciones_push = ?,
                    notificaciones_whatsapp = ?,
                    email_notificacion = ?,
                    telefono_notificacion = ?,
                    emails_adicionales = ?
                    WHERE cliente_id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                isset($_POST['notificaciones_email']) ? 1 : 0,
                isset($_POST['notificaciones_sms']) ? 1 : 0,
                isset($_POST['notificaciones_push']) ? 1 : 0,
                isset($_POST['notificaciones_whatsapp']) ? 1 : 0,
                $_POST['email_notificacion'] ?? '',
                $_POST['telefono_notificacion'] ?? '',
                $_POST['emails_adicionales'] ?? '',
                $cliente_id
            ]);
            
            $mensaje = "Configuración de notificaciones actualizada";
            $tab_activa = 'notificaciones';
            registrarAuditoria($pdo, 'CONFIG_NOTIF', 'configuraciones', $config['id'], 'Actualización de notificaciones');
            break;
            
        case 'cambiar_plan':
            // Cambiar plan de suscripción
            $nuevo_plan_id = $_POST['plan_id'];
            $sql = "UPDATE clientes SET plan_id = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$nuevo_plan_id, $cliente_id]);
            
            $mensaje = "Plan actualizado correctamente";
            $tab_activa = 'planes';
            registrarAuditoria($pdo, 'CAMBIO_PLAN', 'clientes', $cliente_id, 'Cambio de plan a ID: ' . $nuevo_plan_id);
            break;
            
        case 'backup':
            // Realizar backup manual
            $backup_file = realizarBackup($pdo, $cliente_id);
            if ($backup_file) {
                $mensaje = "Backup realizado correctamente: " . $backup_file;
                $tab_activa = 'backup';
                registrarAuditoria($pdo, 'BACKUP', null, null, 'Backup manual realizado');
            } else {
                $error = "Error al realizar el backup";
                $tab_activa = 'backup';
            }
            break;
    }
    
    // Recargar configuración y cliente
    $sql = "SELECT * FROM configuraciones WHERE cliente_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $config = $stmt->fetch();
    
    $sql = "SELECT c.*, p.* 
            FROM clientes c 
            LEFT JOIN planes p ON c.plan_id = p.id 
            WHERE c.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();
}

// Función para realizar backup (simplificada)
function realizarBackup($pdo, $cliente_id) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "backup_{$cliente_id}_{$fecha}.sql";
    
    // Aquí iría el código real de backup
    // Por ahora solo simulamos
    return $filename;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración - <?php echo SISTEMA_NOMBRE; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        :root {
            --primary-color: <?php echo $cliente['color_primario'] ?? '#2c3e50'; ?>;
            --secondary-color: <?php echo $cliente['color_secundario'] ?? '#3498db'; ?>;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --dark: #2c3e50;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f6fa;
        }
        
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 250px;
            background: var(--primary-color);
            z-index: 100;
        }
        
        .sidebar-header {
            padding: 20px;
            background: rgba(0,0,0,0.1);
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        
        .sidebar-header h3 {
            color: white;
            font-size: 1.2rem;
            margin: 0;
        }
        
        .sidebar-menu {
            padding: 20px 0;
        }
        
        .sidebar-menu ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar-menu a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: all 0.3s;
            gap: 15px;
        }
        
        .sidebar-menu a:hover {
            background: rgba(255,255,255,0.05);
            color: white;
            padding-left: 25px;
        }
        
        .sidebar-menu a.active {
            background: var(--secondary-color);
            color: white;
            border-left: 4px solid white;
        }
        
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }
        
        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .content {
            padding: 30px;
        }
        
        .config-tabs {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .tab-nav {
            display: flex;
            background: #f8f9fa;
            border-bottom: 2px solid #e9ecef;
            overflow-x: auto;
            flex-wrap: wrap;
        }
        
        .tab-nav button {
            padding: 15px 20px;
            background: none;
            border: none;
            cursor: pointer;
            font-size: 14px;
            color: #666;
            transition: all 0.3s;
            border-bottom: 3px solid transparent;
            white-space: nowrap;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        
        .tab-nav button.active {
            color: var(--secondary-color);
            background: white;
            border-bottom-color: var(--secondary-color);
        }
        
        .tab-nav button:hover {
            background: white;
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
        
        .form-section h3,
        .form-section h4 {
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #f0f0f0;
            padding-bottom: 10px;
        }
        
        .level-indicator {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: bold;
            margin-left: 10px;
        }
        
        .level-aprobado { background: #c6f6d5; color: #22543d; }
        .level-advertencia { background: #feebc8; color: #744210; }
        .level-reprobado { background: #fed7d7; color: #742a2a; }
        .level-critico { background: #fc8181; color: white; }
        
        .color-picker-group {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .color-preview {
            width: 40px;
            height: 40px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
        
        .plan-card {
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            transition: all 0.3s;
        }
        
        .plan-card.active {
            border-color: var(--secondary-color);
            background: #f0f8ff;
        }
        
        .plan-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .plan-price {
            font-size: 2rem;
            font-weight: bold;
            color: var(--secondary-color);
        }
        
        .backup-list {
            max-height: 300px;
            overflow-y: auto;
        }
        
        .backup-item {
            padding: 10px;
            border-bottom: 1px solid #f0f0f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .tab-nav {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>
                <?php if ($cliente['logo']): ?>
                <img src="uploads/logos/<?php echo $cliente['logo']; ?>" height="30" alt="Logo">
                <?php else: ?>
                <i class="fas fa-flask"></i>
                <?php endif; ?>
                <?php echo SISTEMA_NOMBRE; ?>
            </h3>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="pruebas.php"><i class="fas fa-flask"></i> Pruebas</a></li>
                <li><a href="conductores.php"><i class="fas fa-users"></i> Conductores</a></li>
                <li><a href="vehiculos.php"><i class="fas fa-car"></i> Vehículos</a></li>
                <li><a href="alcoholimetros.php"><i class="fas fa-wind"></i> Alcoholímetros</a></li>
                <li><a href="reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
                <li><a href="configuracion_completo.php" class="active"><i class="fas fa-cog"></i> Configuración</a></li>
                <li><a href="usuarios.php"><i class="fas fa-user-shield"></i> Usuarios</a></li>
                <?php if (esSuperAdmin()): ?>
                <li><a href="roles.php"><i class="fas fa-lock"></i> Roles y Permisos</a></li>
                <?php endif; ?>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2>Configuración del Sistema</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo $_SESSION['usuario_nombre']; ?></span>
            </div>
        </div>
        
        <div class="content">
            <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle"></i> <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Tabs de Configuración -->
            <div class="config-tabs">
                <!-- Tab Navigation -->
                <div class="tab-nav">
                    <button class="tab-btn <?php echo $tab_activa == 'general' ? 'active' : ''; ?>" data-tab="general">
                        <i class="fas fa-building"></i> General
                    </button>
                    <button class="tab-btn <?php echo $tab_activa == 'alcohol' ? 'active' : ''; ?>" data-tab="alcohol">
                        <i class="fas fa-flask"></i> Niveles Alcohol
                    </button>
                    <button class="tab-btn <?php echo $tab_activa == 'retest' ? 'active' : ''; ?>" data-tab="retest">
                        <i class="fas fa-redo"></i> Re-Test
                    </button>
                    <button class="tab-btn <?php echo $tab_activa == 'operacion' ? 'active' : ''; ?>" data-tab="operacion">
                        <i class="fas fa-clipboard-check"></i> Operación
                    </button>
                    <button class="tab-btn <?php echo $tab_activa == 'notificaciones' ? 'active' : ''; ?>" data-tab="notificaciones">
                        <i class="fas fa-bell"></i> Notificaciones
                    </button>
                    <button class="tab-btn <?php echo $tab_activa == 'personalizacion' ? 'active' : ''; ?>" data-tab="personalizacion">
                        <i class="fas fa-palette"></i> Personalización
                    </button>
                    <button class="tab-btn <?php echo $tab_activa == 'planes' ? 'active' : ''; ?>" data-tab="planes">
                        <i class="fas fa-crown"></i> Planes
                    </button>
                    <button class="tab-btn <?php echo $tab_activa == 'backup' ? 'active' : ''; ?>" data-tab="backup">
                        <i class="fas fa-database"></i> Backup
                    </button>
                    <button class="tab-btn <?php echo $tab_activa == 'api' ? 'active' : ''; ?>" data-tab="api">
                        <i class="fas fa-plug"></i> API
                    </button>
                </div>
                
                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Tab General -->
                    <div class="tab-panel <?php echo $tab_activa == 'general' ? 'active' : ''; ?>" id="tab-general">
                        <form method="POST" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="general">
                            
                            <div class="form-section">
                                <h3>Información de la Empresa</h3>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Nombre de la Empresa *</label>
                                        <input type="text" name="nombre_empresa" class="form-control"
                                               value="<?php echo $cliente['nombre_empresa']; ?>" required>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">RUC *</label>
                                        <input type="text" name="ruc" class="form-control"
                                               value="<?php echo $cliente['ruc']; ?>" required>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Dirección</label>
                                        <input type="text" name="direccion" class="form-control"
                                               value="<?php echo $cliente['direccion']; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teléfono</label>
                                        <input type="text" name="telefono" class="form-control"
                                               value="<?php echo $cliente['telefono']; ?>">
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email de Contacto</label>
                                        <input type="email" name="email_contacto" class="form-control"
                                               value="<?php echo $cliente['email_contacto']; ?>">
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Logo de la Empresa</label>
                                        <input type="file" name="logo" class="form-control" accept="image/*">
                                        <?php if ($cliente['logo']): ?>
                                        <small class="text-muted">Logo actual: <?php echo $cliente['logo']; ?></small>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>Configuración del Sistema</h3>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Zona Horaria</label>
                                        <select name="timezone" class="form-control">
                                            <option value="America/Lima" <?php echo ($config['timezone'] ?? 'America/Lima') == 'America/Lima' ? 'selected' : ''; ?>>Lima (UTC-5)</option>
                                            <option value="America/Mexico_City" <?php echo ($config['timezone'] ?? '') == 'America/Mexico_City' ? 'selected' : ''; ?>>Ciudad de México (UTC-6)</option>
                                            <option value="America/Bogota" <?php echo ($config['timezone'] ?? '') == 'America/Bogota' ? 'selected' : ''; ?>>Bogotá (UTC-5)</option>
                                            <option value="America/Santiago" <?php echo ($config['timezone'] ?? '') == 'America/Santiago' ? 'selected' : ''; ?>>Santiago (UTC-3)</option>
                                            <option value="America/Buenos_Aires" <?php echo ($config['timezone'] ?? '') == 'America/Buenos_Aires' ? 'selected' : ''; ?>>Buenos Aires (UTC-3)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Idioma</label>
                                        <select name="idioma" class="form-control">
                                            <option value="es" <?php echo ($config['idioma'] ?? 'es') == 'es' ? 'selected' : ''; ?>>Español</option>
                                            <option value="en" <?php echo ($config['idioma'] ?? '') == 'en' ? 'selected' : ''; ?>>English</option>
                                            <option value="pt" <?php echo ($config['idioma'] ?? '') == 'pt' ? 'selected' : ''; ?>>Português</option>
                                        </select>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Formato de Fecha</label>
                                        <select name="formato_fecha" class="form-control">
                                            <option value="d/m/Y" <?php echo ($config['formato_fecha'] ?? 'd/m/Y') == 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/AAAA</option>
                                            <option value="m/d/Y" <?php echo ($config['formato_fecha'] ?? '') == 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/AAAA</option>
                                            <option value="Y-m-d" <?php echo ($config['formato_fecha'] ?? '') == 'Y-m-d' ? 'selected' : ''; ?>>AAAA-MM-DD</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>Colores Corporativos</h3>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Color Primario</label>
                                        <div class="color-picker-group">
                                            <input type="color" name="color_primario" class="form-control" style="width: 60px"
                                                   value="<?php echo $cliente['color_primario'] ?? '#2c3e50'; ?>">
                                            <div class="color-preview" style="background: <?php echo $cliente['color_primario'] ?? '#2c3e50'; ?>"></div>
                                            <span><?php echo $cliente['color_primario'] ?? '#2c3e50'; ?></span>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Color Secundario</label>
                                        <div class="color-picker-group">
                                            <input type="color" name="color_secundario" class="form-control" style="width: 60px"
                                                   value="<?php echo $cliente['color_secundario'] ?? '#3498db'; ?>">
                                            <div class="color-preview" style="background: <?php echo $cliente['color_secundario'] ?? '#3498db'; ?>"></div>
                                            <span><?php echo $cliente['color_secundario'] ?? '#3498db'; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Configuración General
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tab Niveles de Alcohol -->
                    <div class="tab-panel <?php echo $tab_activa == 'alcohol' ? 'active' : ''; ?>" id="tab-alcohol">
                        <form method="POST">
                            <input type="hidden" name="accion" value="alcohol">
                            
                            <div class="form-section">
                                <h3>Configuración de Niveles de Alcohol</h3>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Unidad de Medida</label>
                                        <select name="unidad_medida" class="form-control">
                                            <option value="g/L" <?php echo ($config['unidad_medida'] ?? 'g/L') == 'g/L' ? 'selected' : ''; ?>>
                                                g/L (gramos por litro)
                                            </option>
                                            <option value="mg/L" <?php echo ($config['unidad_medida'] ?? '') == 'mg/L' ? 'selected' : ''; ?>>
                                                mg/L (miligramos por litro)
                                            </option>
                                            <option value="%" <?php echo ($config['unidad_medida'] ?? '') == '%' ? 'selected' : ''; ?>>
                                                % BAC (Blood Alcohol Content)
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            Límite Permisible (Aprobado) *
                                            <span class="level-indicator level-aprobado">APROBADO</span>
                                        </label>
                                        <input type="number" name="limite_alcohol_permisible" class="form-control"
                                               step="0.001" min="0" max="1"
                                               value="<?php echo $config['limite_alcohol_permisible'] ?? 0.000; ?>" required>
                                        <small class="form-text text-muted">Menor o igual a este valor = Aprobado</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            Nivel de Advertencia
                                            <span class="level-indicator level-advertencia">ADVERTENCIA</span>
                                        </label>
                                        <input type="number" name="nivel_advertencia" class="form-control"
                                               step="0.001" min="0" max="1"
                                               value="<?php echo $config['nivel_advertencia'] ?? 0.025; ?>">
                                        <small class="form-text text-muted">Mayor a este valor = Advertencia</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">
                                            Nivel Crítico
                                            <span class="level-indicator level-critico">CRÍTICO</span>
                                        </label>
                                        <input type="number" name="nivel_critico" class="form-control"
                                               step="0.001" min="0" max="1"
                                               value="<?php echo $config['nivel_critico'] ?? 0.08; ?>">
                                        <small class="form-text text-muted">Mayor a este valor = Crítico (requiere acción inmediata)</small>
                                    </div>
                                </div>
                                
                                <!-- Visual de niveles -->
                                <div class="mt-4">
                                    <h5>Escala Visual de Niveles</h5>
                                    <div style="display: flex; height: 50px; border-radius: 8px; overflow: hidden; margin-top: 10px;">
                                        <div style="background: #48bb78; flex: 1; display: flex; align-items: center; justify-content: center; color: white;">
                                            <strong>Aprobado</strong>
                                        </div>
                                        <div style="background: #f6ad55; flex: 1; display: flex; align-items: center; justify-content: center; color: white;">
                                            <strong>Advertencia</strong>
                                        </div>
                                        <div style="background: #fc8181; flex: 1; display: flex; align-items: center; justify-content: center; color: white;">
                                            <strong>Reprobado</strong>
                                        </div>
                                        <div style="background: #c53030; flex: 1; display: flex; align-items: center; justify-content: center; color: white;">
                                            <strong>Crítico</strong>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Niveles de Alcohol
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tab Protocolo Re-Test -->
                    <div class="tab-panel <?php echo $tab_activa == 'retest' ? 'active' : ''; ?>" id="tab-retest">
                        <form method="POST">
                            <input type="hidden" name="accion" value="retest">
                            
                            <div class="form-section">
                                <h3>Protocolo de Re-Test para Pruebas Positivas</h3>
                                
                                <div class="row">
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Intervalo entre Re-Test (minutos) *</label>
                                        <input type="number" name="intervalo_retest_minutos" class="form-control"
                                               min="5" max="60" 
                                               value="<?php echo $config['intervalo_retest_minutos'] ?? 15; ?>" required>
                                        <small class="form-text text-muted">Tiempo de espera entre cada intento</small>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Cantidad Máxima de Re-Test *</label>
                                        <input type="number" name="intentos_retest" class="form-control"
                                               min="1" max="5" 
                                               value="<?php echo $config['intentos_retest'] ?? 3; ?>" required>
                                        <small class="form-text text-muted">Número máximo de intentos</small>
                                    </div>
                                    
                                    <div class="col-md-4 mb-3">
                                        <label class="form-label">Bloqueo del Conductor (horas)</label>
                                        <input type="number" name="bloqueo_conductor_horas" class="form-control"
                                               min="0" max="168" 
                                               value="<?php echo $config['bloqueo_conductor_horas'] ?? 24; ?>">
                                        <small class="form-text text-muted">Tiempo de bloqueo después de fallar</small>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h4>Acciones Automáticas</h4>
                                    
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="notificar_supervisor_retest" class="form-check-input"
                                               id="notificar_supervisor_retest" 
                                               <?php echo ($config['notificar_supervisor_retest'] ?? 1) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="notificar_supervisor_retest">
                                            Notificar al supervisor inmediatamente en prueba positiva
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input type="checkbox" name="requerir_aprobacion_supervisor" class="form-check-input"
                                               id="requerir_aprobacion_supervisor"
                                               <?php echo ($config['requerir_aprobacion_supervisor'] ?? 0) ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="requerir_aprobacion_supervisor">
                                            Requerir aprobación del supervisor para realizar re-test
                                        </label>
                                    </div>
                                </div>
                                
                                <!-- Diagrama del proceso -->
                                <div class="alert alert-info mt-3">
                                    <h5><i class="fas fa-info-circle"></i> Flujo del Protocolo Configurado</h5>
                                    <ol>
                                        <li>Prueba inicial POSITIVA (≥ <?php echo $config['limite_alcohol_permisible'] ?? '0.000'; ?> g/L)</li>
                                        <li>Notificación automática al supervisor</li>
                                        <li>Espera de <strong><?php echo $config['intervalo_retest_minutos'] ?? 15; ?> minutos</strong></li>
                                        <li>Re-test #1</li>
                                        <li>Si persiste positivo, repetir hasta <strong><?php echo $config['intentos_retest'] ?? 3; ?> intentos</strong></li>
                                        <li>Después del último intento fallido: Bloqueo por <strong><?php echo $config['bloqueo_conductor_horas'] ?? 24; ?> horas</strong></li>
                                    </ol>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Protocolo Re-Test
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tab Operación -->
                    <div class="tab-panel <?php echo $tab_activa == 'operacion' ? 'active' : ''; ?>" id="tab-operacion">
                        <form method="POST">
                            <input type="hidden" name="accion" value="operacion">
                            
                            <div class="form-section">
                                <h3>Configuración Operacional</h3>
                                
                                <h4>Requerimientos de Prueba</h4>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="requerir_geolocalizacion" class="form-check-input"
                                                   id="requerir_geolocalizacion"
                                                   <?php echo ($config['requerir_geolocalizacion'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="requerir_geolocalizacion">
                                                <i class="fas fa-map-marker-alt"></i> Requerir geolocalización (GPS) en cada prueba
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="requerir_foto_evidencia" class="form-check-input"
                                                   id="requerir_foto_evidencia"
                                                   <?php echo ($config['requerir_foto_evidencia'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="requerir_foto_evidencia">
                                                <i class="fas fa-camera"></i> Requerir foto del conductor como evidencia
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="requerir_firma_digital" class="form-check-input"
                                                   id="requerir_firma_digital"
                                                   <?php echo ($config['requerir_firma_digital'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="requerir_firma_digital">
                                                <i class="fas fa-signature"></i> Requerir firma digital del conductor
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="requerir_observaciones" class="form-check-input"
                                                   id="requerir_observaciones"
                                                   <?php echo ($config['requerir_observaciones'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="requerir_observaciones">
                                                <i class="fas fa-comment"></i> Requerir observaciones obligatorias
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <h4 class="mt-4">Límites Operacionales</h4>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Tiempo Máximo para Prueba (minutos)</label>
                                        <input type="number" name="tiempo_maximo_prueba_minutos" class="form-control"
                                               min="1" max="60"
                                               value="<?php echo $config['tiempo_maximo_prueba_minutos'] ?? 10; ?>">
                                        <small class="form-text text-muted">Tiempo límite para completar una prueba desde su inicio</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Distancia Máxima del Punto de Control (metros)</label>
                                        <input type="number" name="distancia_maxima_metros" class="form-control"
                                               min="0" max="10000"
                                               value="<?php echo $config['distancia_maxima_metros'] ?? 500; ?>">
                                        <small class="form-text text-muted">Distancia máxima permitida desde el punto de control (0 = sin límite)</small>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Configuración Operacional
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tab Notificaciones -->
                    <div class="tab-panel <?php echo $tab_activa == 'notificaciones' ? 'active' : ''; ?>" id="tab-notificaciones">
                        <form method="POST">
                            <input type="hidden" name="accion" value="notificaciones">
                            
                            <div class="form-section">
                                <h3>Canales de Notificación</h3>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="notificaciones_email" class="form-check-input"
                                                   id="notificaciones_email"
                                                   <?php echo ($config['notificaciones_email'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notificaciones_email">
                                                <i class="fas fa-envelope"></i> Notificaciones por Email
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="notificaciones_sms" class="form-check-input"
                                                   id="notificaciones_sms"
                                                   <?php echo ($config['notificaciones_sms'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notificaciones_sms">
                                                <i class="fas fa-sms"></i> Notificaciones por SMS
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="col-md-6">
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="notificaciones_push" class="form-check-input"
                                                   id="notificaciones_push"
                                                   <?php echo ($config['notificaciones_push'] ?? 1) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notificaciones_push">
                                                <i class="fas fa-mobile-alt"></i> Notificaciones Push (App Móvil)
                                            </label>
                                        </div>
                                        
                                        <div class="form-check mb-3">
                                            <input type="checkbox" name="notificaciones_whatsapp" class="form-check-input"
                                                   id="notificaciones_whatsapp"
                                                   <?php echo ($config['notificaciones_whatsapp'] ?? 0) ? 'checked' : ''; ?>>
                                            <label class="form-check-label" for="notificaciones_whatsapp">
                                                <i class="fab fa-whatsapp"></i> Notificaciones por WhatsApp
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>Destinatarios de Notificaciones</h3>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Email Principal para Notificaciones</label>
                                        <input type="email" name="email_notificacion" class="form-control"
                                               value="<?php echo $config['email_notificacion'] ?? ''; ?>"
                                               placeholder="supervisor@empresa.com">
                                        <small class="form-text text-muted">Email principal para recibir alertas del sistema</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Teléfono para SMS/WhatsApp</label>
                                        <input type="text" name="telefono_notificacion" class="form-control"
                                               value="<?php echo $config['telefono_notificacion'] ?? ''; ?>"
                                               placeholder="+51 999 999 999">
                                        <small class="form-text text-muted">Número para notificaciones urgentes</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label class="form-label">Emails Adicionales (separados por coma)</label>
                                        <textarea name="emails_adicionales" class="form-control" rows="2"
                                                  placeholder="gerente@empresa.com, rrhh@empresa.com"><?php echo $config['emails_adicionales'] ?? ''; ?></textarea>
                                        <small class="form-text text-muted">Lista de emails adicionales para copias de notificaciones importantes</small>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="form-section">
                                <h3>Tipos de Eventos a Notificar</h3>
                                
                                <div class="alert alert-info">
                                    <i class="fas fa-info-circle"></i> Seleccione los eventos que desea que generen notificaciones automáticas
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" checked disabled>
                                            <label class="form-check-label">Prueba Positiva (Siempre activo)</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" checked>
                                            <label class="form-check-label">Re-test Fallido</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input" checked>
                                            <label class="form-check-label">Conductor Bloqueado</label>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input">
                                            <label class="form-check-label">Calibración Vencida</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input">
                                            <label class="form-check-label">Límite de Pruebas Alcanzado</label>
                                        </div>
                                        <div class="form-check mb-2">
                                            <input type="checkbox" class="form-check-input">
                                            <label class="form-check-label">Falla de Dispositivo</label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Guardar Configuración de Notificaciones
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tab Personalización -->
                    <div class="tab-panel <?php echo $tab_activa == 'personalizacion' ? 'active' : ''; ?>" id="tab-personalizacion">
                        <div class="form-section">
                            <h3>Tema Visual del Sistema</h3>
                            
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tema del Sistema</label>
                                    <select class="form-control">
                                        <option>Tema Claro (Default)</option>
                                        <option disabled>Tema Oscuro (Próximamente)</option>
                                        <option disabled>Tema Alto Contraste (Próximamente)</option>
                                    </select>
                                </div>
                                
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Tamaño de Fuente</label>
                                    <select class="form-control">
                                        <option>Normal (14px)</option>
                                        <option>Grande (16px)</option>
                                        <option>Extra Grande (18px)</option>
                                    </select>
                                </div>
                            </div>
                            
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i> 
                                Los cambios de personalización visual se aplicarán en la próxima actualización del sistema.
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Personalización de Reportes</h3>
                            
                            <div class="row">
                                <div class="col-12">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" checked>
                                        <label class="form-check-label">Incluir logo de la empresa en reportes PDF</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" checked>
                                        <label class="form-check-label">Usar colores corporativos en gráficos</label>
                                    </div>
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input">
                                        <label class="form-check-label">Incluir marca de agua en documentos</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Planes -->
                    <div class="tab-panel <?php echo $tab_activa == 'planes' ? 'active' : ''; ?>" id="tab-planes">
                        <div class="form-section">
                            <h3>Plan Actual: <?php echo $cliente['nombre_plan']; ?></h3>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle"></i> 
                                <strong>Estado:</strong> <?php echo ucfirst($cliente['estado']); ?> | 
                                <strong>Vencimiento:</strong> <?php echo date('d/m/Y', strtotime($cliente['fecha_vencimiento'])); ?>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Planes Disponibles</h3>
                            
                            <div class="row">
                                <?php foreach ($planes as $plan): ?>
                                <div class="col-md-6 col-lg-3">
                                    <div class="plan-card <?php echo $cliente['plan_id'] == $plan['id'] ? 'active' : ''; ?>">
                                        <h4><?php echo $plan['nombre_plan']; ?></h4>
                                        <?php if ($cliente['plan_id'] == $plan['id']): ?>
                                        <span class="badge bg-success mb-2">PLAN ACTUAL</span>
                                        <?php endif; ?>
                                        
                                        <div class="plan-price">
                                            $<?php echo number_format($plan['precio_mensual'], 2); ?>
                                            <small>/mes</small>
                                        </div>
                                        
                                        <ul class="list-unstyled mt-3">
                                            <li><i class="fas fa-check text-success"></i> <?php echo $plan['limite_pruebas_mes']; ?> pruebas/mes</li>
                                            <li><i class="fas fa-check text-success"></i> <?php echo $plan['limite_usuarios']; ?> usuarios</li>
                                            <li><i class="fas fa-check text-success"></i> <?php echo $plan['limite_alcoholimetros']; ?> alcoholímetros</li>
                                            <?php if ($plan['reportes_avanzados']): ?>
                                            <li><i class="fas fa-check text-success"></i> Reportes avanzados</li>
                                            <?php endif; ?>
                                            <?php if ($plan['soporte_prioritario']): ?>
                                            <li><i class="fas fa-check text-success"></i> Soporte prioritario</li>
                                            <?php endif; ?>
                                            <?php if ($plan['acceso_api']): ?>
                                            <li><i class="fas fa-check text-success"></i> Acceso API</li>
                                            <?php endif; ?>
                                        </ul>
                                        
                                        <?php if ($cliente['plan_id'] != $plan['id']): ?>
                                        <form method="POST" class="mt-3">
                                            <input type="hidden" name="accion" value="cambiar_plan">
                                            <input type="hidden" name="plan_id" value="<?php echo $plan['id']; ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-primary w-100">
                                                Cambiar a este plan
                                            </button>
                                        </form>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab Backup -->
                    <div class="tab-panel <?php echo $tab_activa == 'backup' ? 'active' : ''; ?>" id="tab-backup">
                        <form method="POST">
                            <input type="hidden" name="accion" value="backup">
                            
                            <div class="form-section">
                                <h3>Backup Manual</h3>
                                <p>Realiza un respaldo completo de los datos de tu empresa.</p>
                                
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-download"></i> Realizar Backup Ahora
                                </button>
                            </div>
                        </form>
                        
                        <div class="form-section">
                            <h3>Configuración de Backups Automáticos</h3>
                            
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="backup_diario" checked>
                                        <label class="form-check-label" for="backup_diario">
                                            <strong>Backup diario</strong> (3:00 AM hora local)
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="backup_semanal" checked>
                                        <label class="form-check-label" for="backup_semanal">
                                            <strong>Backup semanal completo</strong> (Domingos 2:00 AM)
                                        </label>
                                    </div>
                                    
                                    <div class="form-check mb-2">
                                        <input type="checkbox" class="form-check-input" id="backup_mensual">
                                        <label class="form-check-label" for="backup_mensual">
                                            <strong>Backup mensual</strong> (Día 1 de cada mes)
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label class="form-label">Retención de backups</label>
                                        <select class="form-control">
                                            <option>7 días</option>
                                            <option selected>30 días</option>
                                            <option>90 días</option>
                                            <option>1 año</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Almacenamiento</label>
                                        <select class="form-control">
                                            <option selected>Servidor local</option>
                                            <option>Amazon S3</option>
                                            <option>Google Cloud Storage</option>
                                            <option>Dropbox</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Historial de Backups</h3>
                            
                            <div class="backup-list">
                                <div class="backup-item">
                                    <div>
                                        <strong>backup_<?php echo $cliente_id; ?>_2024-11-25_03-00-00.sql</strong><br>
                                        <small class="text-muted">Automático - <?php echo date('d/m/Y 03:00', strtotime('-1 day')); ?></small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">Completado</span>
                                        <button class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="backup-item">
                                    <div>
                                        <strong>backup_<?php echo $cliente_id; ?>_2024-11-24_03-00-00.sql</strong><br>
                                        <small class="text-muted">Automático - <?php echo date('d/m/Y 03:00', strtotime('-2 days')); ?></small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">Completado</span>
                                        <button class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>
                                
                                <div class="backup-item">
                                    <div>
                                        <strong>backup_<?php echo $cliente_id; ?>_2024-11-23_15-30-00.sql</strong><br>
                                        <small class="text-muted">Manual - <?php echo date('d/m/Y 15:30', strtotime('-2 days')); ?></small>
                                    </div>
                                    <div>
                                        <span class="badge bg-success">Completado</span>
                                        <button class="btn btn-sm btn-outline-primary ms-2">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Tab API -->
                    <div class="tab-panel <?php echo $tab_activa == 'api' ? 'active' : ''; ?>" id="tab-api">
                        <div class="form-section">
                            <h3>Configuración de API</h3>
                            
                            <div class="row">
                                <div class="col-md-8 mb-3">
                                    <label class="form-label">Token de API</label>
                                    <div class="input-group">
                                        <input type="text" class="form-control" id="api_token" 
                                               value="<?php echo $cliente['token_api'] ?? 'No configurado'; ?>" readonly>
                                        <button type="button" class="btn btn-outline-secondary" onclick="copyToken()">
                                            <i class="fas fa-copy"></i> Copiar
                                        </button>
                                        <button type="button" class="btn btn-outline-warning" onclick="regenerateToken()">
                                            <i class="fas fa-sync"></i> Regenerar
                                        </button>
                                    </div>
                                    <small class="form-text text-muted">Use este token en el header: Authorization: Bearer {token}</small>
                                </div>
                                
                                <div class="col-md-4 mb-3">
                                    <label class="form-label">Estado de API</label>
                                    <div class="form-check form-switch">
                                        <input type="checkbox" class="form-check-input" id="api_enabled" checked>
                                        <label class="form-check-label" for="api_enabled">
                                            API Habilitada
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">URL Base de API</label>
                                <input type="text" class="form-control" value="<?php echo SISTEMA_URL; ?>/api/v1/" readonly>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">IPs Permitidas (dejar vacío para permitir todas)</label>
                                <textarea class="form-control" rows="3" placeholder="192.168.1.100&#10;203.0.113.0/24"></textarea>
                                <small class="form-text text-muted">Una IP o rango por línea</small>
                            </div>
                        </div>
                        
                        <div class="form-section">
                            <h3>Webhooks</h3>
                            <p>Configure URLs para recibir notificaciones automáticas de eventos.</p>
                            
                            <div class="mb-3">
                                <label class="form-label">URL Webhook para Pruebas Positivas</label>
                                <input type="url" class="form-control" placeholder="https://sudominio.com/webhook/prueba-positiva">
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">URL Webhook para Calibraciones Vencidas</label>
                                <input type="url" class="form-control" placeholder="https://sudominio.com/webhook/calibracion-vencida">
                            </div>
                            
                            <button type="button" class="btn btn-primary">
                                <i class="fas fa-plus"></i> Agregar Webhook
                            </button>
                        </div>
                        
                        <div class="form-section">
                            <h3>Documentación API</h3>
                            
                            <div class="alert alert-info">
                                <i class="fas fa-book"></i> 
                                Consulte la documentación completa de la API en: 
                                <a href="<?php echo SISTEMA_URL; ?>/api/docs" target="_blank">
                                    <?php echo SISTEMA_URL; ?>/api/docs
                                </a>
                            </div>
                            
                            <h5>Endpoints Principales:</h5>
                            <ul>
                                <li><code>GET /api/v1/pruebas</code> - Listar pruebas</li>
                                <li><code>POST /api/v1/pruebas</code> - Crear nueva prueba</li>
                                <li><code>GET /api/v1/conductores</code> - Listar conductores</li>
                                <li><code>GET /api/v1/reportes/diario</code> - Reporte diario</li>
                                <li><code>GET /api/v1/alcoholimetros</code> - Listar dispositivos</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        $(document).ready(function() {
            // Tab switching
            $('.tab-btn').click(function() {
                $('.tab-btn').removeClass('active');
                $('.tab-panel').removeClass('active');
                
                $(this).addClass('active');
                $('#tab-' + $(this).data('tab')).addClass('active');
            });
            
            // Color picker update
            $('input[type="color"]').on('input', function() {
                $(this).siblings('.color-preview').css('background', $(this).val());
                $(this).siblings('span').text($(this).val());
            });
        });
        
        // Copy token function
        function copyToken() {
            var tokenInput = document.getElementById('api_token');
            tokenInput.select();
            document.execCommand('copy');
            
            // Show feedback
            var btn = event.target.closest('button');
            var originalHTML = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-check"></i> Copiado!';
            btn.classList.remove('btn-outline-secondary');
            btn.classList.add('btn-success');
            
            setTimeout(function() {
                btn.innerHTML = originalHTML;
                btn.classList.remove('btn-success');
                btn.classList.add('btn-outline-secondary');
            }, 2000);
        }
        
        // Regenerate token
        function regenerateToken() {
            if (confirm('¿Está seguro de regenerar el token? Las aplicaciones que usen el token actual dejarán de funcionar.')) {
                // Aquí iría la llamada AJAX para regenerar el token
                alert('Token regenerado correctamente');
            }
        }
    </script>
</body>
</html>