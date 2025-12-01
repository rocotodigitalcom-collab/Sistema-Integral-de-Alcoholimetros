<?php
session_start();

// Registrar logout en auditoría
if (isset($_SESSION['user_id'])) {
    require_once 'config.php';
	require_once __DIR__ . '/includes/Database.php';
	
    $db = new Database();
    $conn = $db->getConnection();
    
    $db->query("
        INSERT INTO auditoria (cliente_id, usuario_id, accion, tabla_afectada, registro_id, detalles, ip_address, user_agent)
        VALUES (?, ?, 'LOGOUT', 'usuarios', ?, 'Cierre de sesión', ?, ?)
    ", [
        $_SESSION['cliente_id'], 
        $_SESSION['user_id'], 
        $_SESSION['user_id'],
        $_SERVER['REMOTE_ADDR'],
        $_SERVER['HTTP_USER_AGENT']
    ]);
}

// Destruir todas las variables de sesión
$_SESSION = array();

// Si se desea destruir la sesión completamente, borre también la cookie de sesión.
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Finalmente, destruir la sesión.
session_destroy();

// Redirigir al login
header('Location: login.php');
exit;
?>