<?php
require_once 'config.php';
require_once 'functions.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

$cliente_id = obtenerClienteActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Alcoholímetros - <?php echo SISTEMA_NOMBRE; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* CSS igual a las otras páginas */
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
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
        
        .device-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            gap: 20px;
        }
        
        .device-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .device-card h5 {
            color: var(--dark);
            margin-bottom: 15px;
        }
        
        .device-info {
            margin-bottom: 10px;
            font-size: 0.9rem;
        }
        
        .device-info strong {
            color: #666;
        }
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
                <li><a href="alcoholimetros.php" class="active"><i class="fas fa-wind"></i> Alcoholímetros</a></li>
                <li><a href="reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
                <?php if ($_SESSION['usuario_rol'] == 'admin' || esSuperAdmin()): ?>
                <li><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
                <li><a href="usuarios.php"><i class="fas fa-user-shield"></i> Usuarios</a></li>
                <?php endif; ?>
                <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Cerrar Sesión</a></li>
            </ul>
        </div>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <div class="topbar">
            <h2>Gestión de Alcoholímetros</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo $_SESSION['usuario_nombre']; ?></span>
            </div>
        </div>
        
        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Dispositivos Alcoholímetros</h3>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Alcoholímetro
                </button>
            </div>
            
            <div class="device-grid">
                <div class="device-card">
                    <h5><i class="fas fa-wind"></i> ALC-001</h5>
                    <div class="device-info">
                        <strong>Modelo:</strong> AL-3000
                    </div>
                    <div class="device-info">
                        <strong>Marca:</strong> AlcoTest
                    </div>
                    <div class="device-info">
                        <strong>Calibración:</strong> 15/01/2024
                    </div>
                    <div class="device-info">
                        <strong>Próxima:</strong> 15/01/2025
                    </div>
                    <div class="device-info">
                        <strong>Estado:</strong> <span class="badge bg-success">Activo</span>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-info">Editar</button>
                        <button class="btn btn-sm btn-warning">Calibrar</button>
                    </div>
                </div>
                
                <div class="device-card">
                    <h5><i class="fas fa-wind"></i> ALC-002</h5>
                    <div class="device-info">
                        <strong>Modelo:</strong> AL-2500
                    </div>
                    <div class="device-info">
                        <strong>Marca:</strong> AlcoTest
                    </div>
                    <div class="device-info">
                        <strong>Calibración:</strong> 20/02/2024
                    </div>
                    <div class="device-info">
                        <strong>Próxima:</strong> 20/02/2025
                    </div>
                    <div class="device-info">
                        <strong>Estado:</strong> <span class="badge bg-success">Activo</span>
                    </div>
                    <div class="mt-3">
                        <button class="btn btn-sm btn-info">Editar</button>
                        <button class="btn btn-sm btn-warning">Calibrar</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>