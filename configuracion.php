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
            registrarAuditoria($pdo, 'CONFIG_RETEST', 'configuraciones', $config['id'], 'Actualización de protocolo re-test');
            break;
    }
    
    // Recargar configuración
    $sql = "SELECT * FROM configuraciones WHERE cliente_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $config = $stmt->fetch();
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
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
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
            background: var(--dark);
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
        }
        
        .tab-nav button.active {
            color: var(--secondary-color);
            background: white;
            border-bottom-color: var(--secondary-color);
        }
        
        .tab-nav button:hover {
            background: white;
        }
        
        .tab-nav button i {
            margin-right: 8px;
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
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3><i class="fas fa-flask"></i> <?php echo SISTEMA_NOMBRE; ?></h3>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="pruebas.php"><i class="fas fa-flask"></i> Pruebas</a></li>
                <li><a href="conductores.php"><i class="fas fa-users"></i> Conductores</a></li>
                <li><a href="vehiculos.php"><i class="fas fa-car"></i> Vehículos</a></li>
                <li><a href="alcoholimetros.php"><i class="fas fa-wind"></i> Alcoholímetros</a></li>
                <li><a href="reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
                <li><a href="configuracion.php" class="active"><i class="fas fa-cog"></i> Configuración</a></li>
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
                    <button class="tab-btn active" data-tab="alcohol">
                        <i class="fas fa-flask"></i> Niveles de Alcohol
                    </button>
                    <button class="tab-btn" data-tab="retest">
                        <i class="fas fa-redo"></i> Protocolo Re-Test
                    </button>
                    <button class="tab-btn" data-tab="operacion">
                        <i class="fas fa-clipboard-check"></i> Operación
                    </button>
                    <button class="tab-btn" data-tab="notificaciones">
                        <i class="fas fa-bell"></i> Notificaciones
                    </button>
                    <button class="tab-btn" data-tab="api">
                        <i class="fas fa-plug"></i> API
                    </button>
                </div>
                
                <!-- Tab Content -->
                <div class="tab-content">
                    <!-- Tab Niveles de Alcohol -->
                    <div class="tab-panel active" id="tab-alcohol">
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
                                <i class="fas fa-save"></i> Guardar Niveles de Alcohol
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tab Protocolo Re-Test -->
                    <div class="tab-panel" id="tab-retest">
                        <form method="POST">
                            <input type="hidden" name="accion" value="retest">
                            
                            <div class="form-section">
                                <h3>Protocolo de Re-Test para Pruebas Positivas</h3>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Intervalo entre Re-Test (minutos) *</label>
                                        <input type="number" name="intervalo_retest_minutos" class="form-control"
                                               min="5" max="60" 
                                               value="<?php echo $config['intervalo_retest_minutos'] ?? 15; ?>" required>
                                        <small class="form-text text-muted">Tiempo de espera entre cada intento de re-test</small>
                                    </div>
                                    
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Cantidad Máxima de Re-Test *</label>
                                        <input type="number" name="intentos_retest" class="form-control"
                                               min="1" max="5" 
                                               value="<?php echo $config['intentos_retest'] ?? 3; ?>" required>
                                        <small class="form-text text-muted">Número máximo de intentos permitidos</small>
                                    </div>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label class="form-label">Bloqueo del Conductor (horas)</label>
                                        <input type="number" name="bloqueo_conductor_horas" class="form-control"
                                               min="0" max="168" 
                                               value="<?php echo $config['bloqueo_conductor_horas'] ?? 24; ?>">
                                        <small class="form-text text-muted">Tiempo de bloqueo después de fallar todos los re-test (0 = sin bloqueo)</small>
                                    </div>
                                </div>
                                
                                <div class="form-section">
                                    <h5>Acciones Automáticas</h5>
                                    
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
                                    <h5>Flujo del Protocolo</h5>
                                    <ol>
                                        <li>Prueba inicial POSITIVA (≥ límite permisible)</li>
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
                    
                    <!-- Tab API -->
                    <div class="tab-panel" id="tab-api">
                        <div class="form-section">
                            <h3>Configuración de API</h3>
                            
                            <div class="mb-3">
                                <label class="form-label">Token de API</label>
                                <div class="input-group">
                                    <input type="text" class="form-control" value="<?php echo $cliente['token_api'] ?? ''; ?>" readonly>
                                    <button type="button" class="btn btn-outline-secondary" onclick="copyToken()">
                                        <i class="fas fa-copy"></i> Copiar
                                    </button>
                                </div>
                                <small class="form-text text-muted">Use este token en el header: Authorization: Bearer {token}</small>
                            </div>
                            
                            <div class="mb-3">
                                <label class="form-label">URL Base de API</label>
                                <input type="text" class="form-control" value="<?php echo SISTEMA_URL; ?>/api/v1/" readonly>
                            </div>
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
            
            // Copy token function
            window.copyToken = function() {
                var tokenInput = document.querySelector('input[value="<?php echo $cliente['token_api'] ?? ''; ?>"]');
                tokenInput.select();
                document.execCommand('copy');
                alert('Token copiado al portapapeles');
            };
        });
    </script>
</body>
</html>
