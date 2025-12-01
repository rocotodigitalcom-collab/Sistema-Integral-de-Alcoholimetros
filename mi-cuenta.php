<?php
// mi-cuenta.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';

$page_title = 'Mi Cuenta';
$breadcrumbs = [
    'index.php' => 'Dashboard',
    'mi-cuenta.php' => 'Mi Cuenta'
];

require_once __DIR__ . '/includes/header.php';

$db = new Database();
$user_id = $_SESSION['user_id'] ?? 0;
$cliente_id = $_SESSION['cliente_id'] ?? 0;

// Obtener información del usuario actual
$usuario_actual = $db->fetchOne("
    SELECT u.*, c.nombre_empresa, c.plan_id, p.nombre_plan 
    FROM usuarios u 
    LEFT JOIN clientes c ON u.cliente_id = c.id 
    LEFT JOIN planes p ON c.plan_id = p.id 
    WHERE u.id = ?
", [$user_id]);

// Obtener historial de actividad reciente
$historial_actividad = $db->fetchAll("
    SELECT accion, detalles, fecha_accion, ip_address 
    FROM auditoria 
    WHERE usuario_id = ? 
    ORDER BY fecha_accion DESC 
    LIMIT 10
", [$user_id]);

// Procesar actualización de información personal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_perfil'])) {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $dni = trim($_POST['dni'] ?? '');
    
    try {
        $db->execute("
            UPDATE usuarios 
            SET nombre = ?, apellido = ?, telefono = ?, dni = ?, fecha_creacion = fecha_creacion 
            WHERE id = ?
        ", [$nombre, $apellido, $telefono, $dni, $user_id]);
        
        $_SESSION['user_nombre'] = $nombre;
        $mensaje_exito = "Perfil actualizado correctamente";
        
        // Registrar en auditoría
        $db->execute("
            INSERT INTO auditoria (cliente_id, usuario_id, accion, tabla_afectada, registro_id, detalles, ip_address, user_agent)
            VALUES (?, ?, 'ACTUALIZACION_PERFIL', 'usuarios', ?, ?, ?, ?)
        ", [$cliente_id, $user_id, $user_id, "Usuario actualizó su perfil", $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
        
    } catch (Exception $e) {
        $mensaje_error = "Error al actualizar el perfil: " . $e->getMessage();
    }
}

// Procesar cambio de contraseña
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_password'])) {
    $password_actual = $_POST['password_actual'] ?? '';
    $nuevo_password = $_POST['nuevo_password'] ?? '';
    $confirmar_password = $_POST['confirmar_password'] ?? '';
    
    // Verificar contraseña actual
    if (password_verify($password_actual, $usuario_actual['password'])) {
        if ($nuevo_password === $confirmar_password) {
            if (strlen($nuevo_password) >= 6) {
                $password_hash = password_hash($nuevo_password, PASSWORD_DEFAULT);
                $db->execute("UPDATE usuarios SET password = ? WHERE id = ?", [$password_hash, $user_id]);
                
                $mensaje_exito = "Contraseña actualizada correctamente";
                
                // Registrar en auditoría
                $db->execute("
                    INSERT INTO auditoria (cliente_id, usuario_id, accion, tabla_afectada, registro_id, detalles, ip_address, user_agent)
                    VALUES (?, ?, 'CAMBIO_PASSWORD', 'usuarios', ?, ?, ?, ?)
                ", [$cliente_id, $user_id, $user_id, "Usuario cambió su contraseña", $_SERVER['REMOTE_ADDR'], $_SERVER['HTTP_USER_AGENT']]);
                
            } else {
                $mensaje_error = "La nueva contraseña debe tener al menos 6 caracteres";
            }
        } else {
            $mensaje_error = "Las contraseñas nuevas no coinciden";
        }
    } else {
        $mensaje_error = "La contraseña actual es incorrecta";
    }
}

// Procesar actualización de preferencias
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_preferencias'])) {
    $timezone = $_POST['timezone'] ?? 'America/Lima';
    $idioma = $_POST['idioma'] ?? 'es';
    $formato_fecha = $_POST['formato_fecha'] ?? 'd/m/Y';
    
    try {
        $db->execute("
            UPDATE configuraciones 
            SET timezone = ?, idioma = ?, formato_fecha = ? 
            WHERE cliente_id = ?
        ", [$timezone, $idioma, $formato_fecha, $cliente_id]);
        
        $mensaje_exito = "Preferencias actualizadas correctamente";
        
    } catch (Exception $e) {
        $mensaje_error = "Error al actualizar preferencias: " . $e->getMessage();
    }
}
?>

<div class="content-body">
    <!-- Header de Mi Cuenta -->
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1>Mi Cuenta</h1>
            <p class="dashboard-subtitle">Gestiona tu información personal, seguridad y preferencias</p>
        </div>
        <div class="header-actions">
            <span class="user-badge">
                <i class="fas fa-user-circle"></i>
                <?php echo htmlspecialchars($usuario_actual['rol'] ?? 'Usuario'); ?>
            </span>
        </div>
    </div>

    <!-- Mensajes de alerta -->
    <?php if (isset($mensaje_exito)): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo $mensaje_exito; ?>
    </div>
    <?php endif; ?>

    <?php if (isset($mensaje_error)): ?>
    <div class="alert alert-danger">
        <i class="fas fa-exclamation-circle"></i>
        <?php echo $mensaje_error; ?>
    </div>
    <?php endif; ?>

    <div class="account-container">
        <!-- Panel lateral de navegación -->
        <div class="account-sidebar">
            <div class="user-profile-card">
                <div class="user-avatar-large">
                    <div class="avatar-initials-large">
                        <?php 
                        $iniciales = substr($usuario_actual['nombre'] ?? 'U', 0, 1) . 
                                    substr($usuario_actual['apellido'] ?? 'S', 0, 1);
                        echo strtoupper($iniciales);
                        ?>
                    </div>
                </div>
                <div class="user-info-summary">
                    <h3><?php echo htmlspecialchars($usuario_actual['nombre'] . ' ' . $usuario_actual['apellido']); ?></h3>
                    <p class="user-email"><?php echo htmlspecialchars($usuario_actual['email']); ?></p>
                    <p class="user-role">
                        <span class="role-badge <?php echo htmlspecialchars($usuario_actual['rol']); ?>">
                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $usuario_actual['rol']))); ?>
                        </span>
                    </p>
                </div>
                <div class="user-stats">
                    <div class="stat-item">
                        <i class="fas fa-building"></i>
                        <span><?php echo htmlspecialchars($usuario_actual['nombre_empresa'] ?? 'Empresa Demo'); ?></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-cube"></i>
                        <span>Plan <?php echo htmlspecialchars($usuario_actual['nombre_plan'] ?? 'Free'); ?></span>
                    </div>
                    <div class="stat-item">
                        <i class="fas fa-calendar"></i>
                        <span>Miembro desde <?php echo date('M Y', strtotime($usuario_actual['fecha_creacion'])); ?></span>
                    </div>
                </div>
            </div>

            <nav class="account-nav">
                <a href="#perfil" class="nav-item active" data-tab="perfil">
                    <i class="fas fa-user"></i>
                    Información Personal
                </a>
                <a href="#seguridad" class="nav-item" data-tab="seguridad">
                    <i class="fas fa-shield-alt"></i>
                    Seguridad
                </a>
                <a href="#preferencias" class="nav-item" data-tab="preferencias">
                    <i class="fas fa-cog"></i>
                    Preferencias
                </a>
                <a href="#actividad" class="nav-item" data-tab="actividad">
                    <i class="fas fa-history"></i>
                    Actividad Reciente
                </a>
            </nav>
        </div>

        <!-- Contenido principal -->
        <div class="account-content">
            <!-- Pestaña: Información Personal -->
            <div id="perfil" class="tab-content active">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-user-edit"></i> Información Personal</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="account-form">
                            <div class="form-grid">
                                <div class="form-group">
                                    <label for="nombre">Nombre *</label>
                                    <input type="text" id="nombre" name="nombre" 
                                           value="<?php echo htmlspecialchars($usuario_actual['nombre'] ?? ''); ?>" 
                                           required class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="apellido">Apellido *</label>
                                    <input type="text" id="apellido" name="apellido" 
                                           value="<?php echo htmlspecialchars($usuario_actual['apellido'] ?? ''); ?>" 
                                           required class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="email">Email *</label>
                                    <input type="email" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($usuario_actual['email'] ?? ''); ?>" 
                                           disabled class="form-control">
                                    <small class="form-text">El email no puede ser modificado</small>
                                </div>
                                <div class="form-group">
                                    <label for="telefono">Teléfono</label>
                                    <input type="tel" id="telefono" name="telefono" 
                                           value="<?php echo htmlspecialchars($usuario_actual['telefono'] ?? ''); ?>" 
                                           class="form-control">
                                </div>
                                <div class="form-group">
                                    <label for="dni">DNI</label>
                                    <input type="text" id="dni" name="dni" 
                                           value="<?php echo htmlspecialchars($usuario_actual['dni'] ?? ''); ?>" 
                                           class="form-control">
                                </div>
                                <div class="form-group full-width">
                                    <label for="rol">Rol en el Sistema</label>
                                    <input type="text" id="rol" 
                                           value="<?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $usuario_actual['rol']))); ?>" 
                                           disabled class="form-control">
                                </div>
                            </div>
                            <div class="form-actions">
                                <button type="submit" name="actualizar_perfil" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Guardar Cambios
                                </button>
                                <button type="reset" class="btn btn-outline">
                                    <i class="fas fa-undo"></i>
                                    Restablecer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Pestaña: Seguridad -->
            <div id="seguridad" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-shield-alt"></i> Seguridad y Contraseña</h3>
                    </div>
                    <div class="card-body">
                        <form method="POST" class="account-form">
                            <div class="security-info">
                                <div class="security-status">
                                    <div class="status-item success">
                                        <i class="fas fa-check-circle"></i>
                                        <div class="status-text">
                                            <strong>Último acceso</strong>
                                            <span><?php echo $usuario_actual['ultimo_login'] ? date('d/m/Y H:i', strtotime($usuario_actual['ultimo_login'])) : 'Nunca'; ?></span>
                                        </div>
                                    </div>
                                    <div class="status-item <?php echo ($usuario_actual['estado'] == 1) ? 'success' : 'warning'; ?>">
                                        <i class="fas fa-<?php echo ($usuario_actual['estado'] == 1) ? 'check' : 'exclamation'; ?>-circle"></i>
                                        <div class="status-text">
                                            <strong>Estado de cuenta</strong>
                                            <span><?php echo ($usuario_actual['estado'] == 1) ? 'Activa' : 'Inactiva'; ?></span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h4>Cambiar Contraseña</h4>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="password_actual">Contraseña Actual *</label>
                                        <input type="password" id="password_actual" name="password_actual" 
                                               required class="form-control" minlength="6">
                                    </div>
                                    <div class="form-group">
                                        <label for="nuevo_password">Nueva Contraseña *</label>
                                        <input type="password" id="nuevo_password" name="nuevo_password" 
                                               required class="form-control" minlength="6">
                                        <small class="form-text">Mínimo 6 caracteres</small>
                                    </div>
                                    <div class="form-group">
                                        <label for="confirmar_password">Confirmar Nueva Contraseña *</label>
                                        <input type="password" id="confirmar_password" name="confirmar_password" 
                                               required class="form-control" minlength="6">
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="cambiar_password" class="btn btn-primary">
                                    <i class="fas fa-key"></i>
                                    Cambiar Contraseña
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Pestaña: Preferencias -->
            <div id="preferencias" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-cog"></i> Preferencias del Sistema</h3>
                    </div>
                    <div class="card-body">
                        <?php
                        // Obtener configuración actual
                        $configuracion = $db->fetchOne("SELECT * FROM configuraciones WHERE cliente_id = ?", [$cliente_id]);
                        ?>
                        <form method="POST" class="account-form">
                            <div class="form-section">
                                <h4>Configuración Regional</h4>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="timezone">Zona Horaria</label>
                                        <select id="timezone" name="timezone" class="form-control">
                                            <option value="America/Lima" <?php echo ($configuracion['timezone'] ?? 'America/Lima') === 'America/Lima' ? 'selected' : ''; ?>>Lima, Perú (UTC-5)</option>
                                            <option value="America/Bogota" <?php echo ($configuracion['timezone'] ?? '') === 'America/Bogota' ? 'selected' : ''; ?>>Bogotá, Colombia (UTC-5)</option>
                                            <option value="America/Mexico_City" <?php echo ($configuracion['timezone'] ?? '') === 'America/Mexico_City' ? 'selected' : ''; ?>>Ciudad de México (UTC-6)</option>
                                            <option value="America/Argentina/Buenos_Aires" <?php echo ($configuracion['timezone'] ?? '') === 'America/Argentina/Buenos_Aires' ? 'selected' : ''; ?>>Buenos Aires, Argentina (UTC-3)</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="idioma">Idioma</label>
                                        <select id="idioma" name="idioma" class="form-control">
                                            <option value="es" <?php echo ($configuracion['idioma'] ?? 'es') === 'es' ? 'selected' : ''; ?>>Español</option>
                                            <option value="en" <?php echo ($configuracion['idioma'] ?? '') === 'en' ? 'selected' : ''; ?>>English</option>
                                            <option value="pt" <?php echo ($configuracion['idioma'] ?? '') === 'pt' ? 'selected' : ''; ?>>Português</option>
                                        </select>
                                    </div>
                                    <div class="form-group">
                                        <label for="formato_fecha">Formato de Fecha</label>
                                        <select id="formato_fecha" name="formato_fecha" class="form-control">
                                            <option value="d/m/Y" <?php echo ($configuracion['formato_fecha'] ?? 'd/m/Y') === 'd/m/Y' ? 'selected' : ''; ?>>DD/MM/YYYY (25/12/2024)</option>
                                            <option value="m/d/Y" <?php echo ($configuracion['formato_fecha'] ?? '') === 'm/d/Y' ? 'selected' : ''; ?>>MM/DD/YYYY (12/25/2024)</option>
                                            <option value="Y-m-d" <?php echo ($configuracion['formato_fecha'] ?? '') === 'Y-m-d' ? 'selected' : ''; ?>>YYYY-MM-DD (2024-12-25)</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <div class="form-section">
                                <h4>Unidades de Medida</h4>
                                <div class="form-grid">
                                    <div class="form-group">
                                        <label for="unidad_medida">Unidad de Alcohol</label>
                                        <select id="unidad_medida" name="unidad_medida" class="form-control" disabled>
                                            <option value="g/L" selected>Gramos por Litro (g/L)</option>
                                            <option value="mg/dL">Miligramos por Decilitro (mg/dL)</option>
                                            <option value="BAC">Blood Alcohol Content (%)</option>
                                        </select>
                                        <small class="form-text">Configuración a nivel de empresa</small>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions">
                                <button type="submit" name="actualizar_preferencias" class="btn btn-primary">
                                    <i class="fas fa-save"></i>
                                    Guardar Preferencias
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Pestaña: Actividad Reciente -->
            <div id="actividad" class="tab-content">
                <div class="card">
                    <div class="card-header">
                        <h3><i class="fas fa-history"></i> Actividad Reciente</h3>
                        <div class="card-actions">
                            <button class="btn btn-outline btn-sm" onclick="refreshActivity()">
                                <i class="fas fa-sync-alt"></i>
                                Actualizar
                            </button>
                        </div>
                    </div>
                    <div class="card-body">
                        <?php if (!empty($historial_actividad)): ?>
                        <div class="activity-timeline">
                            <?php foreach ($historial_actividad as $actividad): ?>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <?php
                                    $icono = 'fa-info-circle';
                                    $color = 'primary';
                                    if (strpos($actividad['accion'], 'LOGIN') !== false) {
                                        $icono = 'fa-sign-in-alt';
                                        $color = 'success';
                                    } elseif (strpos($actividad['accion'], 'LOGOUT') !== false) {
                                        $icono = 'fa-sign-out-alt';
                                        $color = 'warning';
                                    } elseif (strpos($actividad['accion'], 'UPDATE') !== false || strpos($actividad['accion'], 'CREATE') !== false) {
                                        $icono = 'fa-edit';
                                        $color = 'info';
                                    }
                                    ?>
                                    <i class="fas <?php echo $icono; ?> text-<?php echo $color; ?>"></i>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?php echo htmlspecialchars($actividad['accion']); ?>
                                    </div>
                                    <div class="activity-desc">
                                        <?php echo htmlspecialchars($actividad['detalles'] ?? 'Sin detalles'); ?>
                                    </div>
                                    <div class="activity-meta">
                                        <span class="activity-time">
                                            <i class="fas fa-clock"></i>
                                            <?php echo date('d/m/Y H:i', strtotime($actividad['fecha_accion'])); ?>
                                        </span>
                                        <span class="activity-ip">
                                            <i class="fas fa-globe"></i>
                                            <?php echo htmlspecialchars($actividad['ip_address'] ?? 'IP no disponible'); ?>
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <div class="empty-state">
                            <div class="empty-icon">
                                <i class="fas fa-history"></i>
                            </div>
                            <h3>No hay actividad registrada</h3>
                            <p>Tu actividad en el sistema aparecerá aquí</p>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
