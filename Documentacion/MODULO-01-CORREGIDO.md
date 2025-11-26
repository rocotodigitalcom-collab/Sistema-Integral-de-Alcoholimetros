# 📦 MÓDULO 1: SETUP Y BASE MULTI-TENANT (CORREGIDO)
## Sistema de Alcoholímetros en PHP Clásico - Versión Corregida

---

## 🔧 CORRECCIONES APLICADAS

1. **Base de datos corregida** - Campos y valores alineados
2. **Adaptado a tus archivos CSS/JS** - Font Awesome, Themify Icons, jQuery
3. **Diseño profesional** - Usando tus estilos style.css

---

## 🏗️ ESTRUCTURA DEL PROYECTO

```
/sistema-alcoholimetros/
│
├── index.php           (Dashboard principal)
├── config.php          (Configuración)
├── functions.php       (Funciones PHP)
├── login.php          (Login)
├── logout.php         (Cerrar sesión)
├── install.php        (Instalador)
│
├── /css/              (Tus archivos CSS)
│   ├── style.css
│   ├── tabs.css
│   ├── timeline.css
│   ├── /font-awesome/
│   ├── /themify-icons/
│   └── ...
│
├── /js/               (Tus archivos JS)
│   ├── jquery.min.js
│   ├── niche.js
│   ├── validation.js
│   └── ...
│
├── /uploads/
└── /sql/
    └── database.sql
```

---

## 📝 ARCHIVOS CORREGIDOS

