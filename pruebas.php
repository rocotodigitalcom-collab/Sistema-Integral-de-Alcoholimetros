<?php
require_once 'config.php';
require_once 'functions.php';

if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

$cliente_id = obtenerClienteActual();
$mensaje = '';
$error = '';

// Procesar nueva prueba
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion'])) {
    if ($_POST['accion'] == 'nueva_prueba') {
        // Aquí iría el código para guardar la prueba
        $mensaje = "Prueba registrada correctamente";
    }
}

// Obtener pruebas recientes
$sql = "SELECT p.*, u.nombre as conductor_nombre, u.apellido as conductor_apellido,
        us.nombre as supervisor_nombre, v.placa, a.numero_serie
        FROM pruebas p
        LEFT JOIN usuarios u ON p.conductor_id = u.id
        LEFT JOIN usuarios us ON p.supervisor_id = us.id
        LEFT JOIN vehiculos v ON p.vehiculo_id = v.id
        LEFT JOIN alcoholimetros a ON p.alcoholimetro_id = a.id
        WHERE p.cliente_id = ?
        ORDER BY p.fecha_prueba DESC
        LIMIT 50";
$stmt = $pdo->prepare($sql);
$stmt->execute([$cliente_id]);
$pruebas = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pruebas - <?php echo SISTEMA_NOMBRE; ?></title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <!-- Custom CSS (mismo del index) -->
    <style>
        :root {
            --primary-color: #2c3e50;
            --secondary-color: #3498db;
            --success-color: #27ae60;
            --danger-color: #e74c3c;
            --warning-color: #f39c12;
            --dark: #2c3e50;
            --light: #ecf0f1;
        }
        
        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
            background: #f5f6fa;
            min-height: 100vh;
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
        
        .page-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
        }
        
        .data-table {
            background: white;
            border-radius: 10px;
            padding: 20px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.08);
        }
        
        .badge-aprobado {
            background: var(--success-color);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        
        .badge-reprobado {
            background: var(--danger-color);
            color: white;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 0.85rem;
        }
        
        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            
            .main-content {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="sidebar-header">
            <h3>
                <i class="fas fa-flask"></i>
                <?php echo SISTEMA_NOMBRE; ?>
            </h3>
        </div>
        <div class="sidebar-menu">
            <ul>
                <li><a href="index.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a></li>
                <li><a href="pruebas.php" class="active"><i class="fas fa-flask"></i> Pruebas</a></li>
                <li><a href="conductores.php"><i class="fas fa-users"></i> Conductores</a></li>
                <li><a href="vehiculos.php"><i class="fas fa-car"></i> Vehículos</a></li>
                <li><a href="alcoholimetros.php"><i class="fas fa-wind"></i> Alcoholímetros</a></li>
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
            <h2>Gestión de Pruebas</h2>
            <div class="user-info">
                <i class="fas fa-user-circle"></i>
                <span><?php echo $_SESSION['usuario_nombre']; ?></span>
            </div>
        </div>
        
        <div class="content">
            <div class="page-header">
                <h3>Pruebas de Alcoholimetría</h3>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalNuevaPrueba">
                    <i class="fas fa-plus"></i> Nueva Prueba
                </button>
            </div>
            
            <?php if ($mensaje): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $mensaje; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>
            
            <!-- Tabla de Pruebas -->
            <div class="data-table">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Fecha/Hora</th>
                            <th>Conductor</th>
                            <th>Vehículo</th>
                            <th>Alcoholímetro</th>
                            <th>Nivel</th>
                            <th>Resultado</th>
                            <th>Supervisor</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pruebas)): ?>
                        <tr>
                            <td colspan="8" class="text-center py-4">
                                <i class="fas fa-flask fa-3x text-muted mb-3 d-block"></i>
                                No hay pruebas registradas aún
                            </td>
                        </tr>
                        <?php else: ?>
                            <?php foreach ($pruebas as $prueba): ?>
                            <tr>
                                <td><?php echo date('d/m/Y H:i', strtotime($prueba['fecha_prueba'])); ?></td>
                                <td><?php echo $prueba['conductor_nombre'] . ' ' . $prueba['conductor_apellido']; ?></td>
                                <td><?php echo $prueba['placa'] ?: '-'; ?></td>
                                <td><?php echo $prueba['numero_serie']; ?></td>
                                <td><?php echo number_format($prueba['nivel_alcohol'], 3); ?> g/L</td>
                                <td>
                                    <?php if ($prueba['resultado'] == 'aprobado'): ?>
                                    <span class="badge-aprobado">APROBADO</span>
                                    <?php else: ?>
                                    <span class="badge-reprobado">REPROBADO</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $prueba['supervisor_nombre']; ?></td>
                                <td>
                                    <button class="btn btn-sm btn-info" title="Ver detalles">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    
    <!-- Modal Nueva Prueba -->
    <div class="modal fade" id="modalNuevaPrueba" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Prueba de Alcoholimetría</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST">
                    <input type="hidden" name="accion" value="nueva_prueba">
                    <div class="modal-body">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Conductor</label>
                                    <select name="conductor_id" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Vehículo</label>
                                    <select name="vehiculo_id" class="form-control">
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Alcoholímetro</label>
                                    <select name="alcoholimetro_id" class="form-control" required>
                                        <option value="">Seleccione...</option>
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label>Nivel de Alcohol (g/L)</label>
                                    <input type="number" name="nivel_alcohol" class="form-control" 
                                           step="0.001" min="0" max="5" required>
                                </div>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label>Observaciones</label>
                                    <textarea name="observaciones" class="form-control" rows="3"></textarea>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Guardar Prueba
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
