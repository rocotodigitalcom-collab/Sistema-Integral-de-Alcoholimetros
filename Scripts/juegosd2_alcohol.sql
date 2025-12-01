-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 01-12-2025 a las 00:26:43
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

DELIMITER $$
--
-- Procedimientos
--
CREATE DEFINER=`wepanel_juegosd2`@`localhost` PROCEDURE `SolicitarRetest` (IN `p_prueba_original_id` INT, IN `p_solicitado_por` INT, IN `p_motivo` TEXT)   BEGIN
    DECLARE v_intentos_permisibles INT$$

CREATE DEFINER=`wepanel_juegosd2`@`localhost` PROCEDURE `VerificarLimitesPlan` (IN `p_cliente_id` INT, IN `p_tipo_limite` VARCHAR(50))   BEGIN
    DECLARE v_plan_id INT$$

DELIMITER ;

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
(1, 1, 'ALC-001', 'Alcoholímetro Principal', 'AL-3000', 'AlcoTest', '2024-01-15', '2025-01-15', 'activo', 'qr_ALC-001_1764393745.png', '2025-11-25 12:10:30', '2025-11-29 05:22:25'),
(2, 1, 'ALC-002', 'Alcoholímetro Secundario', 'AL-2500', 'AlcoTest', '2024-02-20', '2025-02-20', 'activo', NULL, '2025-11-25 12:10:30', '2025-11-25 12:10:30'),
(3, 1, 'ALC-003', 'Alcoholímetro manual', 'AL-3001', 'AlcoTest', '2025-11-01', '2026-11-30', 'activo', NULL, '2025-11-29 04:47:21', '2025-11-29 04:47:21');

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
(21, 1, 2, 'BACKUP', NULL, NULL, NULL, NULL, 'Backup manual realizado', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 03:57:34'),
(22, 1, 1, 'LOGOUT', 'usuarios', 1, NULL, NULL, 'Cierre de sesión', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 05:36:07'),
(23, 1, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Cierre de sesión', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 05:36:16'),
(24, 1, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Cierre de sesión', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 05:41:00'),
(25, 1, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Cierre de sesión', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 05:59:03'),
(26, 1, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Cierre de sesión', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 06:34:58'),
(27, 1, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Cierre de sesión', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 06:42:13'),
(28, 1, 2, 'LOGOUT', 'usuarios', 2, NULL, NULL, 'Cierre de sesión', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 06:45:10'),
(29, 1, 2, 'LOGIN', 'usuarios', 2, NULL, NULL, 'Inicio de sesión exitoso', '179.6.2.56', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-26 20:05:40'),
(30, 1, 2, 'CONFIG_EMPRESA', 'clientes', 1, NULL, NULL, 'Actualización de información de la empresa', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 03:28:35'),
(31, 1, 2, 'CONFIG_EMPRESA', 'clientes', 1, NULL, NULL, 'Actualización de información de la empresa', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 03:28:42'),
(32, 1, 2, 'CONFIG_EMPRESA', 'clientes', 1, NULL, NULL, 'Actualización de información de la empresa', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-28 03:28:49'),
(33, 1, 2, 'ACTUALIZAR_ALCOHOLIMETRO', 'alcoholimetros', 1, NULL, NULL, 'Alcoholímetro actualizado', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 04:35:07'),
(34, 1, 2, 'ACTUALIZAR_ALCOHOLIMETRO', 'alcoholimetros', 1, NULL, NULL, 'Alcoholímetro actualizado', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 04:35:12'),
(35, 1, 2, 'ACTUALIZAR_ALCOHOLIMETRO', 'alcoholimetros', 1, NULL, NULL, 'Alcoholímetro actualizado', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 04:35:17'),
(36, 1, 2, 'ACTUALIZAR_ALCOHOLIMETRO', 'alcoholimetros', 1, NULL, NULL, 'Alcoholímetro actualizado', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 04:35:29'),
(37, 1, 2, 'ACTUALIZAR_ALCOHOLIMETRO', 'alcoholimetros', 1, NULL, NULL, 'Alcoholímetro actualizado', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 04:46:19'),
(38, 1, 2, 'ACTUALIZAR_ALCOHOLIMETRO', 'alcoholimetros', 1, NULL, NULL, 'Alcoholímetro actualizado', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 04:46:34'),
(39, 1, 2, 'ACTUALIZAR_ALCOHOLIMETRO', 'alcoholimetros', 1, NULL, NULL, 'Alcoholímetro actualizado', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 04:46:46'),
(40, 1, 2, 'CREAR_ALCOHOLIMETRO', 'alcoholimetros', 3, NULL, NULL, 'Alcoholímetro creado', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 04:47:21'),
(41, 1, 2, 'GENERAR_QR_INDIVIDUAL', 'alcoholimetros', 1, NULL, NULL, 'Código QR generado: qr_ALC-001_1764393745.png', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 05:22:25'),
(42, 1, 2, 'DESCARGAR_QR', 'alcoholimetros', 1, NULL, NULL, 'Descarga de código QR: qr_ALC-001_1764393745.png', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 05:22:31'),
(43, 1, 2, 'DESCARGAR_QR', 'alcoholimetros', 1, NULL, NULL, 'Descarga de código QR: qr_ALC-001_1764393745.png', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 05:22:56'),
(44, 1, 2, 'DESCARGAR_QR', 'alcoholimetros', 1, NULL, NULL, 'Descarga de código QR: qr_ALC-001_1764393745.png', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 05:30:34'),
(45, 1, 2, 'CREAR_VEHICULO', 'vehiculos', 3, NULL, NULL, 'Vehículo DEF-456777 - Nissan Frontier - Estado: activo', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 06:49:50'),
(46, 1, 2, 'CREAR_CONDUCTOR', 'usuarios', 0, NULL, NULL, 'Conductor Pedro Ramirez - DNI: 57845478 - Estado: Activo', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 06:57:56'),
(47, 1, 2, 'CREAR_CONDUCTOR', 'usuarios', 0, NULL, NULL, 'Conductor Pedro Ramirez - DNI: 57845478 - Estado: Activo', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 06:58:04'),
(48, 1, 2, 'CREAR_CONDUCTOR', 'usuarios', 6, NULL, NULL, 'Conductor Pedro Ramirez - DNI: 40766447 - Estado: Activo', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-29 07:08:47'),
(49, 1, 2, 'CREAR_PRUEBA', 'pruebas', 1, NULL, NULL, 'Prueba creada - Nivel: 0.1 g/L', '179.6.3.42', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/142.0.0.0 Safari/537.36', '2025-11-30 04:05:37');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `backups`
--

CREATE TABLE `backups` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `archivo` varchar(255) NOT NULL,
  `ruta_archivo` varchar(500) DEFAULT NULL,
  `tamanio` bigint(20) DEFAULT NULL,
  `hash_verificacion` varchar(100) DEFAULT NULL,
  `incluye_archivos` tinyint(1) DEFAULT 0,
  `tipo` enum('manual','automatico') DEFAULT 'manual',
  `estado` enum('completado','error','en_proceso') DEFAULT 'completado',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `observaciones` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `backups`
--

INSERT INTO `backups` (`id`, `cliente_id`, `archivo`, `ruta_archivo`, `tamanio`, `hash_verificacion`, `incluye_archivos`, `tipo`, `estado`, `fecha_creacion`, `observaciones`) VALUES
(1, 1, 'backup_1_2024-11-25_03-00-00.sql', NULL, 2621440, NULL, 0, 'automatico', 'completado', '2025-11-26 03:25:45', 'Backup diario automático'),
(2, 1, 'backup_1_2024-11-24_03-00-00.sql', NULL, 2521340, NULL, 0, 'automatico', 'completado', '2025-11-26 03:25:45', 'Backup diario automático'),
(3, 1, 'backup_1_2024-11-23_15-30-00.sql', NULL, 2421240, NULL, 0, 'manual', 'completado', '2025-11-26 03:25:45', 'Backup manual solicitado por usuario');

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
(1, 'Empresa Demo S.A.C.', '20123456789', 'Av. Demo 123, Lima', '01-234-5678', 'admin@demo.com', 1, NULL, '#84061f', '#427420', '2025-11-25 12:10:30', '2025-12-25', 'prueba', '8bd23693c01825696ee136aee8eae333', 1);

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
-- Estructura de tabla para la tabla `configuracion_notificaciones`
--

CREATE TABLE `configuracion_notificaciones` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `notificaciones_email` tinyint(1) DEFAULT 1,
  `notificaciones_sms` tinyint(1) DEFAULT 0,
  `notificaciones_push` tinyint(1) DEFAULT 1,
  `notificaciones_whatsapp` tinyint(1) DEFAULT 0,
  `alerta_nivel_alto` tinyint(1) DEFAULT 1,
  `alerta_nivel_medio` tinyint(1) DEFAULT 1,
  `alerta_nivel_bajo` tinyint(1) DEFAULT 1,
  `notificar_supervisor` tinyint(1) DEFAULT 1,
  `notificar_admin` tinyint(1) DEFAULT 1,
  `notificar_conductores` tinyint(1) DEFAULT 0,
  `umbral_alto` decimal(5,3) DEFAULT 0.800,
  `umbral_medio` decimal(5,3) DEFAULT 0.500,
  `umbral_bajo` decimal(5,3) DEFAULT 0.300,
  `intervalo_notificaciones` int(11) DEFAULT 60,
  `horario_inicio` time DEFAULT '08:00:00',
  `horario_fin` time DEFAULT '18:00:00',
  `dias_activos` varchar(50) DEFAULT '1,2,3,4,5',
  `plantilla_email` text DEFAULT NULL,
  `plantilla_sms` text DEFAULT NULL,
  `configuracion_avanzada` text DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `configuracion_notificaciones`
--

INSERT INTO `configuracion_notificaciones` (`id`, `cliente_id`, `notificaciones_email`, `notificaciones_sms`, `notificaciones_push`, `notificaciones_whatsapp`, `alerta_nivel_alto`, `alerta_nivel_medio`, `alerta_nivel_bajo`, `notificar_supervisor`, `notificar_admin`, `notificar_conductores`, `umbral_alto`, `umbral_medio`, `umbral_bajo`, `intervalo_notificaciones`, `horario_inicio`, `horario_fin`, `dias_activos`, `plantilla_email`, `plantilla_sms`, `configuracion_avanzada`, `estado`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 1, 0, 1, 0, 1, 1, 1, 1, 1, 0, 0.800, 0.500, 0.300, 60, '08:00:00', '18:00:00', '1,2,3,4,5', 'Estimado usuario,\n\nSe ha registrado una prueba de alcohol con resultado: {resultado}.\nNivel: {nivel_alcohol} {unidad_medida}\nConductor: {conductor_nombre}\nFecha: {fecha_prueba}\n\nSaludos,\nSistema de Control de Alcohol', 'Alerta: Prueba {resultado}. Nivel: {nivel_alcohol}. Conductor: {conductor_nombre}', NULL, 1, '2025-11-28 05:18:02', '2025-11-28 05:18:02');

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
(1, 1, 'prueba_positiva', 1, 1, 1, 1, 1),
(2, 1, 'retest_fallido', 1, 1, 1, 1, 1),
(3, 1, 'conductor_bloqueado', 1, 1, 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_niveles_alcohol`
--

CREATE TABLE `historial_niveles_alcohol` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `limite_anterior` decimal(5,3) NOT NULL,
  `limite_nuevo` decimal(5,3) NOT NULL,
  `nivel_advertencia_anterior` decimal(5,3) NOT NULL,
  `nivel_advertencia_nuevo` decimal(5,3) NOT NULL,
  `nivel_critico_anterior` decimal(5,3) NOT NULL,
  `nivel_critico_nuevo` decimal(5,3) NOT NULL,
  `motivo_cambio` text DEFAULT NULL,
  `fecha_cambio` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `historial_planes`
--

CREATE TABLE `historial_planes` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `plan_anterior_id` int(11) DEFAULT NULL,
  `plan_nuevo_id` int(11) NOT NULL,
  `fecha_cambio` timestamp NULL DEFAULT current_timestamp(),
  `motivo_cambio` varchar(255) DEFAULT NULL,
  `cambio_por` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `licencias`
--

CREATE TABLE `licencias` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `conductor_id` int(11) NOT NULL,
  `numero_licencia` varchar(20) NOT NULL,
  `categoria` varchar(10) NOT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `estado` enum('activa','vencida','suspendida','inactiva') DEFAULT 'activa',
  `restricciones` text DEFAULT NULL,
  `archivo_adjunto` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `limite_conductores` int(11) DEFAULT 50,
  `limite_vehiculos` int(11) DEFAULT 50,
  `limite_alcoholimetros` int(11) DEFAULT 10,
  `reportes_avanzados` tinyint(1) DEFAULT 0,
  `soporte_prioritario` tinyint(1) DEFAULT 0,
  `acceso_api` tinyint(1) DEFAULT 0,
  `almacenamiento_fotos` int(11) DEFAULT 100,
  `backup_automatico` tinyint(1) DEFAULT 1,
  `retencion_datos_meses` int(11) DEFAULT 12,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `integraciones` tinyint(1) DEFAULT 0,
  `multi_sede` tinyint(1) DEFAULT 0,
  `personalizacion` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `planes`
--

INSERT INTO `planes` (`id`, `nombre_plan`, `precio_mensual`, `limite_pruebas_mes`, `limite_usuarios`, `limite_conductores`, `limite_vehiculos`, `limite_alcoholimetros`, `reportes_avanzados`, `soporte_prioritario`, `acceso_api`, `almacenamiento_fotos`, `backup_automatico`, `retencion_datos_meses`, `estado`, `fecha_creacion`, `integraciones`, `multi_sede`, `personalizacion`) VALUES
(1, 'Free', 0.00, 30, 1, 50, 50, 1, 0, 0, 0, 100, 1, 12, 1, '2025-11-25 12:10:30', 0, 0, 0),
(2, 'Starter', 49.00, 500, 5, 50, 50, 3, 0, 0, 0, 100, 1, 12, 1, '2025-11-25 12:10:30', 0, 0, 0),
(3, 'Professional', 149.00, 2000, 20, 50, 50, 10, 1, 1, 1, 100, 1, 12, 1, '2025-11-25 12:10:30', 1, 0, 1),
(4, 'Enterprise', 499.00, 99999, 99999, 50, 50, 99999, 1, 1, 1, 100, 1, 12, 1, '2025-11-25 12:10:30', 1, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programacion_backups`
--

CREATE TABLE `programacion_backups` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `tipo_backup` enum('completo','incremental','diferencial') DEFAULT 'completo',
  `frecuencia` enum('diario','semanal','mensual') DEFAULT 'diario',
  `hora_ejecucion` time DEFAULT '02:00:00',
  `dias_semana` varchar(20) DEFAULT NULL,
  `dia_mes` int(11) DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1,
  `ultima_ejecucion` timestamp NULL DEFAULT NULL,
  `proxima_ejecucion` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
  `motivo_retest` varchar(100) DEFAULT NULL,
  `aprobado_por_supervisor` tinyint(1) DEFAULT 0,
  `fecha_aprobacion_retest` timestamp NULL DEFAULT NULL,
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

--
-- Volcado de datos para la tabla `pruebas`
--

INSERT INTO `pruebas` (`id`, `cliente_id`, `alcoholimetro_id`, `conductor_id`, `supervisor_id`, `vehiculo_id`, `nivel_alcohol`, `limite_permisible`, `resultado`, `es_retest`, `prueba_padre_id`, `intento_numero`, `motivo_retest`, `aprobado_por_supervisor`, `fecha_aprobacion_retest`, `latitud`, `longitud`, `direccion_geocodificada`, `foto_evidencia`, `firma_conductor`, `firma_supervisor`, `observaciones`, `fecha_prueba`, `sync_movil`, `dispositivo_movil`, `hash_verificacion`, `temperatura_ambiente`, `humedad_ambiente`) VALUES
(1, 1, 3, 6, 3, 1, 0.100, 0.000, 'reprobado', 0, NULL, 1, NULL, 0, NULL, -12.15692800, -76.99169280, NULL, NULL, NULL, NULL, 'ninguna', '2025-11-30 04:05:37', 0, NULL, NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `regulaciones_alcohol`
--

CREATE TABLE `regulaciones_alcohol` (
  `id` int(11) NOT NULL,
  `pais` varchar(100) NOT NULL,
  `codigo_pais` varchar(5) NOT NULL,
  `limite_permisible` decimal(5,3) NOT NULL,
  `unidad_medida` varchar(10) DEFAULT 'g/L',
  `descripcion` text DEFAULT NULL,
  `activo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Volcado de datos para la tabla `regulaciones_alcohol`
--

INSERT INTO `regulaciones_alcohol` (`id`, `pais`, `codigo_pais`, `limite_permisible`, `unidad_medida`, `descripcion`, `activo`) VALUES
(1, 'Perú', 'PE', 0.000, 'g/L', 'Límite cero alcohol para conductores', 1),
(2, 'Chile', 'CL', 0.030, 'g/L', 'Límite general para conductores', 1),
(3, 'Argentina', 'AR', 0.000, 'g/L', 'Límite cero alcohol para conductores', 1),
(4, 'Colombia', 'CO', 0.020, 'g/L', 'Límite general para conductores', 1),
(5, 'México', 'MX', 0.040, 'g/L', 'Límite general para conductores', 1),
(6, 'España', 'ES', 0.050, 'g/L', 'Límite general para conductores experimentados', 1);

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
-- Estructura de tabla para la tabla `solicitudes_retest`
--

CREATE TABLE `solicitudes_retest` (
  `id` int(11) NOT NULL,
  `prueba_original_id` int(11) NOT NULL,
  `solicitado_por` int(11) NOT NULL,
  `motivo` text DEFAULT NULL,
  `estado` enum('pendiente','aprobado','rechazado') DEFAULT 'pendiente',
  `aprobado_por` int(11) DEFAULT NULL,
  `fecha_solicitud` timestamp NULL DEFAULT current_timestamp(),
  `fecha_resolucion` timestamp NULL DEFAULT NULL,
  `observaciones_aprobacion` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(2, 1, 'Admin', 'Demo', 'admin@demo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', NULL, '12345678', 'admin', NULL, 1, '2025-11-26 20:05:40', NULL, NULL, 0, NULL, '2025-11-25 12:10:30'),
(3, 1, 'Jose', 'Aguilar', 'fernando_7@hotmail.com', '$2y$10$RepK.j0a9EuYuf58yYEd1uMg4bAHfTDPJYPuNEqB1HpyEZnKbrMhW', '987456321', '40766447', 'supervisor', NULL, 1, NULL, NULL, NULL, 0, NULL, '2025-11-28 03:36:42'),
(6, 1, 'Pedro', 'Ramirez', 'radiantcenter.com@gmail.com', '$2y$10$KirIVOUPjbVHAzwWEh83TuWByE8M2hUoNNuIKJjYLInOGZsz5zcA.', '987456321', '40766447', 'conductor', NULL, 1, NULL, NULL, NULL, 0, NULL, '2025-11-29 07:08:47');

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
(2, 1, 'DEF-456', 'Nissan', 'Frontier', 2022, 'Negro', 25000, 'activo', '2025-11-25 12:10:30'),
(3, 1, 'DEF-456777', 'Nissan', 'Frontier', 2022, 'Negro', 25000, 'activo', '2025-11-29 06:49:50');

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_retests`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_retests` (
`cliente_id` int(11)
,`prueba_id` int(11)
,`nivel_alcohol` decimal(5,3)
,`resultado` enum('aprobado','reprobado')
,`es_retest` tinyint(1)
,`intento_numero` int(11)
,`nivel_original` decimal(5,3)
,`resultado_original` enum('aprobado','reprobado')
,`minutos_diferencia` bigint(21)
);

-- --------------------------------------------------------

--
-- Estructura Stand-in para la vista `vista_uso_planes`
-- (Véase abajo para la vista actual)
--
CREATE TABLE `vista_uso_planes` (
`cliente_id` int(11)
,`nombre_empresa` varchar(255)
,`nombre_plan` varchar(100)
,`limite_pruebas_mes` int(11)
,`pruebas_este_mes` bigint(21)
,`limite_usuarios` int(11)
,`usuarios_activos` bigint(21)
,`limite_alcoholimetros` int(11)
,`alcoholimetros_activos` bigint(21)
,`estado_pruebas` varchar(16)
);

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

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_retests`
--
DROP TABLE IF EXISTS `vista_retests`;

CREATE ALGORITHM=UNDEFINED DEFINER=`wepanel_juegosd2`@`localhost` SQL SECURITY DEFINER VIEW `vista_retests`  AS SELECT `p`.`cliente_id` AS `cliente_id`, `p`.`id` AS `prueba_id`, `p`.`nivel_alcohol` AS `nivel_alcohol`, `p`.`resultado` AS `resultado`, `p`.`es_retest` AS `es_retest`, `p`.`intento_numero` AS `intento_numero`, `p_prueba_original`.`nivel_alcohol` AS `nivel_original`, `p_prueba_original`.`resultado` AS `resultado_original`, timestampdiff(MINUTE,`p_prueba_original`.`fecha_prueba`,`p`.`fecha_prueba`) AS `minutos_diferencia` FROM (`pruebas` `p` left join `pruebas` `p_prueba_original` on(`p`.`prueba_padre_id` = `p_prueba_original`.`id`)) WHERE `p`.`es_retest` = 1 OR `p`.`prueba_padre_id` is not null ;

-- --------------------------------------------------------

--
-- Estructura para la vista `vista_uso_planes`
--
DROP TABLE IF EXISTS `vista_uso_planes`;

CREATE ALGORITHM=UNDEFINED DEFINER=`wepanel_juegosd2`@`localhost` SQL SECURITY DEFINER VIEW `vista_uso_planes`  AS SELECT `c`.`id` AS `cliente_id`, `c`.`nombre_empresa` AS `nombre_empresa`, `p`.`nombre_plan` AS `nombre_plan`, `p`.`limite_pruebas_mes` AS `limite_pruebas_mes`, (select count(0) from `pruebas` `pr` where `pr`.`cliente_id` = `c`.`id` and month(`pr`.`fecha_prueba`) = month(current_timestamp())) AS `pruebas_este_mes`, `p`.`limite_usuarios` AS `limite_usuarios`, (select count(0) from `usuarios` `u` where `u`.`cliente_id` = `c`.`id`) AS `usuarios_activos`, `p`.`limite_alcoholimetros` AS `limite_alcoholimetros`, (select count(0) from `alcoholimetros` `a` where `a`.`cliente_id` = `c`.`id`) AS `alcoholimetros_activos`, CASE WHEN (select count(0) from `pruebas` `pr` where `pr`.`cliente_id` = `c`.`id` AND month(`pr`.`fecha_prueba`) = month(current_timestamp())) >= `p`.`limite_pruebas_mes` THEN 'LIMITE_ALCANZADO' ELSE 'DENTRO_LIMITE' END AS `estado_pruebas` FROM (`clientes` `c` join `planes` `p` on(`c`.`plan_id` = `p`.`id`)) ;

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
-- Indices de la tabla `configuracion_notificaciones`
--
ALTER TABLE `configuracion_notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cliente` (`cliente_id`);

--
-- Indices de la tabla `config_notificaciones_eventos`
--
ALTER TABLE `config_notificaciones_eventos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_cliente_evento` (`cliente_id`,`evento`);

--
-- Indices de la tabla `historial_niveles_alcohol`
--
ALTER TABLE `historial_niveles_alcohol`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `historial_planes`
--
ALTER TABLE `historial_planes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `plan_anterior_id` (`plan_anterior_id`),
  ADD KEY `plan_nuevo_id` (`plan_nuevo_id`),
  ADD KEY `cambio_por` (`cambio_por`);

--
-- Indices de la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_numero_licencia` (`cliente_id`,`numero_licencia`),
  ADD KEY `conductor_id` (`conductor_id`),
  ADD KEY `cliente_id` (`cliente_id`);

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
-- Indices de la tabla `programacion_backups`
--
ALTER TABLE `programacion_backups`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

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
-- Indices de la tabla `regulaciones_alcohol`
--
ALTER TABLE `regulaciones_alcohol`
  ADD PRIMARY KEY (`id`);

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
-- Indices de la tabla `solicitudes_retest`
--
ALTER TABLE `solicitudes_retest`
  ADD PRIMARY KEY (`id`),
  ADD KEY `prueba_original_id` (`prueba_original_id`),
  ADD KEY `solicitado_por` (`solicitado_por`),
  ADD KEY `aprobado_por` (`aprobado_por`);

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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=50;

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
-- AUTO_INCREMENT de la tabla `configuracion_notificaciones`
--
ALTER TABLE `configuracion_notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `config_notificaciones_eventos`
--
ALTER TABLE `config_notificaciones_eventos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `historial_niveles_alcohol`
--
ALTER TABLE `historial_niveles_alcohol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `historial_planes`
--
ALTER TABLE `historial_planes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `licencias`
--
ALTER TABLE `licencias`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

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
-- AUTO_INCREMENT de la tabla `programacion_backups`
--
ALTER TABLE `programacion_backups`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pruebas`
--
ALTER TABLE `pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `regulaciones_alcohol`
--
ALTER TABLE `regulaciones_alcohol`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

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
-- AUTO_INCREMENT de la tabla `solicitudes_retest`
--
ALTER TABLE `solicitudes_retest`
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
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT de la tabla `vehiculos`
--
ALTER TABLE `vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

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
-- Filtros para la tabla `configuracion_notificaciones`
--
ALTER TABLE `configuracion_notificaciones`
  ADD CONSTRAINT `config_notificaciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `config_notificaciones_eventos`
--
ALTER TABLE `config_notificaciones_eventos`
  ADD CONSTRAINT `config_notificaciones_eventos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

--
-- Filtros para la tabla `historial_niveles_alcohol`
--
ALTER TABLE `historial_niveles_alcohol`
  ADD CONSTRAINT `historial_niveles_alcohol_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `historial_niveles_alcohol_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `historial_planes`
--
ALTER TABLE `historial_planes`
  ADD CONSTRAINT `historial_planes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`),
  ADD CONSTRAINT `historial_planes_ibfk_2` FOREIGN KEY (`plan_anterior_id`) REFERENCES `planes` (`id`),
  ADD CONSTRAINT `historial_planes_ibfk_3` FOREIGN KEY (`plan_nuevo_id`) REFERENCES `planes` (`id`),
  ADD CONSTRAINT `historial_planes_ibfk_4` FOREIGN KEY (`cambio_por`) REFERENCES `usuarios` (`id`);

--
-- Filtros para la tabla `licencias`
--
ALTER TABLE `licencias`
  ADD CONSTRAINT `licencias_ibfk_1` FOREIGN KEY (`conductor_id`) REFERENCES `usuarios` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `licencias_ibfk_2` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`) ON DELETE CASCADE;

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
-- Filtros para la tabla `programacion_backups`
--
ALTER TABLE `programacion_backups`
  ADD CONSTRAINT `programacion_backups_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `clientes` (`id`);

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
-- Filtros para la tabla `solicitudes_retest`
--
ALTER TABLE `solicitudes_retest`
  ADD CONSTRAINT `solicitudes_retest_ibfk_1` FOREIGN KEY (`prueba_original_id`) REFERENCES `pruebas` (`id`),
  ADD CONSTRAINT `solicitudes_retest_ibfk_2` FOREIGN KEY (`solicitado_por`) REFERENCES `usuarios` (`id`),
  ADD CONSTRAINT `solicitudes_retest_ibfk_3` FOREIGN KEY (`aprobado_por`) REFERENCES `usuarios` (`id`);

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
