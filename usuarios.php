<?php
require_once 'config.php';
require_once 'functions.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

// Solo admin puede ver esto
if ($_SESSION['usuario_rol'] != 'admin' && !esSuperAdmin()) {
    header('Location: index.php');
    exit();
}

$cliente_id = obtenerClienteActual();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - <?php echo SISTEMA_NOMBRE; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        /* CSS estándar */
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
        
        .data-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
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
                <li><a href="alcoholimetros.php"><i class="fas fa-wind"></i> Alcoholímetros</a></li>
                <li><a href="reportes.php"><i class="fas fa-chart-bar"></i> Reportes</a></li>
                <li><a href="configuracion.php"><i class="fas fa-cog"></i> Configuración</a></li>
                <li><a href="usuarios.php" class="active"><i class="fas fa-user-shield"></i> Usuarios</a></li>
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
            <h2>Gestión de Usuarios</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo $_SESSION['usuario_nombre']; ?></span>
            </div>
        </div>
        
        <div class="content">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3>Usuarios del Sistema</h3>
                <button class="btn btn-primary">
                    <i class="fas fa-plus"></i> Nuevo Usuario
                </button>
            </div>
            
            <div class="data-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Nombre</th>
                            <th>Email</th>
                            <th>Rol</th>
                            <th>Estado</th>
                            <th>Último Acceso</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Admin Demo</td>
                            <td>admin@demo.com</td>
                            <td><span class="badge bg-danger">Admin</span></td>
                            <td><span class="badge bg-success">Activo</span></td>
                            <td><?php echo date('d/m/Y H:i'); ?></td>
                            <td>
                                <button class="btn btn-sm btn-info"><i class="fas fa-edit"></i></button>
                                <button class="btn btn-sm btn-danger"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>