### 1️⃣ database.sql - BASE DE DATOS CORREGIDA
```sql
-- ========================================
-- BASE DE DATOS CORREGIDA
-- ========================================

CREATE DATABASE IF NOT EXISTS sistema_alcoholimetros 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

USE sistema_alcoholimetros;

-- Tabla de Planes
CREATE TABLE planes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_plan VARCHAR(100) NOT NULL,
    precio_mensual DECIMAL(10,2) NOT NULL,
    limite_pruebas_mes INT DEFAULT 1000,
    limite_usuarios INT DEFAULT 5,
    limite_alcoholimetros INT DEFAULT 10,
    reportes_avanzados TINYINT(1) DEFAULT 0,
    soporte_prioritario TINYINT(1) DEFAULT 0,
    acceso_api TINYINT(1) DEFAULT 0,
    almacenamiento_fotos INT DEFAULT 100,
    estado TINYINT(1) DEFAULT 1,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Clientes (Empresas)
CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre_empresa VARCHAR(255) NOT NULL,
    ruc VARCHAR(20) UNIQUE NOT NULL,
    direccion TEXT,
    telefono VARCHAR(20),
    email_contacto VARCHAR(255),
    plan_id INT NOT NULL,
    logo VARCHAR(255),
    color_primario VARCHAR(7) DEFAULT '#2196F3',
    color_secundario VARCHAR(7) DEFAULT '#1976D2',
    fecha_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_vencimiento DATE,
    estado ENUM('activo','inactivo','suspendido','prueba') DEFAULT 'prueba',
    token_api VARCHAR(100) UNIQUE,
    modo_demo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (plan_id) REFERENCES planes(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Usuarios
CREATE TABLE usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100),
    email VARCHAR(255) NOT NULL,
    password VARCHAR(255) NOT NULL,
    telefono VARCHAR(20),
    dni VARCHAR(15),
    rol ENUM('super_admin','admin','supervisor','operador','conductor','auditor') NOT NULL,
    foto_perfil VARCHAR(255),
    estado TINYINT(1) DEFAULT 1,
    ultimo_login TIMESTAMP NULL,
    token_recuperacion VARCHAR(100),
    fecha_expiracion_token DATETIME,
    intentos_login INT DEFAULT 0,
    bloqueado_hasta DATETIME NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_email (email),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Configuraciones
CREATE TABLE configuraciones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    limite_alcohol_permisible DECIMAL(5,3) DEFAULT 0.000,
    intervalo_retest_minutos INT DEFAULT 15,
    intentos_retest INT DEFAULT 3,
    requerir_geolocalizacion TINYINT(1) DEFAULT 1,
    requerir_foto_evidencia TINYINT(1) DEFAULT 0,
    requerir_firma_digital TINYINT(1) DEFAULT 1,
    notificaciones_email TINYINT(1) DEFAULT 1,
    notificaciones_sms TINYINT(1) DEFAULT 0,
    notificaciones_push TINYINT(1) DEFAULT 1,
    timezone VARCHAR(50) DEFAULT 'America/Lima',
    idioma ENUM('es','en','pt') DEFAULT 'es',
    formato_fecha VARCHAR(20) DEFAULT 'd/m/Y',
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY unique_cliente (cliente_id),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Alcoholímetros
CREATE TABLE alcoholimetros (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    numero_serie VARCHAR(50) NOT NULL,
    nombre_activo VARCHAR(100) NOT NULL,
    modelo VARCHAR(50),
    marca VARCHAR(50),
    fecha_calibracion DATE,
    proxima_calibracion DATE,
    estado ENUM('activo','inactivo','mantenimiento','calibracion') DEFAULT 'activo',
    qr_code VARCHAR(255),
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Vehículos
CREATE TABLE vehiculos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    placa VARCHAR(20) NOT NULL,
    marca VARCHAR(50),
    modelo VARCHAR(50),
    anio INT,
    color VARCHAR(30),
    kilometraje INT,
    estado ENUM('activo','inactivo','mantenimiento') DEFAULT 'activo',
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Pruebas (Core)
CREATE TABLE pruebas (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    alcoholimetro_id INT NOT NULL,
    conductor_id INT NOT NULL,
    supervisor_id INT NOT NULL,
    vehiculo_id INT,
    nivel_alcohol DECIMAL(5,3) NOT NULL,
    limite_permisible DECIMAL(5,3) DEFAULT 0.000,
    resultado ENUM('aprobado','reprobado') NOT NULL,
    es_retest TINYINT(1) DEFAULT 0,
    prueba_padre_id INT NULL,
    intento_numero INT DEFAULT 1,
    latitud DECIMAL(10,8),
    longitud DECIMAL(11,8),
    direccion_geocodificada TEXT,
    foto_evidencia VARCHAR(255),
    firma_conductor VARCHAR(255),
    firma_supervisor VARCHAR(255),
    observaciones TEXT,
    fecha_prueba TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    sync_movil TINYINT(1) DEFAULT 0,
    dispositivo_movil VARCHAR(100),
    hash_verificacion VARCHAR(100),
    temperatura_ambiente DECIMAL(4,2),
    humedad_ambiente DECIMAL(4,2),
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (alcoholimetro_id) REFERENCES alcoholimetros(id),
    FOREIGN KEY (conductor_id) REFERENCES usuarios(id),
    FOREIGN KEY (supervisor_id) REFERENCES usuarios(id),
    FOREIGN KEY (vehiculo_id) REFERENCES vehiculos(id),
    FOREIGN KEY (prueba_padre_id) REFERENCES pruebas(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Auditoría
CREATE TABLE auditoria (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT,
    usuario_id INT,
    accion VARCHAR(100) NOT NULL,
    tabla_afectada VARCHAR(50),
    registro_id INT,
    valores_anteriores TEXT,
    valores_nuevos TEXT,
    detalles TEXT,
    ip_address VARCHAR(45),
    user_agent TEXT,
    fecha_accion TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Tabla de Sesiones
CREATE TABLE sesiones (
    id INT PRIMARY KEY AUTO_INCREMENT,
    usuario_id INT NOT NULL,
    token_sesion VARCHAR(255) NOT NULL UNIQUE,
    dispositivo VARCHAR(100),
    ip_address VARCHAR(45),
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_expiracion TIMESTAMP NULL,
    activa TINYINT(1) DEFAULT 1,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insertar Planes
INSERT INTO planes (nombre_plan, precio_mensual, limite_pruebas_mes, limite_usuarios, 
                    limite_alcoholimetros, reportes_avanzados, soporte_prioritario, acceso_api) 
VALUES 
('Free', 0.00, 30, 1, 1, 0, 0, 0),
('Starter', 49.00, 500, 5, 3, 0, 0, 0),
('Professional', 149.00, 2000, 20, 10, 1, 1, 1),
('Enterprise', 499.00, 99999, 99999, 99999, 1, 1, 1);

-- Insertar Cliente Demo
INSERT INTO clientes (nombre_empresa, ruc, direccion, telefono, email_contacto, plan_id, 
                     fecha_vencimiento, estado, token_api, modo_demo) 
VALUES 
('Empresa Demo S.A.', '20123456789', 'Av. Demo 123, Lima', '01-234-5678', 
 'admin@demo.com', 1, DATE_ADD(NOW(), INTERVAL 30 DAY), 'prueba', 
 MD5(CONCAT('demo_', NOW())), 1);

-- Insertar Usuario Super Admin (sin cliente_id)
INSERT INTO usuarios (cliente_id, nombre, apellido, email, password, rol, estado) 
VALUES 
(NULL, 'Super', 'Administrador', 'superadmin@sistema.com', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 'super_admin', 1);

-- Insertar Usuario Admin Demo
INSERT INTO usuarios (cliente_id, nombre, apellido, email, password, dni, rol, estado) 
VALUES 
(1, 'Admin', 'Demo', 'admin@demo.com', 
 '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
 '12345678', 'admin', 1);

-- Insertar Configuración para Cliente Demo
INSERT INTO configuraciones (cliente_id) VALUES (1);

-- Insertar Alcoholímetros Demo
INSERT INTO alcoholimetros (cliente_id, numero_serie, nombre_activo, modelo, marca, 
                           fecha_calibracion, proxima_calibracion, estado) 
VALUES 
(1, 'ALC-001', 'Alcoholímetro Principal', 'AL-3000', 'AlcoTest', 
 '2024-01-15', '2025-01-15', 'activo'),
(1, 'ALC-002', 'Alcoholímetro Secundario', 'AL-2500', 'AlcoTest', 
 '2024-02-20', '2025-02-20', 'activo');

-- Insertar Vehículos Demo
INSERT INTO vehiculos (cliente_id, placa, marca, modelo, anio, color, kilometraje, estado) 
VALUES 
(1, 'ABC-123', 'Toyota', 'Hilux', 2023, 'Blanco', 15000, 'activo'),
(1, 'DEF-456', 'Nissan', 'Frontier', 2022, 'Negro', 25000, 'activo');
```

