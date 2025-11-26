<?php
require_once 'config.php';
require_once 'functions.php';

$mensaje_error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = limpiarDato($_POST['email']);
    $password = $_POST['password'];
    
    if (empty($email) || empty($password)) {
        $mensaje_error = 'Por favor complete todos los campos';
    } else {
        // Buscar usuario
        $sql = "SELECT u.*, c.nombre_empresa, c.estado as cliente_estado, c.fecha_vencimiento 
                FROM usuarios u 
                LEFT JOIN clientes c ON u.cliente_id = c.id 
                WHERE u.email = ? AND u.estado = 1";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$email]);
        $usuario = $stmt->fetch();
        
        if ($usuario && verificarPassword($password, $usuario['password'])) {
            // Verificar si el cliente está activo
            if ($usuario['cliente_id'] && $usuario['cliente_estado'] != 'activo' && 
                $usuario['cliente_estado'] != 'prueba') {
                $mensaje_error = 'La cuenta de su empresa está ' . $usuario['cliente_estado'];
            } else {
                // Login exitoso
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['usuario_nombre'] = $usuario['nombre'];
                $_SESSION['usuario_email'] = $usuario['email'];
                $_SESSION['usuario_rol'] = $usuario['rol'];
                $_SESSION['cliente_id'] = $usuario['cliente_id'];
                $_SESSION['es_super_admin'] = ($usuario['rol'] == 'super_admin') ? 1 : 0;
                
                // Actualizar último login
                $sql = "UPDATE usuarios SET ultimo_login = NOW(), intentos_login = 0 WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$usuario['id']]);
                
                // Registrar en auditoría
                registrarAuditoria($pdo, 'LOGIN', 'usuarios', $usuario['id'], 'Inicio de sesión exitoso');
                
                // Redirigir al dashboard
                header('Location: index.php');
                exit();
            }
        } else {
            $mensaje_error = 'Email o contraseña incorrectos';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SISTEMA_NOMBRE; ?> - Login</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/themify-icons/themify-icons.css">
    
    <style>
        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', sans-serif;
        }
        
        .login-box {
            background: white;
            border-radius: 10px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 400px;
            max-width: 90%;
        }
        
        .login-header {
            background: #667eea;
            padding: 30px;
            text-align: center;
            color: white;
        }
        
        .login-header h1 {
            margin: 0;
            font-size: 24px;
            font-weight: 300;
        }
        
        .login-header .icon {
            font-size: 48px;
            margin-bottom: 10px;
        }
        
        .login-body {
            padding: 30px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 5px;
            color: #666;
            font-size: 14px;
        }
        
        .form-group .input-group {
            position: relative;
        }
        
        .form-group .input-group-icon {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .form-group input {
            width: 100%;
            padding: 12px 12px 12px 40px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            transition: all 0.3s;
        }
        
        .form-group input:focus {
            border-color: #667eea;
            outline: none;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        
        .btn-login {
            width: 100%;
            padding: 12px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            cursor: pointer;
            transition: all 0.3s;
        }
        
        .btn-login:hover {
            background: #5a67d8;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .alert-error {
            background: #fee;
            color: #c33;
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .login-footer {
            text-align: center;
            padding: 20px;
            background: #f7f9fc;
            border-top: 1px solid #e9ecef;
        }
        
        .login-footer a {
            color: #667eea;
            text-decoration: none;
            font-size: 14px;
        }
        
        .demo-info {
            margin-top: 15px;
            padding: 15px;
            background: #f0f0f0;
            border-radius: 5px;
            font-size: 12px;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <div class="login-header">
            <i class="fa fa-flask icon"></i>
            <h1><?php echo SISTEMA_NOMBRE; ?></h1>
            <p>Sistema de Gestión de Alcoholimetría</p>
        </div>
        
        <div class="login-body">
            <?php if ($mensaje_error): ?>
            <div class="alert-error">
                <i class="fa fa-exclamation-triangle"></i> <?php echo $mensaje_error; ?>
            </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <div class="input-group">
                        <i class="fa fa-envelope input-group-icon"></i>
                        <input type="email" 
                               id="email" 
                               name="email" 
                               required 
                               placeholder="correo@empresa.com"
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <div class="input-group">
                        <i class="fa fa-lock input-group-icon"></i>
                        <input type="password" 
                               id="password" 
                               name="password" 
                               required 
                               placeholder="••••••••">
                    </div>
                </div>
                
                <button type="submit" class="btn-login">
                    <i class="fa fa-sign-in"></i> Iniciar Sesión
                </button>
            </form>
        </div>
        
        <div class="login-footer">
            <a href="#"><i class="fa fa-key"></i> ¿Olvidaste tu contraseña?</a>
            
            <div class="demo-info">
                <strong>Credenciales Demo:</strong><br>
                Email: admin@demo.com<br>
                Contraseña: password
            </div>
        </div>
    </div>
    
    <!-- JS Files -->
    <script src="js/jquery.min.js"></script>
    <script src="js/validation.js"></script>
</body>
</html>