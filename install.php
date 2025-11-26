<?php
// Instalador simple
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $host = $_POST['db_host'];
    $user = $_POST['db_user'];
    $pass = $_POST['db_pass'];
    $name = $_POST['db_name'];
    
    try {
        // Conectar sin especificar BD
        $pdo = new PDO("mysql:host=$host", $user, $pass);
        
        // Leer y ejecutar SQL
        $sql = file_get_contents('sql/database.sql');
        $pdo->exec($sql);
        
        $mensaje = 'Base de datos instalada correctamente. <a href="login.php">Ir al Login</a>';
    } catch (Exception $e) {
        $mensaje = 'Error: ' . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Instalador</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <div style="max-width: 500px; margin: 100px auto; padding: 20px; background: white; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);">
        <h2>Instalador del Sistema</h2>
        
        <?php if ($mensaje): ?>
        <div style="padding: 10px; background: #e8f5e9; color: #2e7d32; border-radius: 5px;">
            <?php echo $mensaje; ?>
        </div>
        <?php endif; ?>
        
        <form method="POST">
            <div style="margin-bottom: 15px;">
                <label>Host de Base de Datos:</label>
                <input type="text" name="db_host" value="localhost" required style="width: 100%; padding: 8px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Usuario:</label>
                <input type="text" name="db_user" value="root" required style="width: 100%; padding: 8px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Contraseña:</label>
                <input type="password" name="db_pass" style="width: 100%; padding: 8px;">
            </div>
            
            <div style="margin-bottom: 15px;">
                <label>Nombre de BD:</label>
                <input type="text" name="db_name" value="sistema_alcoholimetros" required style="width: 100%; padding: 8px;">
            </div>
            
            <button type="submit" style="width: 100%; padding: 10px; background: #667eea; color: white; border: none; border-radius: 5px; cursor: pointer;">
                Instalar Base de Datos
            </button>
        </form>
    </div>
</body>
</html>