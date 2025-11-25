<?php
// ========================================
// CONFIGURACIÓN DEL SISTEMA
// ========================================

// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'juegosd2_alcohol');
define('DB_USER', 'juegosd2_alcohol');
define('DB_PASS', '#Peru07128020@');
define('DB_CHARSET', 'utf8mb4');

// Configuración del Sistema
define('SISTEMA_NOMBRE', 'Sistema Integral de Alcoholímetros');
define('SISTEMA_VERSION', '1.0.0');
define('SISTEMA_URL', 'https://alcoholimetro.rocotodigital.com');
define('TIMEZONE', 'America/Lima');

// Sesiones
define('SESSION_LIFETIME', 3600);
define('SESSION_NAME', 'alcoholimetros_session');

// Archivos
define('MAX_UPLOAD_SIZE', 5242880); // 5 MB
define('UPLOAD_PATH', __DIR__ . '/uploads/');

// Niveles de Alcohol (g/L)
define('ALCOHOL_APROBADO', 0.024);
define('ALCOHOL_ADVERTENCIA', 0.049);
define('ALCOHOL_REPROBADO', 0.05);
define('ALCOHOL_CRITICO', 0.08);

// Re-test
define('RETEST_INTERVALO_MINUTOS', 15);
define('RETEST_INTENTOS_MAX', 3);

// Zona horaria
date_default_timezone_set(TIMEZONE);

// Iniciar sesión
session_name(SESSION_NAME);
session_start();

// Conexión a Base de Datos
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET,
        DB_USER,
        DB_PASS,
        array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        )
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
?>