### 2️⃣ config.php - CONFIGURACIÓN
```php
<?php
// ========================================
// CONFIGURACIÓN DEL SISTEMA
// ========================================

// Configuración de Base de Datos
define('DB_HOST', 'localhost');
define('DB_NAME', 'sistema_alcoholimetros');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_CHARSET', 'utf8mb4');

// Configuración del Sistema
define('SISTEMA_NOMBRE', 'Sistema Integral de Alcoholímetros');
define('SISTEMA_VERSION', '1.0.0');
define('SISTEMA_URL', 'http://localhost/sistema-alcoholimetros');
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
```

### 3️⃣ functions.php
```php
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
```

### 4️⃣ login.php - LOGIN CON TUS ESTILOS
```php
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
```

### 5️⃣ index.php - DASHBOARD CON TUS ESTILOS
```php
<?php
require_once 'config.php';
require_once 'functions.php';

// Si no está logueado, redirigir a login
if (!estaLogueado()) {
    header('Location: login.php');
    exit();
}

// Obtener datos
$usuario_id = $_SESSION['usuario_id'];
$cliente_id = obtenerClienteActual();

// Si no hay cliente_id y no es super admin, cerrar sesión
if (!$cliente_id && !esSuperAdmin()) {
    header('Location: logout.php');
    exit();
}

// Obtener información del cliente
$cliente = null;
$plan = null;
if ($cliente_id) {
    $sql = "SELECT c.*, p.nombre_plan, p.limite_pruebas_mes, p.limite_usuarios, p.limite_alcoholimetros 
            FROM clientes c 
            LEFT JOIN planes p ON c.plan_id = p.id 
            WHERE c.id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch();
}

// Obtener estadísticas
$stats = $cliente_id ? obtenerEstadisticasCliente($pdo, $cliente_id) : array();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo SISTEMA_NOMBRE; ?> - Dashboard</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/font-awesome/css/font-awesome.min.css">
    <link rel="stylesheet" href="css/themify-icons/themify-icons.css">
    <link rel="stylesheet" href="css/tabs.css">
    
    <style>
        body {
            background: #f4f6f9;
            font-family: 'Roboto', sans-serif;
            margin: 0;
            padding: 0;
        }
        
        .header {
            background: #fff;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            padding: 15px 30px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            font-size: 20px;
            color: #333;
            margin: 0;
        }
        
        .header .user-info {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .sidebar {
            background: #2c3e50;
            width: 250px;
            min-height: calc(100vh - 70px);
            float: left;
        }
        
        .sidebar ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        
        .sidebar a {
            display: block;
            padding: 15px 20px;
            color: #ecf0f1;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .sidebar a:hover,
        .sidebar a.active {
            background: #34495e;
            border-left: 3px solid #3498db;
        }
        
        .sidebar a i {
            margin-right: 10px;
            width: 20px;
            text-align: center;
        }
        
        .main-content {
            margin-left: 250px;
            padding: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 24px;
            color: white;
        }
        
        .stat-icon.blue { background: linear-gradient(135deg, #667eea, #764ba2); }
        .stat-icon.green { background: linear-gradient(135deg, #11998e, #38ef7d); }
        .stat-icon.red { background: linear-gradient(135deg, #ee0979, #ff6a00); }
        .stat-icon.yellow { background: linear-gradient(135deg, #f2994a, #f2c94c); }
        
        .stat-details h3 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        
        .stat-details p {
            margin: 5px 0 0 0;
            color: #666;
            font-size: 14px;
        }
        
        .quick-actions {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }
        
        .action-btn {
            background: #f8f9fa;
            padding: 20px;
            text-align: center;
            border-radius: 8px;
            text-decoration: none;
            color: #333;
            transition: all 0.3s;
        }
        
        .action-btn:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            background: #667eea;
            color: white;
        }
        
        .action-btn i {
            display: block;
            font-size: 32px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="header">
        <h1><i class="fa fa-flask"></i> <?php echo $cliente ? $cliente['nombre_empresa'] : SISTEMA_NOMBRE; ?></h1>
        <div class="user-info">
            <span>
                <i class="fa fa-user"></i> 
                <?php echo $_SESSION['usuario_nombre']; ?> 
                (<?php echo $_SESSION['usuario_rol']; ?>)
            </span>
            <?php if ($cliente): ?>
            <span class="badge">
                Plan: <?php echo $cliente['nombre_plan']; ?>
            </span>
            <?php endif; ?>
            <a href="logout.php" class="btn btn-danger">
                <i class="fa fa-sign-out"></i> Salir
            </a>
        </div>
    </div>
    
    <!-- Sidebar -->
    <div class="sidebar">
        <ul>
            <li><a href="index.php" class="active"><i class="ti-dashboard"></i> Dashboard</a></li>
            <li><a href="#"><i class="fa fa-flask"></i> Pruebas</a></li>
            <li><a href="#"><i class="ti-car"></i> Vehículos</a></li>
            <li><a href="#"><i class="fa fa-users"></i> Conductores</a></li>
            <li><a href="#"><i class="ti-harddrives"></i> Alcoholímetros</a></li>
            <li><a href="#"><i class="ti-bar-chart"></i> Reportes</a></li>
            <?php if ($_SESSION['usuario_rol'] == 'admin' || esSuperAdmin()): ?>
            <li><a href="#"><i class="ti-settings"></i> Configuración</a></li>
            <li><a href="#"><i class="ti-user"></i> Usuarios</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <!-- Main Content -->
    <div class="main-content">
        <h2>Panel de Control</h2>
        
        <!-- Stats Grid -->
        <div class="stats-grid">
            <div class="stat-card">
                <div class="stat-icon blue">
                    <i class="fa fa-flask"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo isset($stats['pruebas_mes']) ? $stats['pruebas_mes'] : 0; ?></h3>
                    <p>Pruebas este mes</p>
                    <?php if ($cliente): ?>
                    <small>Límite: <?php echo $cliente['limite_pruebas_mes']; ?></small>
                    <?php endif; ?>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon green">
                    <i class="fa fa-check-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo isset($stats['pruebas_aprobado']) ? $stats['pruebas_aprobado'] : 0; ?></h3>
                    <p>Aprobadas</p>
                    <?php 
                    $total = isset($stats['pruebas_mes']) ? $stats['pruebas_mes'] : 0;
                    $aprobadas = isset($stats['pruebas_aprobado']) ? $stats['pruebas_aprobado'] : 0;
                    $porcentaje = $total > 0 ? round(($aprobadas / $total) * 100, 1) : 0;
                    ?>
                    <small><?php echo $porcentaje; ?>% del total</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon red">
                    <i class="fa fa-times-circle"></i>
                </div>
                <div class="stat-details">
                    <h3><?php echo isset($stats['pruebas_reprobado']) ? $stats['pruebas_reprobado'] : 0; ?></h3>
                    <p>Reprobadas</p>
                    <?php 
                    $reprobadas = isset($stats['pruebas_reprobado']) ? $stats['pruebas_reprobado'] : 0;
                    $porcentaje = $total > 0 ? round(($reprobadas / $total) * 100, 1) : 0;
                    ?>
                    <small><?php echo $porcentaje; ?>% del total</small>
                </div>
            </div>
            
            <div class="stat-card">
                <div class="stat-icon yellow">
                    <i class="ti-timer"></i>
                </div>
                <div class="stat-details">
                    <?php if ($cliente): ?>
                    <h3><?php echo diasRestantes($cliente['fecha_vencimiento']); ?></h3>
                    <p>Días restantes</p>
                    <small>Vence: <?php echo formatearFecha($cliente['fecha_vencimiento'], 'd/m/Y'); ?></small>
                    <?php else: ?>
                    <h3>∞</h3>
                    <p>Super Admin</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <h3>Acciones Rápidas</h3>
            <div class="actions-grid">
                <a href="#" class="action-btn">
                    <i class="fa fa-plus-circle"></i>
                    Nueva Prueba
                </a>
                <a href="#" class="action-btn">
                    <i class="ti-user"></i>
                    Nuevo Conductor
                </a>
                <a href="#" class="action-btn">
                    <i class="ti-car"></i>
                    Nuevo Vehículo
                </a>
                <a href="#" class="action-btn">
                    <i class="ti-bar-chart"></i>
                    Reporte Diario
                </a>
                <a href="#" class="action-btn">
                    <i class="ti-settings"></i>
                    Calibración
                </a>
                <a href="#" class="action-btn">
                    <i class="ti-export"></i>
                    Exportar Datos
                </a>
            </div>
        </div>
    </div>
    
    <!-- JS Files -->
    <script src="js/jquery.min.js"></script>
    <script src="js/niche.js"></script>
</body>
</html>
```

