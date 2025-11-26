<?php
// ========================================
// FUNCIONES PRINCIPALES
// ========================================

function estaLogueado() {
    return isset($_SESSION['usuario_id']) && $_SESSION['usuario_id'] > 0;
}

function esSuperAdmin() {
    return isset($_SESSION['es_super_admin']) && $_SESSION['es_super_admin'] == 1;
}

function obtenerClienteActual() {
    if (esSuperAdmin() && isset($_SESSION['cliente_seleccionado'])) {
        return $_SESSION['cliente_seleccionado'];
    }
    return isset($_SESSION['cliente_id']) ? $_SESSION['cliente_id'] : null;
}

function limpiarDato($dato) {
    $dato = trim($dato);
    $dato = stripslashes($dato);
    $dato = htmlspecialchars($dato);
    return $dato;
}

function generarToken($longitud = 32) {
    return bin2hex(random_bytes($longitud / 2));
}

function encriptarPassword($password) {
    return password_hash($password, PASSWORD_DEFAULT);
}

function verificarPassword($password, $hash) {
    return password_verify($password, $hash);
}

function registrarAuditoria($pdo, $accion, $tabla = null, $registro_id = null, $detalles = null) {
    $cliente_id = obtenerClienteActual();
    $usuario_id = isset($_SESSION['usuario_id']) ? $_SESSION['usuario_id'] : null;
    $ip = isset($_SERVER['REMOTE_ADDR']) ? $_SERVER['REMOTE_ADDR'] : '';
    $user_agent = isset($_SERVER['HTTP_USER_AGENT']) ? $_SERVER['HTTP_USER_AGENT'] : '';
    
    $sql = "INSERT INTO auditoria (cliente_id, usuario_id, accion, tabla_afectada, registro_id, 
            detalles, ip_address, user_agent, fecha_accion) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id, $usuario_id, $accion, $tabla, $registro_id, $detalles, $ip, $user_agent]);
}

function obtenerPlanCliente($pdo, $cliente_id) {
    $sql = "SELECT p.*, c.fecha_vencimiento 
            FROM planes p 
            INNER JOIN clientes c ON c.plan_id = p.id 
            WHERE c.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    return $stmt->fetch();
}

function obtenerEstadisticasCliente($pdo, $cliente_id) {
    $stats = array(
        'pruebas_mes' => 0,
        'pruebas_aprobado' => 0,
        'pruebas_reprobado' => 0,
        'usuarios_activos' => 0,
        'alcoholimetros_activos' => 0
    );
    
    // Total pruebas del mes
    $sql = "SELECT COUNT(*) as total FROM pruebas 
            WHERE cliente_id = ? AND MONTH(fecha_prueba) = MONTH(NOW())";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $result = $stmt->fetch();
    $stats['pruebas_mes'] = $result ? $result['total'] : 0;
    
    // Pruebas aprobadas vs reprobadas
    $sql = "SELECT resultado, COUNT(*) as total FROM pruebas 
            WHERE cliente_id = ? AND MONTH(fecha_prueba) = MONTH(NOW()) 
            GROUP BY resultado";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    while ($row = $stmt->fetch()) {
        $stats['pruebas_' . $row['resultado']] = $row['total'];
    }
    
    // Usuarios activos
    $sql = "SELECT COUNT(*) as total FROM usuarios WHERE cliente_id = ? AND estado = 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $result = $stmt->fetch();
    $stats['usuarios_activos'] = $result ? $result['total'] : 0;
    
    // Alcoholímetros activos
    $sql = "SELECT COUNT(*) as total FROM alcoholimetros WHERE cliente_id = ? AND estado = 'activo'";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $result = $stmt->fetch();
    $stats['alcoholimetros_activos'] = $result ? $result['total'] : 0;
    
    return $stats;
}

function formatearFecha($fecha, $formato = 'd/m/Y H:i') {
    if (empty($fecha)) return '-';
    return date($formato, strtotime($fecha));
}

function diasRestantes($fecha) {
    $fecha_futura = strtotime($fecha);
    $fecha_actual = time();
    $diferencia = $fecha_futura - $fecha_actual;
    return floor($diferencia / (60 * 60 * 24));
}
?>