/* Estilos específicos para Mi Cuenta */
.account-container {
    display: grid;
    grid-template-columns: 300px 1fr;
    gap: 2rem;
    margin-top: 1.5rem;
}

.account-sidebar {
    display: flex;
    flex-direction: column;
    gap: 1.5rem;
}

.user-profile-card {
    background: white;
    border-radius: 12px;
    padding: 2rem;
    text-align: center;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
    border: 1px solid var(--border);
}

.user-avatar-large {
    margin-bottom: 1.5rem;
}

.avatar-initials-large {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    display: flex;
    align-items: center;
    justify-content: center;
    color: white;
    font-size: 1.8rem;
    font-weight: 600;
    margin: 0 auto;
}

.user-info-summary h3 {
    margin: 0 0 0.5rem 0;
    font-size: 1.3rem;
    color: var(--dark);
}

.user-email {
    color: var(--gray);
    margin: 0 0 1rem 0;
}

.role-badge {
    display: inline-block;
    padding: 0.35rem 1rem;
    border-radius: 20px;
    font-size: 0.8rem;
    font-weight: 600;
    text-transform: capitalize;
}

.role-badge.super_admin { background: rgba(231, 76, 60, 0.1); color: var(--danger); }
.role-badge.admin { background: rgba(52, 152, 219, 0.1); color: var(--primary); }
.role-badge.supervisor { background: rgba(243, 156, 18, 0.1); color: var(--warning); }
.role-badge.operador { background: rgba(39, 174, 96, 0.1); color: var(--success); }
.role-badge.conductor { background: rgba(149, 165, 166, 0.1); color: var(--gray); }

