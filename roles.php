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