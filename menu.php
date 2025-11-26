<!-- menu.php -->
<?php
// Definir todos los enlaces en un solo lugar
$menu_links = [
    'dashboard' => 'index.php',
    'pruebas' => 'pruebas.php',
    'conductores' => 'conductores.php',
    'vehiculos' => 'vehiculos.php',
    'alcoholimetros' => 'alcoholimetros.php',
    'reportes' => 'reportes.php',
    'configuracion' => 'configuracion.php', // Cambiar aquí afecta todo
    'usuarios' => 'usuarios.php',
    'roles' => 'roles.php',
    'logout' => 'logout.php'
];
?>

<div class="sidebar-menu">
    <ul>
        <li><a href="<?php echo $menu_links['dashboard']; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $menu_links['dashboard'] ? 'active' : ''; ?>">
            <i class="fas fa-tachometer-alt"></i> Dashboard
        </a></li>
        <li><a href="<?php echo $menu_links['pruebas']; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $menu_links['pruebas'] ? 'active' : ''; ?>">
            <i class="fas fa-flask"></i> Pruebas
        </a></li>
        <li><a href="<?php echo $menu_links['conductores']; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $menu_links['conductores'] ? 'active' : ''; ?>">
            <i class="fas fa-users"></i> Conductores
        </a></li>
        <li><a href="<?php echo $menu_links['vehiculos']; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $menu_links['vehiculos'] ? 'active' : ''; ?>">
            <i class="fas fa-car"></i> Vehículos
        </a></li>
        <li><a href="<?php echo $menu_links['alcoholimetros']; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $menu_links['alcoholimetros'] ? 'active' : ''; ?>">
            <i class="fas fa-wind"></i> Alcoholímetros
        </a></li>
        <li><a href="<?php echo $menu_links['reportes']; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $menu_links['reportes'] ? 'active' : ''; ?>">
            <i class="fas fa-chart-bar"></i> Reportes
        </a></li>
        <?php if ($_SESSION['usuario_rol'] == 'admin' || esSuperAdmin()): ?>
        <li><a href="<?php echo $menu_links['configuracion']; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $menu_links['configuracion'] ? 'active' : ''; ?>">
            <i class="fas fa-cog"></i> Configuración
        </a></li>
        <li><a href="<?php echo $menu_links['usuarios']; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $menu_links['usuarios'] ? 'active' : ''; ?>">
            <i class="fas fa-user-shield"></i> Usuarios
        </a></li>
        <?php endif; ?>
        <?php if (esSuperAdmin()): ?>
        <li><a href="<?php echo $menu_links['roles']; ?>" class="<?php echo basename($_SERVER['PHP_SELF']) == $menu_links['roles'] ? 'active' : ''; ?>">
            <i class="fas fa-lock"></i> Roles y Permisos
        </a></li>
        <?php endif; ?>
        <li><a href="<?php echo $menu_links['logout']; ?>">
            <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
        </a></li>
    </ul>
</div>