.user-stats {
    margin-top: 1.5rem;
    text-align: left;
}

.stat-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 0.75rem 0;
    border-bottom: 1px solid var(--border);
    font-size: 0.9rem;
    color: var(--dark);
}

.stat-item:last-child {
    border-bottom: none;
}

.stat-item i {
    width: 20px;
    color: var(--primary);
}

.account-nav {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    padding: 1rem 1.5rem;
    text-decoration: none;
    color: var(--dark);
    border-left: 4px solid transparent;
    transition: var(--transition);
    font-weight: 500;
}

.nav-item:hover {
    background: var(--light);
    color: var(--primary);
}

.nav-item.active {
    background: rgba(52, 152, 219, 0.05);
    color: var(--primary);
    border-left-color: var(--primary);
}

.nav-item i {
    width: 20px;
    text-align: center;
}

.account-content {
    min-height: 600px;
}

.tab-content {
    display: none;
}

.tab-content.active {
    display: block;
    animation: fadeIn 0.3s ease-in-out;
}

.account-form {
    max-width: 100%;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-section {
    margin-bottom: 2rem;
    padding-bottom: 1.5rem;
    border-bottom: 1px solid var(--border);
}

.form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

.form-section h4 {
    margin: 0 0 1rem 0;
    color: var(--dark);
    font-size: 1.1rem;
}

.form-actions {
    display: flex;
    gap: 1rem;
    justify-content: flex-start;
    padding-top: 1rem;
    border-top: 1px solid var(--border);
}

.security-info {
    margin-bottom: 2rem;
}

.security-status {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 1rem;
}

.status-item {
    display: flex;
    align-items: center;
    gap: 1rem;
    padding: 1rem;
    border-radius: 8px;
    background: var(--light);
}

.status-item.success {
    background: rgba(39, 174, 96, 0.1);
    border-left: 4px solid var(--success);
}

.status-item.warning {
    background: rgba(243, 156, 18, 0.1);
    border-left: 4px solid var(--warning);
}

.status-item i {
    font-size: 1.5rem;
}

.status-text strong {
    display: block;
    color: var(--dark);
    margin-bottom: 0.25rem;
}

.status-text span {
    color: var(--gray);
    font-size: 0.9rem;
}

.activity-timeline {
    display: flex;
    flex-direction: column;
    gap: 1rem;
}

.activity-item {
    display: flex;
    gap: 1rem;
    padding: 1rem;
    border-radius: 8px;
    background: var(--light);
    transition: var(--transition);
}

.activity-item:hover {
    background: rgba(52, 152, 219, 0.05);
}

.activity-icon {
    font-size: 1.2rem;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.5rem;
}

.activity-desc {
    color: var(--gray);
    font-size: 0.9rem;
    margin-bottom: 0.5rem;
}

.activity-meta {
    display: flex;
    gap: 1.5rem;
    font-size: 0.8rem;
    color: var(--gray);
}

.activity-meta span {
    display: flex;
    align-items: center;
    gap: 0.25rem;
}

.alert {
    padding: 1rem 1.5rem;
    border-radius: 8px;
    margin-bottom: 1.5rem;
    display: flex;
    align-items: center;
    gap: 0.75rem;
}

.alert-success {
    background: rgba(39, 174, 96, 0.1);
    border: 1px solid rgba(39, 174, 96, 0.2);
    color: var(--success);
}

.alert-danger {
    background: rgba(231, 76, 60, 0.1);
    border: 1px solid rgba(231, 76, 60, 0.2);
    color: var(--danger);
}

.user-badge {
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    padding: 0.5rem 1rem;
    background: var(--light);
    border-radius: 20px;
    font-weight: 500;
    color: var(--dark);
}

/* Responsive */
@media (max-width: 1024px) {
    .account-container {
        grid-template-columns: 1fr;
    }
    
    .account-sidebar {
        order: 2;
    }
    
    .account-content {
        order: 1;
    }
}

@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .security-status {
        grid-template-columns: 1fr;
    }
    
    .activity-meta {
        flex-direction: column;
        gap: 0.5rem;
    }
    
    .form-actions {
        flex-direction: column;
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}

/* Estilos mejorados para formularios - Mi Cuenta */
.account-form {
    max-width: 100%;
}

.form-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 1.5rem;
    margin-bottom: 2rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group label {
    font-weight: 600;
    color: var(--dark);
    margin-bottom: 0.5rem;
    font-size: 0.9rem;
    transition: var(--transition);
}

.form-group:focus-within label {
    color: var(--primary);
}

/* Inputs mejorados */
.form-control {
    padding: 0.875rem 1rem;
    border: 2px solid #e1e8ed;
    border-radius: 10px;
    font-size: 0.95rem;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    background: linear-gradient(135deg, #fafbfc 0%, #ffffff 100%);
    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.02);
    position: relative;
    color: var(--dark);
}

.form-control:hover {
    border-color: #c8d1d9;
    background: linear-gradient(135deg, #f5f7fa 0%, #ffffff 100%);
    box-shadow: 0 4px 8px rgba(0, 0, 0, 0.04);
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    background: white;
    box-shadow: 0 0 0 4px rgba(52, 152, 219, 0.1), 
                0 4px 12px rgba(0, 0, 0, 0.08);
    transform: translateY(-1px);
}

.form-control:disabled {
    background: #f8f9fa;
    border-color: #e9ecef;
    color: #6c757d;
    cursor: not-allowed;
    opacity: 0.7;
}

.form-control:disabled:hover {
    border-color: #e9ecef;
    box-shadow: none;
    transform: none;
}

/* Selects mejorados */
select.form-control {
    appearance: none;
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%236c757d' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px;
    padding-right: 3rem;
    cursor: pointer;
}

select.form-control:hover {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23343a40' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
}

select.form-control:focus {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%233498db' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'%3E%3C/polyline%3E%3C/svg%3E");
}

/* Input groups con iconos */
.input-group {
    position: relative;
    display: flex;
    align-items: center;
}

.input-group .form-control {
    padding-left: 3rem;
    width: 100%;
}

.input-group-icon {
    position: absolute;
    left: 1rem;
    color: #6c757d;
    z-index: 2;
    transition: var(--transition);
}

.form-control:focus + .input-group-icon {
    color: var(--primary);
}

/* Estados de validación */
.form-control.is-valid {
    border-color: var(--success);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2327ae60' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='20 6 9 17 4 12'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px;
    padding-right: 3rem;
}

.form-control.is-invalid {
    border-color: var(--danger);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23e74c3c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10'%3E%3C/circle%3E%3Cline x1='15' y1='9' x2='9' y2='15'%3E%3C/line%3E%3Cline x1='9' y1='9' x2='15' y2='15'%3E%3C/line%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px;
    padding-right: 3rem;
}

/* Texto de ayuda */
.form-text {
    margin-top: 0.5rem;
    font-size: 0.8rem;
    color: #6c757d;
    line-height: 1.4;
}

.form-control:focus ~ .form-text {
    color: var(--primary);
}

/* Password toggle */
.password-toggle {
    position: absolute;
    right: 1rem;
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: var(--transition);
}

.password-toggle:hover {
    color: var(--dark);
    background: rgba(0, 0, 0, 0.05);
}

.form-control:focus ~ .password-toggle {
    color: var(--primary);
}

/* Checkboxes y radios mejorados */
.form-check {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 1rem;
    cursor: pointer;
}

.form-check-input {
    appearance: none;
    width: 20px;
    height: 20px;
    border: 2px solid #e1e8ed;
    border-radius: 6px;
    background: white;
    position: relative;
    cursor: pointer;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.form-check-input:checked {
    background: var(--primary);
    border-color: var(--primary);
}

.form-check-input:checked::after {
    content: '';
    position: absolute;
    top: 2px;
    left: 6px;
    width: 5px;
    height: 10px;
    border: solid white;
    border-width: 0 2px 2px 0;
    transform: rotate(45deg);
}

.form-check-input:hover {
    border-color: var(--primary);
    box-shadow: 0 0 0 3px rgba(52, 152, 219, 0.1);
}

.form-check-label {
    font-weight: 500;
    color: var(--dark);
    cursor: pointer;
    user-select: none;
}

/* Radio buttons */
.form-radio .form-check-input {
    border-radius: 50%;
}

.form-radio .form-check-input:checked::after {
    content: '';
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: white;
    border: none;
}

/* Textarea mejorado */
textarea.form-control {
    min-height: 120px;
    resize: vertical;
    line-height: 1.5;
}

/* Grupos de formulario con íconos */
.form-group-with-icon {
    position: relative;
}

.form-group-with-icon .form-control {
    padding-left: 3rem;
}

.form-group-icon {
    position: absolute;
    left: 1rem;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    transition: var(--transition);
    pointer-events: none;
}

.form-control:focus ~ .form-group-icon {
    color: var(--primary);
}

/* Estados de carga */
.form-control.loading {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%233498db' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M21 12a9 9 0 11-6.219-8.56'%3E%3C/path%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    from { transform: rotate(0deg); }
    to { transform: rotate(360deg); }
}

/* Placeholders mejorados */
.form-control::placeholder {
    color: #adb5bd;
    transition: var(--transition);
}

.form-control:focus::placeholder {
    color: #6c757d;
    transform: translateX(5px);
}

/* Efectos de transición para grupos completos */
.form-group {
    transition: var(--transition);
}

.form-group:focus-within {
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
        gap: 1rem;
    }
    
    .form-control {
        padding: 0.75rem 0.875rem;
        font-size: 16px; /* Previene zoom en iOS */
    }
    
    select.form-control {
        background-position: right 0.875rem center;
        padding-right: 2.5rem;
    }
}

/* Dark mode support */
@media (prefers-color-scheme: dark) {
    .form-control {
        background: #2d3748;
        border-color: #4a5568;
        color: #e2e8f0;
    }
    
    .form-control:hover {
        background: #364152;
        border-color: #718096;
    }
    
    .form-control:focus {
        background: #2d3748;
        border-color: var(--primary);
    }
    
    .form-control::placeholder {
        color: #a0aec0;
    }
}

/* Mejoras específicas para el módulo Mi Cuenta */
.account-form .form-group {
    margin-bottom: 1.5rem;
}

.account-form .form-section {
    margin-bottom: 2.5rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border);
}

.account-form .form-section:last-child {
    border-bottom: none;
    margin-bottom: 0;
}

/* Estilos para campos de contraseña con toggle */
.password-field {
    position: relative;
}

.password-field .form-control {
    padding-right: 3rem;
}

.toggle-password {
    position: absolute;
    right: 1rem;
    top: 50%;
    transform: translateY(-50%);
    background: none;
    border: none;
    color: #6c757d;
    cursor: pointer;
    padding: 0.25rem;
    border-radius: 4px;
    transition: var(--transition);
}

.toggle-password:hover {
    color: var(--dark);
    background: rgba(0, 0, 0, 0.05);
}

.form-control:focus ~ .toggle-password {
    color: var(--primary);
}

/* Estados de éxito y error mejorados */
.form-control.success {
    border-color: var(--success);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%2327ae60' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M22 11.08V12a10 10 0 1 1-5.93-9.14'%3E%3C/path%3E%3Cpolyline points='22 4 12 14.01 9 11.01'%3E%3C/polyline%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px;
    padding-right: 3rem;
}

.form-control.error {
    border-color: var(--danger);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' viewBox='0 0 24 24' fill='none' stroke='%23e74c3c' stroke-width='2' stroke-linecap='round' stroke-linejoin='round'%3E%3Ccircle cx='12' cy='12' r='10'%3E%3C/circle%3E%3Cline x1='12' y1='8' x2='12' y2='12'%3E%3C/line%3E%3Cline x1='12' y1='16' x2='12.01' y2='16'%3E%3C/line%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right 1rem center;
    background-size: 16px;
    padding-right: 3rem;
}

/* Animación de entrada para nuevos campos */
@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.form-group {
    animation: slideInUp 0.3s ease-out;
}

.form-group:nth-child(odd) {
    animation-delay: 0.05s;
}

.form-group:nth-child(even) {
    animation-delay: 0.1s;
}
</style>

<script>
// Navegación entre pestañas
document.addEventListener('DOMContentLoaded', function() {
    const navItems = document.querySelectorAll('.nav-item');
    const tabContents = document.querySelectorAll('.tab-content');
    
    navItems.forEach(item => {
        item.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remover clase active de todos los items
            navItems.forEach(nav => nav.classList.remove('active'));
            tabContents.forEach(tab => tab.classList.remove('active'));
            
            // Agregar clase active al item clickeado
            this.classList.add('active');
            
            // Mostrar el contenido correspondiente
            const targetTab = this.getAttribute('data-tab');
            document.getElementById(targetTab).classList.add('active');
        });
    });
    
    // Validación de contraseñas
    const formCambioPassword = document.querySelector('form[action*="cambiar_password"]');
    if (formCambioPassword) {
        formCambioPassword.addEventListener('submit', function(e) {
            const nuevoPassword = document.getElementById('nuevo_password').value;
            const confirmarPassword = document.getElementById('confirmar_password').value;
            
            if (nuevoPassword !== confirmarPassword) {
                e.preventDefault();
                alert('Las contraseñas no coinciden. Por favor, verifica.');
                return false;
            }
            
            if (nuevoPassword.length < 6) {
                e.preventDefault();
                alert('La contraseña debe tener al menos 6 caracteres.');
                return false;
            }
        });
    }
});

