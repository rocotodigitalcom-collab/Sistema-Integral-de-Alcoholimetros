<?php
// Mostrar todos los errores desde el principio
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

try {
    session_start();
    
    // Redirigir si ya está logueado
    if (isset($_SESSION['user_id'])) {
        header('Location: index.php');
        exit;
    }
    
    $error = '';
    
    // Procesar login
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        if (!empty($email) && !empty($password)) {
            try {
                require_once 'config.php';
				require_once __DIR__ . '/includes/Database.php';
				
                $db = new Database();
                $conn = $db->getConnection();
                
                // Buscar usuario
                $user = $db->fetchOne("
                    SELECT u.*, c.nombre_empresa, c.plan_id, c.estado as cliente_estado 
                    FROM usuarios u 
                    LEFT JOIN clientes c ON u.cliente_id = c.id 
                    WHERE u.email = ? AND u.estado = 1
                ", [$email]);
                
                if ($user && password_verify($password, $user['password'])) {
                    // Establecer sesión
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['user_nombre'] = $user['nombre'];
                    $_SESSION['user_apellido'] = $user['apellido'];
                    $_SESSION['user_role'] = $user['rol'];
                    $_SESSION['cliente_id'] = $user['cliente_id'];
                    $_SESSION['cliente_nombre'] = $user['nombre_empresa'];
                    $_SESSION['user_permissions'] = ['all']; // Temporal
                    
                    header('Location: index.php');
                    exit;
                } else {
                    $error = 'Credenciales incorrectas.';
                }
            } catch (PDOException $e) {
                $error = 'Error de base de datos: ' . $e->getMessage();
            } catch (Exception $e) {
                $error = 'Error del sistema: ' . $e->getMessage();
            }
        } else {
            $error = 'Por favor, complete todos los campos.';
        }
    }
} catch (Exception $e) {
    $error = 'Error inicial: ' . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - AlcoholControl</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { 
            font-family: Arial, sans-serif; 
            background: linear-gradient(135deg, #84061f, #427420);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .login-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
            width: 100%;
            max-width: 400px;
        }
        .error { 
            background: #fee; 
            color: #c33; 
            padding: 0.75rem; 
            border-radius: 5px;
            margin-bottom: 1rem;
            border: 1px solid #fdd;
        }
        .form-group { margin-bottom: 1rem; }
        input { 
            width: 100%; 
            padding: 0.75rem; 
            border: 1px solid #ddd; 
            border-radius: 5px;
        }
        button {
            width: 100%;
            padding: 0.75rem;
            background: #84061f;
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1>AlcoholControl</h1>
        <p>Sistema de Control de Alcoholímetros</p>
        
        <?php if ($error): ?>
        <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <input type="email" name="email" placeholder="Email" value="admin@demo.com" required>
            </div>
            <div class="form-group">
                <input type="password" name="password" placeholder="Contraseña" value="password" required>
            </div>
            <button type="submit">Iniciar Sesión</button>
        </form>
        
        <div style="margin-top: 1rem; font-size: 0.9rem; color: #666;">
            <strong>Demo:</strong> admin@demo.com / password
        </div>
    </div>
</body>
</html>