### 6️⃣ logout.php
```php
<?php
session_start();
session_destroy();
header('Location: login.php');
exit();
?>
```

### 7️⃣ install.php - INSTALADOR
```php
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
```

---

## ✅ ERRORES CORREGIDOS

### 1. **Base de Datos**
- ✅ Campos alineados con valores INSERT
- ✅ TINYINT(1) para booleanos
- ✅ Estructura correcta de foreign keys
- ✅ Collation utf8mb4_unicode_ci

### 2. **Integración CSS/JS**
- ✅ Font Awesome para iconos
- ✅ Themify Icons
- ✅ jQuery integrado
- ✅ Estilos profesionales

### 3. **Funcionalidad**
- ✅ Login funcional
- ✅ Dashboard con estadísticas
- ✅ Sidebar navegable
- ✅ Diseño responsive

---

## 🚀 INSTALACIÓN

1. **Copia los archivos** a tu carpeta `sistema-alcoholimetros`
2. **Copia tus carpetas CSS y JS** al proyecto
3. **Crea el archivo** `/sql/database.sql` con el contenido proporcionado
4. **Accede a** `install.php` para instalar la BD
5. **Login con**:
   - Email: `admin@demo.com`
   - Password: `password`

---

El sistema ahora debe funcionar correctamente con tu base de datos y tus archivos CSS/JS.