function refreshActivity() {
    const btn = event.target;
    const originalHTML = btn.innerHTML;
    
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Actualizando...';
    btn.disabled = true;
    
    setTimeout(() => {
        location.reload();
    }, 1000);
}

// Mostrar/ocultar contraseñas (opcional)
function togglePasswordVisibility(inputId) {
    const input = document.getElementById(inputId);
    const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
    input.setAttribute('type', type);
}

// JavaScript mejorado para inputs y selects
document.addEventListener('DOMContentLoaded', function() {
    // Toggle de visibilidad de contraseña
    const toggleButtons = document.querySelectorAll('.toggle-password');
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const input = this.previousElementSibling;
            const type = input.getAttribute('type') === 'password' ? 'text' : 'password';
            input.setAttribute('type', type);
            
            // Cambiar ícono
            const icon = this.querySelector('i');
            if (type === 'password') {
                icon.className = 'fas fa-eye';
            } else {
                icon.className = 'fas fa-eye-slash';
            }
        });
    });

    // Validación en tiempo real
    const inputs = document.querySelectorAll('.form-control');
    inputs.forEach(input => {
        // Limpiar estados previos al enfocar
        input.addEventListener('focus', function() {
            this.classList.remove('error', 'success');
        });

        // Validación básica al salir del campo
        input.addEventListener('blur', function() {
            validateField(this);
        });

        // Validación en tiempo real para campos requeridos
        if (input.hasAttribute('required')) {
            input.addEventListener('input', function() {
                validateField(this);
            });
        }
    });

    // Validación de campos de contraseña
    const passwordFields = document.querySelectorAll('input[type="password"]');
    passwordFields.forEach(field => {
        field.addEventListener('input', function() {
            if (this.value.length > 0) {
                if (this.value.length < 6) {
                    this.classList.add('error');
                    this.classList.remove('success');
                } else {
                    this.classList.add('success');
                    this.classList.remove('error');
                }
            } else {
                this.classList.remove('error', 'success');
            }
        });
    });

    // Efecto de carga para formularios
    const forms = document.querySelectorAll('.account-form');
    forms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const submitButton = this.querySelector('button[type="submit"]');
            const originalText = submitButton.innerHTML;
            
            // Mostrar estado de carga
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
            submitButton.disabled = true;
            
            // Agregar clase de loading a todos los campos
            const inputs = this.querySelectorAll('.form-control');
            inputs.forEach(input => {
                input.classList.add('loading');
            });
            
            // Simular procesamiento (en producción, esto sería asíncrono)
            setTimeout(() => {
                submitButton.innerHTML = originalText;
                submitButton.disabled = false;
                inputs.forEach(input => {
                    input.classList.remove('loading');
                });
            }, 2000);
        });
    });

    // Función de validación de campo
    function validateField(field) {
        const value = field.value.trim();
        
        if (field.hasAttribute('required') && value === '') {
            field.classList.add('error');
            return false;
        }
        
        // Validación de email
        if (field.type === 'email' && value !== '') {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(value)) {
                field.classList.add('error');
                return false;
            }
        }
        
        // Validación de teléfono
        if (field.type === 'tel' && value !== '') {
            const phoneRegex = /^[\+]?[0-9\s\-\(\)]{8,}$/;
            if (!phoneRegex.test(value)) {
                field.classList.add('error');
                return false;
            }
        }
        
        // Si pasa todas las validaciones
        if (value !== '') {
            field.classList.add('success');
        }
        
        return true;
    }

    // Efecto de placeholder flotante mejorado
    const floatLabels = document.querySelectorAll('.form-group label');
    floatLabels.forEach(label => {
        const input = label.nextElementSibling;
        if (input && input.value !== '') {
            label.style.transform = 'translateY(-150%) scale(0.85)';
            label.style.color = 'var(--primary)';
        }
        
        input.addEventListener('focus', function() {
            label.style.transform = 'translateY(-150%) scale(0.85)';
            label.style.color = 'var(--primary)';
        });
        
        input.addEventListener('blur', function() {
            if (this.value === '') {
                label.style.transform = 'none';
                label.style.color = '';
            }
        });
    });
});

</script>


<?php
if (file_exists(__DIR__ . '/includes/footer.php')) {
    require_once __DIR__ . '/includes/footer.php';
}
?>