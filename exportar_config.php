<?php
require_once 'config.php';
require_once 'functions.php';

if (!estaLogueado() || $_SESSION['usuario_rol'] != 'admin') {
    die('Sin permisos');
}

$cliente_id = obtenerClienteActual();

// Exportar configuración
if (isset($_GET['exportar'])) {
    $sql = "SELECT * FROM configuraciones WHERE cliente_id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $config = $stmt->fetch();
    
    // Agregar información del cliente
    $sql = "SELECT * FROM clientes WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();
    
    $export = [
        'version' => SISTEMA_VERSION,
        'fecha_export' => date('Y-m-d H:i:s'),
        'cliente' => $cliente,
        'configuracion' => $config
    ];
    
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="config_backup_' . date('Ymd_His') . '.json"');
    echo json_encode($export, JSON_PRETTY_PRINT);
    exit();
}

// Importar configuración
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_FILES['config_file'])) {
    $json = file_get_contents($_FILES['config_file']['tmp_name']);
    $data = json_decode($json, true);
    
    if ($data && isset($data['configuracion'])) {
        $config = $data['configuracion'];
        
        $sql = "UPDATE configuraciones SET 
                limite_alcohol_permisible = ?,
                nivel_advertencia = ?,
                nivel_critico = ?,
                intervalo_retest_minutos = ?,
                intentos_retest = ?
                WHERE cliente_id = ?";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $config['limite_alcohol_permisible'],
            $config['nivel_advertencia'],
            $config['nivel_critico'],
            $config['intervalo_retest_minutos'],
            $config['intentos_retest'],
            $cliente_id
        ]);
        
        header('Location: configuracion.php?imported=1');
    }
}
?>