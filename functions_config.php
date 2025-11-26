<?php
// ========================================
// FUNCIONES DE CONFIGURACIÓN
// ========================================

// Función para realizar backup
function realizarBackup($pdo, $cliente_id) {
    $fecha = date('Y-m-d_H-i-s');
    $filename = "backup_{$cliente_id}_{$fecha}.sql";
    $filepath = UPLOAD_PATH . 'backups/' . $filename;
    
    // Crear directorio si no existe
    if (!file_exists(UPLOAD_PATH . 'backups/')) {
        mkdir(UPLOAD_PATH . 'backups/', 0777, true);
    }
    
    // Obtener todas las tablas
    $tablas = [
        'usuarios',
        'pruebas',
        'alcoholimetros',
        'vehiculos',
        'configuraciones'
    ];
    
    $backup = "-- Backup Sistema Alcoholimetros\n";
    $backup .= "-- Cliente ID: $cliente_id\n";
    $backup .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n";
    
    foreach ($tablas as $tabla) {
        // Estructura de la tabla
        $sql = "SHOW CREATE TABLE $tabla";
        $stmt = $pdo->prepare($sql);
        $stmt->execute();
        $row = $stmt->fetch();
        $backup .= "\n-- Tabla: $tabla\n";
        $backup .= $row['Create Table'] . ";\n\n";
        
        // Datos de la tabla (solo del cliente)
        $sql = "SELECT * FROM $tabla WHERE cliente_id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$cliente_id]);
        
        while ($row = $stmt->fetch()) {
            $backup .= "INSERT INTO $tabla VALUES (";
            $values = [];
            foreach ($row as $value) {
                if (is_null($value)) {
                    $values[] = "NULL";
                } else {
                    $values[] = "'" . addslashes($value) . "'";
                }
            }
            $backup .= implode(", ", $values);
            $backup .= ");\n";
        }
    }
    
    // Guardar archivo
    if (file_put_contents($filepath, $backup)) {
        // Registrar en BD
        $sql = "INSERT INTO backups (cliente_id, archivo, tamanio, tipo, fecha_creacion) 
                VALUES (?, ?, ?, 'manual', NOW())";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            $cliente_id,
            $filename,
            filesize($filepath)
        ]);
        
        return $filename;
    }
    
    return false;
}

// Función para validar permisos de acceso
function tienePermiso($pdo, $usuario_id, $permiso) {
    $sql = "SELECT COUNT(*) as tiene
            FROM usuarios u
            JOIN rol_permisos rp ON rp.rol_id = u.rol_id
            JOIN permisos p ON p.id = rp.permiso_id
            WHERE u.id = ? AND p.codigo = ?";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$usuario_id, $permiso]);
    $result = $stmt->fetch();
    
    return $result['tiene'] > 0;
}

// Función para obtener configuración específica
function obtenerConfiguracion($pdo, $cliente_id, $clave) {
    $sql = "SELECT * FROM configuraciones WHERE cliente_id = ? LIMIT 1";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $config = $stmt->fetch();
    
    return isset($config[$clave]) ? $config[$clave] : null;
}

// Función para guardar log de configuración
function logConfiguracion($pdo, $cliente_id, $usuario_id, $seccion, $cambios) {
    $sql = "INSERT INTO logs_configuracion 
            (cliente_id, usuario_id, seccion, cambios, fecha) 
            VALUES (?, ?, ?, ?, NOW())";
    
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        $cliente_id,
        $usuario_id,
        $seccion,
        json_encode($cambios)
    ]);
}
?>