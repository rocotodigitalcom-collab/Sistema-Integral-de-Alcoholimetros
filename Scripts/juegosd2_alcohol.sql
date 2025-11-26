-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 26-11-2025 a las 04:01:12
-- Versión del servidor: 10.11.13-MariaDB-cll-lve
-- Versión de PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `juegosd2_alcohol`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `alcoholimetros`
--

CREATE TABLE `alcoholimetros` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `numero_serie` varchar(50) NOT NULL,
  `nombre_activo` varchar(100) NOT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `fecha_calibracion` date DEFAULT NULL,
  `proxima_calibracion` date DEFAULT NULL,
  `estado` enum('activo','inactivo','mantenimiento','calibracion') DEFAULT 'activo',
  `qr_code` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `alcoholimetros`
--

INSERT INTO `alcoholimetros` (`id`, `cliente_id`, `numero_serie`, `nombre_activo`, `modelo`, `marca`, `fecha_calibracion`, `proxima_calibracion`, `estado`, `qr_code`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 'ALC-001', 'Alcoholímetro Principal', 'AL-3000', 'AlcoTest', '2024-01-15', '2025-01-15', 'activo', NULL, '2025-11-25 12:10:30', '2025-11-25 12:10:30'),
(2, 1, 'ALC-002', 'Alcoholímetro Secundario', 'AL-2500', 'AlcoTest', '2024-02-20', '2025-02-20', 'activo', NULL, '2025-11-25 12:10:30', '2025-11-25 12:10:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `valores_anteriores` text DEFAULT NULL,
  `valores_nuevos` text DEFAULT NULL,
  `detalles` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha_accion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `auditoria`
--

INSERT INTO `auditoria` (`id`, `cliente_id`, `usuario_id`, `accion`, `tabla_afectada`, `registro_id`, `valores_anteriores`, `valores_nuevos`, `detalles`, `ip_address`, `user_agent`, `fecha_accion`) VALUES
(1, 1, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Inicio de sesión exitoso', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 12:15:31'),
(2, 1, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Inicio de sesión exitoso', '132.191.2.150', 'Mozilla/5.0 (Android 13; Mobile; rv:145.0) Gecko/145.0 Firefox/145.0', '2025-11-25 13:10:14'),
(3, 1, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Inicio de sesión exitoso', '132.191.2.150', 'Mozilla/5.0 (X11; Linux x86_64; rv:145.0) Gecko/20100101 Firefox/145.0', '2025-11-25 13:11:47'),
(4, 1, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Inicio de sesión exitoso', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 22:20:28'),
(5, 1, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Inicio de sesión exitoso', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 22:34:31'),
(6, 1, 2, 'CONFIG_ALCOHOL', 'configuraciones', 1, NULL, NULL, 'Actualización de niveles de alcohol', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 22:50:44'),
(7, 1, 2, 'CONFIG_RETEST', 'configuraciones', 1, NULL, NULL, 'Actualización de protocolo re-test', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 22:50:51'),
(8, 1, 2, 'CONFIG_RETEST', 'configuraciones', 1, NULL, NULL, 'Actualización de protocolo re-test', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-25 22:50:59'),
(9, 1, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Inicio de sesión exitoso', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 02:09:22'),
(10, 1, 2, 'CAMBIO_PLAN', 'clientes', 1, NULL, NULL, 'Cambio de plan a ID: 2', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:35:21'),
(11, 1, 2, 'CAMBIO_PLAN', 'clientes', 1, NULL, NULL, 'Cambio de plan a ID: 3', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:35:26'),
(12, 1, 2, 'CAMBIO_PLAN', 'clientes', 1, NULL, NULL, 'Cambio de plan a ID: 4', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:35:31'),
(13, 1, 2, 'CAMBIO_PLAN', 'clientes', 1, NULL, NULL, 'Cambio de plan a ID: 1', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:35:53'),
(14, 1, 2, 'CONFIG_GENERAL', 'clientes', 1, NULL, NULL, 'Actualización de datos generales', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:47:16'),
(15, 1, 2, 'CONFIG_GENERAL', 'clientes', 1, NULL, NULL, 'Actualización de datos generales', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:47:30'),
(16, 1, 2, 'CONFIG_GENERAL', 'clientes', 1, NULL, NULL, 'Actualización de datos generales', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:47:36'),
(17, 1, 2, 'CONFIG_RETEST', 'configuraciones', 1, NULL, NULL, 'Actualización de protocolo re-test', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:47:50'),
(18, 1, 2, 'CONFIG_RETEST', 'configuraciones', 1, NULL, NULL, 'Actualización de protocolo re-test', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:48:21'),
(19, 1, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Inicio de sesión exitoso', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:49:04'),
(20, 1, 2, 'BACKUP', NULL, NULL, NULL, NULL, 'Backup manual realizado', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:57:04'),
(21, 1, 2, 'BACKUP', NULL, NULL, NULL, NULL, 'Backup manual realizado', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:57:34');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `archivo` varchar(255) NOT NULL,
  `tamanio` bigint(20) DEFAULT NULL,
  `tipo` enum('manual','automatico') DEFAULT 'manual',
  `estado` enum('completado','error','en_proceso') DEFAULT 'completado',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `observaciones` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `backups`
--

INSERT INTO `backups` (`id`, `cliente_id`, `archivo`, `tamanio`, `tipo`, `estado`, `fecha_creacion`, `observaciones`) VALUES
(1, 1, 'backup_1_2024-11-25_03-00-00.sql', 2621440, 'automatico', 'completado', '2025-11-26 03:25:45', 'Backup diario automático'),
(2, 1, 'backup_1_2024-11-24_03-00-00.sql', 2521340, 'automatico', 'completado', '2025-11-26 03:25:45', 'Backup diario automático'),
(3, 1, 'backup_1_2024-11-23_15-30-00.sql', 2421240, 'manual', 'completado', '2025-11-26 03:25:45', 'Backup manual solicitado por usuario');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `clientes`
--

CREATE TABLE `clientes` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(255) NOT NULL,
  `ruc` varchar(20) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email_contacto` varchar(255) DEFAULT NULL,
  `plan_id` int(11) NOT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `color_primario` varchar(7) DEFAULT '#2196F3',
  `color_secundario` varchar(7) DEFAULT '#1976D2',
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('activo','inactivo','suspendido','prueba') DEFAULT 'prueba',
  `token_api` varchar(100) DEFAULT NULL,
  `modo_demo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `clientes`
--

INSERT INTO `clientes` (`id`, `nombre_empresa`, `ruc`, `direccion`, `telefono`, `email_contacto`, `plan_id`, `logo`, `color_primario`, `color_secundario`, `fecha_registro`, `fecha_vencimiento`, `estado`, `token_api`, `modo_demo`) VALUES
(1, 'Empresa Demo S.A.', '20123456789', 'Av. Demo 123, Lima', '01-234-5678', 'admin@demo.com', 1, NULL, '#84061f', '#427420', '2025-11-25 12:10:30', '2025-12-25', 'prueba', '8bd23693c01825696ee136aee8eae333', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `configuraciones`
--

CREATE TABLE `configuraciones` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `limite_alcohol_permisible` decimal(5,3) DEFAULT 0.000,
  `intervalo_retest_minutos` int(11) DEFAULT 15,
  `intentos_retest` int(11) DEFAULT 3,
  `requerir_geolocalizacion` tinyint(1) DEFAULT 1,
  `requerir_foto_evidencia` tinyint(1) DEFAULT 0,
  `requerir_firma_digital` tinyint(1) DEFAULT 1,
  `notificaciones_email` tinyint(1) DEFAULT 1,
  `notificaciones_sms` tinyint(1) DEFAULT 0,
  `notificaciones_push` tinyint(1) DEFAULT 1,
  `timezone` varchar(50) DEFAULT 'America/Lima',
  `idioma` enum('es','en','pt') DEFAULT 'es',
  `formato_fecha` varchar(20) DEFAULT 'd/m/Y',
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `nivel_advertencia` decimal(5,3) DEFAULT 0.025,
  `nivel_critico` decimal(5,3) DEFAULT 0.080,
  `unidad_medida` varchar(10) DEFAULT 'g/L',
  `bloqueo_conductor_horas` int(11) DEFAULT 24,
  `notificar_supervisor_retest` tinyint(1) DEFAULT 1,
  `requerir_aprobacion_supervisor` tinyint(1) DEFAULT 0,
  `requerir_observaciones` tinyint(1) DEFAULT 0,
  `tiempo_maximo_prueba_minutos` int(11) DEFAULT 10,
  `distancia_maxima_metros` int(11) DEFAULT 500,
  `notificaciones_whatsapp` tinyint(1) DEFAULT 0,
  `email_notificacion` varchar(255) DEFAULT NULL,
  `telefono_notificacion` varchar(20) DEFAULT NULL,
  `emails_adicionales` text DEFAULT NULL,
  `backup_diario` tinyint(1) DEFAULT 1,
  `backup_semanal` tinyint(1) DEFAULT 1,
  `backup_mensual` tinyint(1) DEFAULT 0,
  `retencion_dias` int(11) DEFAULT 30,
  `observaciones` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuraciones`
--

INSERT INTO `configuraciones` (`id`, `cliente_id`, `limite_alcohol_permisible`, `intervalo_retest_minutos`, `intentos_retest`, `requerir_geolocalizacion`, `requerir_foto_evidencia`, `requerir_firma_digital`, `notificaciones_email`, `notificaciones_sms`, `notificaciones_push`, `timezone`, `idioma`, `formato_fecha`, `fecha_actualizacion`, `nivel_advertencia`, `nivel_critico`, `unidad_medida`, `bloqueo_conductor_horas`, `notificar_supervisor_retest`, `requerir_aprobacion_supervisor`, `requerir_observaciones`, `tiempo_maximo_prueba_minutos`, `distancia_maxima_metros`, `notificaciones_whatsapp`, `email_notificacion`, `telefono_notificacion`, `emails_adicionales`, `backup_diario`, `backup_semanal`, `backup_mensual`, `retencion_dias`, `observaciones`) VALUES
(1, 1, 0.000, 15, 5, 1, 0, 1, 1, 0, 1, 'America/Lima', 'es', 'd/m/Y', '2025-11-26 03:47:50', 0.025, 0.080, 'g/L', 24, 1, 0, 0, 10, 500, 0, NULL, NULL, NULL, 1, 1, 0, 30, '');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `config_notificaciones_eventos`
--

CREATE TABLE `config_notificaciones_eventos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `evento` varchar(50) NOT NULL,
  `notificar_email` tinyint(1) DEFAULT 1,
  `notificar_sms` tinyint(1) DEFAULT 0,
  `notificar_push` tinyint(1) DEFAULT 1,
  `notificar_whatsapp` tinyint(1) DEFAULT 0,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `config_notificaciones_eventos`
--

INSERT INTO `config_notificaciones_eventos` (`id`, `cliente_id`, `evento`, `notificar_email`, `notificar_sms`, `notificar_push`, `notificar_whatsapp`, `activo`) VALUES
(1, 1, 'prueba_positiva', 1, 0, 1, 0, 1),
(2, 1, 'retest_fallido', 1, 0, 1, 0, 1),
(3, 1, 'conductor_bloqueado', 1, 0, 1, 0, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_configuracion`
--

CREATE TABLE `logs_configuracion` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `seccion` varchar(50) DEFAULT NULL,
  `cambios` text DEFAULT NULL,
  `fecha` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `logs_notificaciones`
--

CREATE TABLE `logs_notificaciones` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `tipo_notificacion` enum('email','sms','push','whatsapp') NOT NULL,
  `destinatario` varchar(255) DEFAULT NULL,
  `asunto` varchar(255) DEFAULT NULL,
  `mensaje` text DEFAULT NULL,
  `estado` enum('enviado','error','pendiente') DEFAULT 'pendiente',
  `error_mensaje` text DEFAULT NULL,
  `fecha_envio` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `permisos`
--

CREATE TABLE `permisos` (
  `id` int(11) NOT NULL,
  `modulo` varchar(50) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `codigo` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `permisos`
--

INSERT INTO `permisos` (`id`, `modulo`, `nombre`, `codigo`, `descripcion`, `estado`) VALUES
(1, 'pruebas', 'Ver Pruebas', 'pruebas.ver', 'Ver listado de pruebas', 1),
(2, 'pruebas', 'Crear Pruebas', 'pruebas.crear', 'Realizar nuevas pruebas', 1),
(3, 'pruebas', 'Editar Pruebas', 'pruebas.editar', 'Modificar pruebas existentes', 1),
(4, 'pruebas', 'Eliminar Pruebas', 'pruebas.eliminar', 'Eliminar pruebas', 1),
(5, 'pruebas', 'Aprobar Re-test', 'pruebas.aprobar_retest', 'Aprobar realización de re-test', 1),
(6, 'configuracion', 'Ver Configuración', 'config.ver', 'Ver configuración del sistema', 1),
(7, 'configuracion', 'Editar Configuración', 'config.editar', 'Modificar configuración', 1),
(8, 'configuracion', 'Gestionar Roles', 'config.roles', 'Administrar roles y permisos', 1),
(9, 'configuracion', 'Realizar Backups', 'config.backup', 'Realizar backups del sistema', 1),
(10, 'usuarios', 'Ver Usuarios', 'usuarios.ver', 'Ver listado de usuarios', 1),
(11, 'usuarios', 'Crear Usuarios', 'usuarios.crear', 'Crear nuevos usuarios', 1),
(12, 'usuarios', 'Editar Usuarios', 'usuarios.editar', 'Modificar usuarios', 1),
(13, 'usuarios', 'Eliminar Usuarios', 'usuarios.eliminar', 'Eliminar usuarios', 1),
(14, 'reportes', 'Ver Reportes', 'reportes.ver', 'Ver reportes', 1),
(15, 'reportes', 'Exportar Reportes', 'reportes.exportar', 'Exportar reportes', 1),
(16, 'reportes', 'Reportes Gerenciales', 'reportes.gerenciales', 'Acceso a reportes gerenciales', 1),
(17, 'vehiculos', 'Ver Vehículos', 'vehiculos.ver', 'Ver listado de vehículos', 1),
(18, 'vehiculos', 'Gestionar Vehículos', 'vehiculos.gestionar', 'Crear, editar y eliminar vehículos', 1),
(19, 'alcoholimetros', 'Ver Alcoholímetros', 'alcoholimetros.ver', 'Ver listado de alcoholímetros', 1),
(20, 'alcoholimetros', 'Gestionar Alcoholímetros', 'alcoholimetros.gestionar', 'Administrar alcoholímetros', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `planes`
--

CREATE TABLE `planes` (
  `id` int(11) NOT NULL,
  `nombre_plan` varchar(100) NOT NULL,
  `precio_mensual` decimal(10,2) NOT NULL,
  `limite_pruebas_mes` int(11) DEFAULT 1000,
  `limite_usuarios` int(11) DEFAULT 5,
  `limite_alcoholimetros` int(11) DEFAULT 10,
  `reportes_avanzados` tinyint(1) DEFAULT 0,
  `soporte_prioritario` tinyint(1) DEFAULT 0,
  `acceso_api` tinyint(1) DEFAULT 0,
  `almacenamiento_fotos` int(11) DEFAULT 100,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `integraciones` tinyint(1) DEFAULT 0,
  `multi_sede` tinyint(1) DEFAULT 0,
  `personalizacion` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id`, `nombre_plan`, `precio_mensual`, `limite_pruebas_mes`, `limite_usuarios`, `limite_alcoholimetros`, `reportes_avanzados`, `soporte_prioritario`, `acceso_api`, `almacenamiento_fotos`, `estado`, `fecha_creacion`, `integraciones`, `multi_sede`, `personalizacion`) VALUES
(1, 'Free', 0.00, 30, 1, 1, 0, 0, 0, 100, 1, '2025-11-25 12:10:30', 0, 0, 0),
(2, 'Starter', 49.00, 500, 5, 3, 0, 0, 0, 100, 1, '2025-11-25 12:10:30', 0, 0, 0),
(3, 'Professional', 149.00, 2000, 20, 10, 1, 1, 1, 100, 1, '2025-11-25 12:10:30', 1, 0, 1),
(4, 'Enterprise', 499.00, 99999, 99999, 99999, 1, 1, 1, 100, 1, '2025-11-25 12:10:30', 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pruebas`
--

CREATE TABLE `pruebas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `alcoholimetro_id` int(11) NOT NULL,
  `conductor_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `vehiculo_id` int(11) DEFAULT NULL,
  `nivel_alcohol` decimal(5,3) NOT NULL,
  `limite_permisible` decimal(5,3) DEFAULT 0.000,
  `resultado` enum('aprobado','reprobado') NOT NULL,
  `es_retest` tinyint(1) DEFAULT 0,
  `prueba_padre_id` int(11) DEFAULT NULL,
  `intento_numero` int(11) DEFAULT 1,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `direccion_geocodificada` text DEFAULT NULL,
  `foto_evidencia` varchar(255) DEFAULT NULL,
  `firma_conductor` varchar(255) DEFAULT NULL,
  `firma_supervisor` varchar(255) DEFAULT NULL,
  `observaciones` text DEFAULT NULL,
  `fecha_prueba` timestamp NULL DEFAULT current_timestamp(),
  `sync_movil` tinyint(1) DEFAULT 0,
  `dispositivo_movil` varchar(100) DEFAULT NULL,
  `hash_verificacion` varchar(100) DEFAULT NULL,
  `temperatura_ambiente` decimal(4,2) DEFAULT NULL,
  `humedad_ambiente` decimal(4,2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `nombre` varchar(50) NOT NULL,
  `descripcion` text DEFAULT NULL,
  `nivel` int(11) DEFAULT 1,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `roles`
--

INSERT INTO `roles` (`id`, `nombre`, `descripcion`, `nivel`, `estado`, `fecha_creacion`) VALUES
(1, 'Super Admin', 'Acceso total al sistema', 10, 1, '2025-11-25 12:47:37'),
(2, 'Admin Cliente', 'Administrador de la empresa', 8, 1, '2025-11-25 12:47:37'),
(3, 'Supervisor', 'Supervisor de operaciones', 6, 1, '2025-11-25 12:47:37'),
(4, 'Operador', 'Operador de pruebas', 4, 1, '2025-11-25 12:47:37'),
(5, 'Conductor', 'Conductor - solo consulta', 2, 1, '2025-11-25 12:47:37'),
(6, 'Auditor', 'Solo lectura y reportes', 3, 1, '2025-11-25 12:47:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol_permisos`
--

CREATE TABLE `rol_permisos` (
  `id` int(11) NOT NULL,
  `rol_id` int(11) NOT NULL,
  `permiso_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `rol_permisos`
--

INSERT INTO `rol_permisos` (`id`, `rol_id`, `permiso_id`) VALUES
(11, 1, 1),
(8, 1, 2),
(9, 1, 3),
(10, 1, 4),
(7, 1, 5),
(6, 1, 6),
(4, 1, 7),
(5, 1, 8),
(3, 1, 9),
(18, 1, 10),
(15, 1, 11),
(16, 1, 12),
(17, 1, 13),
(14, 1, 14),
(12, 1, 15),
(13, 1, 16),
(20, 1, 17),
(19, 1, 18),
(2, 1, 19),
(1, 1, 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `token_sesion` varchar(255) NOT NULL,
  `dispositivo` varchar(100) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `fecha_inicio` timestamp NULL DEFAULT current_timestamp(),
  `fecha_expiracion` timestamp NULL DEFAULT NULL,
  `activa` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `temas_personalizados`
--

CREATE TABLE `temas_personalizados` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre_tema` varchar(50) DEFAULT 'default',
  `color_primario` varchar(7) DEFAULT '#2c3e50',
  `color_secundario` varchar(7) DEFAULT '#3498db',
  `color_exito` varchar(7) DEFAULT '#27ae60',
  `color_error` varchar(7) DEFAULT '#e74c3c',
  `color_advertencia` varchar(7) DEFAULT '#f39c12',
  `fuente_principal` varchar(100) DEFAULT 'Roboto',
  `tamanio_fuente` int(11) DEFAULT 14,
  `border_radius` int(11) DEFAULT 4,
  `activo` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `nombre` varchar(100) NOT NULL,
  `apellido` varchar(100) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `dni` varchar(15) DEFAULT NULL,
  `rol` enum('super_admin','admin','supervisor','operador','conductor','auditor') NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `token_recuperacion` varchar(100) DEFAULT NULL,
  `fecha_expiracion_token` datetime DEFAULT NULL,
  `intentos_login` int(11) DEFAULT 0,
  `bloqueado_hasta` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `cliente_id`, `nombre`, `apellido`, `email`, `password`, `telefono`, `dni`, `rol`, `foto_perfil`, `estado`, `ultimo_login`, `token_recuperacion`, `fecha_expiracion_token`, `intentos_login`, `bloqueado_hasta`, `fecha_creacion`) VALUES
(1, NULL, 'Super', 'Administrador', 'superadmin@sistema.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, NULL, 'super_admin', NULL, 1, NULL, NULL, NULL, 0, NULL, '2025-11-25 12:10:30'),
(2, 1, 'Admin', 'Demo', 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '12345678', 'admin', NULL, 1, '2025-11-26 03:49:04', NULL, NULL, 0, NULL, '2025-11-25 12:10:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `vehiculos`
--

CREATE TABLE `vehiculos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `anio` int(11) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `kilometraje` int(11) DEFAULT NULL,
  `estado` enum('activo','inactivo','mantenimiento') DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `vehiculos`
--

INSERT INTO `vehiculos` (`id`, `cliente_id`, `placa`, `marca`, `modelo`, `anio`, `color`, `kilometraje`, `estado`, `fecha_creacion`) VALUES
(1, 1, 'ABC-123', 'Toyota', 'Hilux', 2023, 'Blanco', 15000, 'activo', '2025-11-25 12:10:30'),
(2, 1, 'DEF-456', 'Nissan', 'Frontier', 2022, 'Negro', 25000, 'activo', '2025-11-25 12:10:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `webhooks`
--

CREATE TABLE `webhooks` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `url` varchar(500) NOT NULL,
  `evento` varchar(50) NOT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `secret_key` varchar(100) DEFAULT NULL,
  `reintentos` int(11) DEFAULT 3,
  `ultimo_intento` datetime DEFAULT NULL,
  `ultimo_estado` varchar(50) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `alcoholimetros`
--
ALTER TABLE `alcoholimetros`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `backups`
--
ALTER TABLE `backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_backups_cliente` (`cliente_id`,`fecha_creacion`);

--
-- Indices de la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ruc` (`ruc`),
  ADD UNIQUE KEY `token_api` (`token_api`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indices de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cliente` (`cliente_id`),
  ADD KEY `idx_configuraciones_cliente` (`cliente_id`);

--
-- Indices de la tabla `config_notificaciones_eventos`
--
ALTER TABLE `config_notificaciones_eventos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cliente_evento` (`cliente_id`,`evento`);

--
-- Indices de la tabla `logs_configuracion`
--
ALTER TABLE `logs_configuracion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `logs_notificaciones`
--
ALTER TABLE `logs_notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `idx_logs_notif_fecha` (`fecha_envio`);

--
-- Indices de la tabla `permisos`
--
ALTER TABLE `permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `planes`
--
ALTER TABLE `planes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `pruebas`
--
ALTER TABLE `pruebas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `alcoholimetro_id` (`alcoholimetro_id`),
  ADD KEY `conductor_id` (`conductor_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `vehiculo_id` (`vehiculo_id`),
  ADD KEY `prueba_padre_id` (`prueba_padre_id`);

--
-- Indices de la tabla `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_rol_permiso` (`rol_id`,`permiso_id`),
  ADD KEY `permiso_id` (`permiso_id`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_sesion` (`token_sesion`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `temas_personalizados`
--
ALTER TABLE `temas_personalizados`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cliente_tema` (`cliente_id`,`nombre_tema`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_email` (`email`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `webhooks`
--
ALTER TABLE `webhooks`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_webhooks_cliente_evento` (`cliente_id`,`evento`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `alcoholimetros`
--
ALTER TABLE `alcoholimetros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=22;

--
-- AUTO_INCREMENT de la tabla `backups`
--
ALTER TABLE `backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `clientes`
--
ALTER TABLE `clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `config_notificaciones_eventos`
--
ALTER TABLE `config_notificaciones_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `logs_configuracion`
--
ALTER TABLE `logs_configuracion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `logs_notificaciones`
--
ALTER TABLE `logs_notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `permisos`
--
ALTER TABLE `permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `planes`
--
ALTER TABLE `planes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pruebas`
--
ALTER TABLE `pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `temas_personalizados`
--
ALTER TABLE `temas_personalizados`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `webhooks`
--
ALTER TABLE `webhooks`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `alcoholimetros`
--
ALTER TABLE `alcoholimetros`
  ADD CONSTRAINT `alcoholimetros_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `backups`
--
ALTER TABLE `backups`
  ADD CONSTRAINT `backups_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `clientes`
--
ALTER TABLE `clientes`
  ADD CONSTRAINT `clientes_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `planes` (`id`);

--
-- Filtros para la tabla `configuraciones`
--
ALTER TABLE `configuraciones`
  ADD CONSTRAINT `configuraciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `config_notificaciones_eventos`
--
ALTER TABLE `config_notificaciones_eventos`
  ADD CONSTRAINT `config_notificaciones_eventos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `logs_configuracion`
--
ALTER TABLE `logs_configuracion`
  ADD CONSTRAINT `logs_configuracion_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `logs_configuracion_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `logs_notificaciones`
--
ALTER TABLE `logs_notificaciones`
  ADD CONSTRAINT `logs_notificaciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `pruebas`
--
ALTER TABLE `pruebas`
  ADD CONSTRAINT `pruebas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `pruebas_ibfk_2` FOREIGN KEY (`alcoholimetro_id`) REFERENCES `alcoholimetros` (`id`),
  ADD CONSTRAINT `pruebas_ibfk_3` FOREIGN KEY (`conductor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pruebas_ibfk_4` FOREIGN KEY (`supervisor_id`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `pruebas_ibfk_5` FOREIGN KEY (`vehiculo_id`) REFERENCES `vehiculos` (`id`),
  ADD CONSTRAINT `pruebas_ibfk_6` FOREIGN KEY (`prueba_padre_id`) REFERENCES `pruebas` (`id`);

--
-- Filtros para la tabla `rol_permisos`
--
ALTER TABLE `rol_permisos`
  ADD CONSTRAINT `rol_permisos_ibfk_1` FOREIGN KEY (`rol_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `rol_permisos_ibfk_2` FOREIGN KEY (`permiso_id`) REFERENCES `permisos` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `sesiones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `temas_personalizados`
--
ALTER TABLE `temas_personalizados`
  ADD CONSTRAINT `temas_personalizados_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD CONSTRAINT `usuarios_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  ADD CONSTRAINT `vehiculos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `webhooks`
--
ALTER TABLE `webhooks`
  ADD CONSTRAINT `webhooks_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
