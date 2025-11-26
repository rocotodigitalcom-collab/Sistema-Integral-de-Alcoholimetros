<?php
require_once 'config.php';
require_once 'functions.php';

// Si no está logueado, redirigir a login
if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

// Obtener datos
$usuario_id = $_SESSION['usuario_id'];
$cliente_id = obtenerClienteActual();

// Si no hay cliente_id y no es super admin, cerrar sesión
if (!$cliente_id && !esSuperAdmin()) {
    header('Location: logout.php');
    exit();
}

// Obtener información del cliente
$cliente = null;
if ($cliente_id) {
    $sql = "SELECT c.*, p.nombre_plan, p.limite_pruebas_mes, p.limite_usuarios, p.limite_alcoholimetros 
            FROM clientes c 
            LEFT JOIN planes p ON c.plan_id = p.id 
            WHERE c.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();
}

// Obtener estadísticas
$stats = $cliente_id ? obtenerEstadisticasCliente($pdo, $cliente_id) : array();

// Calcular días restantes
$dias_restantes = 0;
if ($cliente && $cliente['fecha_vencimiento']) {
    $fecha_vence = strtotime($cliente['fecha_vencimiento']);
    $hoy = time();
    $dias_restantes = ceil(($fecha_vence - $hoy) / 86400);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SISTEMA_NOMBRE; ?> - Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --info-color: #3498db;
            --dark: #2c3e50;
            --light: #ecf0f1;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
        }
        
        /* Sidebar */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            bottom: 0;
            width: 250px;
            background: var(--dark);
            z-index: 100;
            transition: all 0.3s;
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
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .sidebar-menu i {
            width: 20px;
            text-align: center;
        }
        
        /* Main Content */
        .main-content {
            margin-left: 250px;
            min-height: 100vh;
        }
        
        /* Top Bar */
        .topbar {
            background: white;
            padding: 15px 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .topbar-left h2 {
            font-size: 1.5rem;
            color: var(--dark);
            margin: 0;
        }
        
        .topbar-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 8px 15px;
            background: #f8f9fa;
            border-radius: 50px;
        }
        
        .user-info img {
            width: 32px;
            height: 32px;
            border-radius: 50%;
        }
        
        /* Content Area */
        .content {
            padding: 30px;
        }
        
        /* Stats Cards */
        .stats-row {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
            transition: transform 0.3s, box-shadow 0.3s;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.15);
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stat-icon.purple { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); }
        .stat-icon.green { background: linear-gradient(135deg, #0ba360 0%, #3cba92 100%); }
        .stat-icon.red { background: linear-gradient(135deg, #f93b1d 0%, #ea5455 100%); }
        .stat-icon.orange { background: linear-gradient(135deg, #ff9a44 0%, #fc6075 100%); }
        
        .stat-details h3 {
            font-size: 2rem;
            font-weight: 700;
            margin: 0;
            color: var(--dark);
        }
        
        .stat-details p {
            margin: 5px 0;
            color: #7c8798;
            font-size: 0.9rem;
        }
        
        .stat-details small {
            color: #a0a0a0;
            font-size: 0.8rem;
        }
        
        /* Quick Actions */
        .quick-actions {
            background: white;
            border-radius: 10px;
            padding: 25px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .quick-actions h3 {
            font-size: 1.2rem;
            margin-bottom: 20px;
            color: var(--dark);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
        }
        
        .action-card {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            text-decoration: none;
            color: var(--dark);
            transition: all 0.3s;
            border: 2px solid transparent;
        }
        
        .action-card:hover {
            background: white;
            border-color: var(--secondary-color);
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            color: var(--secondary-color);
        }
        
        .action-card i {
            font-size: 32px;
            margin-bottom: 10px;
            display: block;
        }
        
        .action-card span {
            font-size: 0.9rem;
            font-weight: 500;
        }
        
        /* Mobile Menu Toggle */
        .mobile-toggle {
            display: none;
            position: fixed;
            bottom: 20px;
            right: 20px;
            width: 60px;
            height: 60px;
            background: var(--secondary-color);
            border-radius: 50%;
            color: white;
            border: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.2);
            z-index: 1000;
            font-size: 24px;
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            
            .sidebar.active {
                left: 0;
            }
            
            .main-content {
                margin-left: 0;
            }
            
            .mobile-toggle {
                display: flex;
                align-items: center;
                justify-content: center;
            }
            
            .stats-row {
                grid-template-columns: 1fr;
            }
            
            .actions-grid {
                grid-template-columns: repeat(auto-fit, minmax(100px, 1fr));
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
        <!-- Top Bar -->
        <div class="topbar">
            <div class="topbar-left">
                <h2>Panel de Control</h2>
            </div>
            <div class="topbar-right">
                <?php if ($cliente): ?>
                <span class="badge bg-primary">
                    Plan: <?php echo $cliente['nombre_plan']; ?>
                </span>
                <?php endif; ?>
                <div class="user-info">
                    <i class="fas fa-user-circle"></i>
                    <span><?php echo $_SESSION['usuario_nombre']; ?></span>
                </div>
            </div>
        </div>
        
        <!-- Content Area -->
        <div class="content">
            <!-- Stats Cards -->
            <div class="stats-row">
                <div class="stat-card">
                    <div class="stat-icon purple">
                        <i class="fas fa-flask"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo isset($stats['pruebas_mes']) ? $stats['pruebas_mes'] : 0; ?></h3>
                        <p>Pruebas este mes</p>
                        <?php if ($cliente): ?>
                        <small>Límite: <?php echo $cliente['limite_pruebas_mes']; ?></small>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon green">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo isset($stats['pruebas_aprobado']) ? $stats['pruebas_aprobado'] : 0; ?></h3>
                        <p>Aprobadas</p>
                        <?php 
                        $total = isset($stats['pruebas_mes']) ? $stats['pruebas_mes'] : 0;
                        $aprobadas = isset($stats['pruebas_aprobado']) ? $stats['pruebas_aprobado'] : 0;
                        $porcentaje = $total > 0 ? round(($aprobadas / $total) * 100, 1) : 0;
                        ?>
                        <small><?php echo $porcentaje; ?>% del total</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon red">
                        <i class="fas fa-times-circle"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo isset($stats['pruebas_reprobado']) ? $stats['pruebas_reprobado'] : 0; ?></h3>
                        <p>Reprobadas</p>
                        <?php 
                        $reprobadas = isset($stats['pruebas_reprobado']) ? $stats['pruebas_reprobado'] : 0;
                        $porcentaje_rep = $total > 0 ? round(($reprobadas / $total) * 100, 1) : 0;
                        ?>
                        <small><?php echo $porcentaje_rep; ?>% del total</small>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon orange">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-details">
                        <h3><?php echo $dias_restantes; ?></h3>
                        <p>Días restantes</p>
                        <?php if ($cliente): ?>
                        <small>Vence: <?php echo date('d/m/Y', strtotime($cliente['fecha_vencimiento'])); ?></small>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <div class="quick-actions">
                <h3>Acciones Rápidas</h3>
                <div class="actions-grid">
                    <a href="pruebas.php?action=nueva" class="action-card">
                        <i class="fas fa-plus-circle"></i>
                        <span>Nueva Prueba</span>
                    </a>
                    <a href="conductores.php?action=nuevo" class="action-card">
                        <i class="fas fa-user-plus"></i>
                        <span>Nuevo Conductor</span>
                    </a>
                    <a href="vehiculos.php?action=nuevo" class="action-card">
                        <i class="fas fa-car"></i>
                        <span>Nuevo Vehículo</span>
                    </a>
                    <a href="reportes.php?tipo=diario" class="action-card">
                        <i class="fas fa-chart-line"></i>
                        <span>Reporte Diario</span>
                    </a>
                    <a href="alcoholimetros.php?action=calibracion" class="action-card">
                        <i class="fas fa-cogs"></i>
                        <span>Calibración</span>
                    </a>
                    <a href="reportes.php?action=exportar" class="action-card">
                        <i class="fas fa-download"></i>
                        <span>Exportar Datos</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Mobile Menu Toggle -->
    <button class="mobile-toggle" onclick="toggleSidebar()">
        <i class="fas fa-bars"></i>
    </button>
    
    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
        }
        
        // Auto refresh every 5 minutes
        setTimeout(function() {
            location.reload();
        }, 300000);
    </script>
</body>
</html>
