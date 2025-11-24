-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost
-- Tiempo de generación: 24-11-2025 a las 03:46:45
-- Versión del servidor: 10.11.13-MariaDB-cll-lve
-- Versión de PHP: 8.3.19

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `juegosd2_alcoholimetro`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `system_config`
--

CREATE TABLE `system_config` (
  `id` int(11) NOT NULL,
  `config_key` varchar(255) NOT NULL,
  `config_value` text DEFAULT NULL,
  `config_type` enum('string','number','boolean','json') DEFAULT 'string',
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_alcoholimetros`
--

CREATE TABLE `tb_alcoholimetros` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `numero_serie` varchar(50) NOT NULL,
  `nombre_activo` varchar(100) NOT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `codigo_ecu` varchar(50) DEFAULT NULL,
  `anio_adquisicion` year(4) NOT NULL,
  `fecha_calibracion` date DEFAULT NULL,
  `proxima_calibracion` date DEFAULT NULL,
  `estado` enum('activo','mantenimiento','inactivo','calibracion') DEFAULT 'activo',
  `qr_code` varchar(255) DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp(),
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tb_alcoholimetros`
--

INSERT INTO `tb_alcoholimetros` (`id`, `cliente_id`, `numero_serie`, `nombre_activo`, `modelo`, `marca`, `codigo_ecu`, `anio_adquisicion`, `fecha_calibracion`, `proxima_calibracion`, `estado`, `qr_code`, `fecha_creacion`, `fecha_actualizacion`) VALUES
(1, 1, 'ALC-001', 'Alcoholímetro Principal', 'Modelo X100', 'Marca A', 'ECU-7854', '2024', '2024-01-15', '2024-07-15', 'activo', 'QR_ALC_001_123', '2025-11-20 02:55:53', '2025-11-20 02:55:53'),
(2, 1, 'ALC-002', 'Alcoholímetro Secundario', 'Modelo X200', 'Marca B', 'ECU-8745', '2023', '2024-02-20', '2024-08-20', 'activo', 'QR_ALC_002_456', '2025-11-20 02:55:53', '2025-11-20 02:55:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_alertas`
--

CREATE TABLE `tb_alertas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre_alerta` varchar(100) NOT NULL,
  `tipo` enum('calibracion','prueba_reprobada','vencimiento_licencia','pago') DEFAULT 'calibracion',
  `condicion` text NOT NULL,
  `accion` enum('email','push','ambas') DEFAULT 'email',
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tb_alertas`
--

INSERT INTO `tb_alertas` (`id`, `cliente_id`, `nombre_alerta`, `tipo`, `condicion`, `accion`, `estado`) VALUES
(1, 1, 'Calibración Próxima', 'calibracion', 'proxima_calibracion <= 7', 'email', 1),
(2, 1, 'Prueba Reprobada', 'prueba_reprobada', 'resultado = \"reprobado\"', 'ambas', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_auditoria`
--

CREATE TABLE `tb_auditoria` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `accion` varchar(100) NOT NULL,
  `tabla_afectada` varchar(50) DEFAULT NULL,
  `registro_id` int(11) DEFAULT NULL,
  `valores_anteriores` text DEFAULT NULL,
  `valores_nuevos` text DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `user_agent` text DEFAULT NULL,
  `fecha_accion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_clientes`
--

CREATE TABLE `tb_clientes` (
  `id` int(11) NOT NULL,
  `nombre_empresa` varchar(255) NOT NULL,
  `ruc` varchar(20) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `email_contacto` varchar(255) DEFAULT NULL,
  `plan_id` int(11) NOT NULL,
  `fecha_registro` timestamp NULL DEFAULT current_timestamp(),
  `fecha_vencimiento` date DEFAULT NULL,
  `estado` enum('activo','inactivo','suspendido','prueba') DEFAULT 'prueba',
  `limite_usuarios` int(11) DEFAULT 5,
  `limite_alcoholimetros` int(11) DEFAULT 10,
  `token_api` varchar(100) DEFAULT NULL,
  `modo_demo` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tb_clientes`
--

INSERT INTO `tb_clientes` (`id`, `nombre_empresa`, `ruc`, `direccion`, `telefono`, `email_contacto`, `plan_id`, `fecha_registro`, `fecha_vencimiento`, `estado`, `limite_usuarios`, `limite_alcoholimetros`, `token_api`, `modo_demo`) VALUES
(1, 'Empresa Demo SA', '20123456789', 'Av. Ejemplo 123, Lima', '+51 987654321', 'admin@empresademo.com', 4, '2025-11-20 02:55:53', '2025-12-05', 'prueba', 2, 3, 'demo_token_123456', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_configuraciones`
--

CREATE TABLE `tb_configuraciones` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `limite_alcohol_permisible` decimal(5,3) DEFAULT 0.000,
  `requerir_geolocalizacion` tinyint(1) DEFAULT 1,
  `requerir_foto_evidencia` tinyint(1) DEFAULT 0,
  `notificaciones_email` tinyint(1) DEFAULT 1,
  `notificaciones_push` tinyint(1) DEFAULT 1,
  `timezone` varchar(50) DEFAULT 'America/Lima',
  `idioma` enum('es','en') DEFAULT 'es',
  `formato_fecha` varchar(20) DEFAULT 'd/m/Y',
  `fecha_actualizacion` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tb_configuraciones`
--

INSERT INTO `tb_configuraciones` (`id`, `cliente_id`, `limite_alcohol_permisible`, `requerir_geolocalizacion`, `requerir_foto_evidencia`, `notificaciones_email`, `notificaciones_push`, `timezone`, `idioma`, `formato_fecha`, `fecha_actualizacion`) VALUES
(1, 1, 0.000, 1, 0, 1, 1, 'America/Lima', 'es', 'd/m/Y', '2025-11-20 02:55:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_dispositivos_moviles`
--

CREATE TABLE `tb_dispositivos_moviles` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `usuario_id` int(11) NOT NULL,
  `device_id` varchar(255) NOT NULL,
  `device_name` varchar(100) DEFAULT NULL,
  `modelo` varchar(100) DEFAULT NULL,
  `os_version` varchar(50) DEFAULT NULL,
  `app_version` varchar(20) DEFAULT NULL,
  `token_firebase` varchar(255) DEFAULT NULL,
  `ultima_conexion` timestamp NULL DEFAULT NULL,
  `estado` enum('activo','inactivo','bloqueado') DEFAULT 'activo',
  `fecha_registro` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_documentos`
--

CREATE TABLE `tb_documentos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre_documento` varchar(255) NOT NULL,
  `tipo_documento` enum('manual','procedimiento','contrato','certificado','otro') DEFAULT 'manual',
  `ruta_archivo` varchar(500) NOT NULL,
  `tamanio` int(11) DEFAULT NULL,
  `usuario_subio` int(11) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `fecha_subida` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_facturacion`
--

CREATE TABLE `tb_facturacion` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `plan_id` int(11) NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `estado_pago` enum('pendiente','pagado','vencido','cancelado') DEFAULT 'pendiente',
  `metodo_pago` varchar(50) DEFAULT NULL,
  `referencia_pago` varchar(100) DEFAULT NULL,
  `fecha_pago` date DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_licencias_conductor`
--

CREATE TABLE `tb_licencias_conductor` (
  `id` int(11) NOT NULL,
  `conductor_id` int(11) NOT NULL,
  `numero_licencia` varchar(50) NOT NULL,
  `categoria` varchar(10) DEFAULT NULL,
  `fecha_emision` date NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `foto_licencia` varchar(255) DEFAULT NULL,
  `estado` enum('vigente','vencida','suspendida') DEFAULT 'vigente',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tb_licencias_conductor`
--

INSERT INTO `tb_licencias_conductor` (`id`, `conductor_id`, `numero_licencia`, `categoria`, `fecha_emision`, `fecha_vencimiento`, `foto_licencia`, `estado`, `fecha_creacion`) VALUES
(1, 2, 'B12345678', 'AIIB', '2023-01-15', '2028-01-15', NULL, 'vigente', '2025-11-20 02:55:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_mantenimientos`
--

CREATE TABLE `tb_mantenimientos` (
  `id` int(11) NOT NULL,
  `alcoholimetro_id` int(11) NOT NULL,
  `tipo_mantenimiento` enum('calibracion','limpieza','reparacion','general') DEFAULT 'calibracion',
  `fecha_mantenimiento` date NOT NULL,
  `proximo_mantenimiento` date DEFAULT NULL,
  `tecnico_responsable` int(11) DEFAULT NULL,
  `descripcion` text DEFAULT NULL,
  `costo` decimal(8,2) DEFAULT NULL,
  `estado` enum('programado','en_proceso','completado','cancelado') DEFAULT 'programado',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_notificaciones`
--

CREATE TABLE `tb_notificaciones` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `usuario_id` int(11) DEFAULT NULL,
  `titulo` varchar(255) NOT NULL,
  `mensaje` text NOT NULL,
  `tipo` enum('sistema','alerta','recordatorio','pago') DEFAULT 'sistema',
  `leida` tinyint(1) DEFAULT 0,
  `fecha_envio` timestamp NULL DEFAULT current_timestamp(),
  `fecha_leida` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_planes`
--

CREATE TABLE `tb_planes` (
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
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tb_planes`
--

INSERT INTO `tb_planes` (`id`, `nombre_plan`, `precio_mensual`, `limite_pruebas_mes`, `limite_usuarios`, `limite_alcoholimetros`, `reportes_avanzados`, `soporte_prioritario`, `acceso_api`, `almacenamiento_fotos`, `estado`, `fecha_creacion`) VALUES
(1, 'Básico', 49.00, 500, 3, 5, 0, 0, 0, 100, 1, '2025-11-20 02:55:53'),
(2, 'Profesional', 99.00, 2000, 10, 20, 1, 0, 1, 500, 1, '2025-11-20 02:55:53'),
(3, 'Empresarial', 199.00, 10000, 50, 100, 1, 1, 1, 2000, 1, '2025-11-20 02:55:53'),
(4, 'Prueba', 0.00, 100, 2, 3, 0, 0, 0, 50, 1, '2025-11-20 02:55:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_pruebas`
--

CREATE TABLE `tb_pruebas` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `alcoholimetro_id` int(11) NOT NULL,
  `conductor_id` int(11) NOT NULL,
  `supervisor_id` int(11) NOT NULL,
  `vehiculo_id` int(11) DEFAULT NULL,
  `nivel_alcohol` decimal(5,3) NOT NULL,
  `limite_permisible` decimal(5,3) DEFAULT 0.000,
  `resultado` enum('aprobado','reprobado') NOT NULL,
  `latitud` decimal(10,8) DEFAULT NULL,
  `longitud` decimal(11,8) DEFAULT NULL,
  `direccion_geocodificada` text DEFAULT NULL,
  `foto_evidencia` varchar(255) DEFAULT NULL,
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
-- Estructura de tabla para la tabla `tb_reportes`
--

CREATE TABLE `tb_reportes` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre_reporte` varchar(255) NOT NULL,
  `tipo_reporte` enum('conductor','alcoholimetro','general','personalizado','legal') NOT NULL,
  `fecha_inicio` date NOT NULL,
  `fecha_fin` date NOT NULL,
  `parametros` text DEFAULT NULL,
  `url_archivo` varchar(500) DEFAULT NULL,
  `estado` enum('generando','completado','error') DEFAULT 'generando',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_sesiones`
--

CREATE TABLE `tb_sesiones` (
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
-- Estructura de tabla para la tabla `tb_turnos`
--

CREATE TABLE `tb_turnos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre_turno` varchar(100) NOT NULL,
  `hora_inicio` time NOT NULL,
  `hora_fin` time NOT NULL,
  `dias_semana` varchar(20) DEFAULT '1,2,3,4,5',
  `estado` tinyint(1) DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tb_turnos`
--

INSERT INTO `tb_turnos` (`id`, `cliente_id`, `nombre_turno`, `hora_inicio`, `hora_fin`, `dias_semana`, `estado`) VALUES
(1, 1, 'Turno Mañana', '06:00:00', '14:00:00', '1,2,3,4,5', 1),
(2, 1, 'Turno Tarde', '14:00:00', '22:00:00', '1,2,3,4,5', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_usuarios`
--

CREATE TABLE `tb_usuarios` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `telefono` varchar(20) DEFAULT NULL,
  `dni` varchar(15) DEFAULT NULL,
  `rol` enum('admin','supervisor','conductor','tecnico','auditor') NOT NULL,
  `foto_perfil` varchar(255) DEFAULT NULL,
  `estado` tinyint(1) DEFAULT 1,
  `ultimo_login` timestamp NULL DEFAULT NULL,
  `token_recuperacion` varchar(100) DEFAULT NULL,
  `fecha_expiracion_token` datetime DEFAULT NULL,
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tb_usuarios`
--

INSERT INTO `tb_usuarios` (`id`, `cliente_id`, `nombre`, `email`, `password`, `telefono`, `dni`, `rol`, `foto_perfil`, `estado`, `ultimo_login`, `token_recuperacion`, `fecha_expiracion_token`, `fecha_creacion`) VALUES
(1, 1, 'Administrador Principal', 'admin@empresademo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+51 987654321', '12345678', 'admin', NULL, 1, '2025-11-20 03:55:23', NULL, NULL, '2025-11-20 02:55:53'),
(2, 1, 'Conductor Demo', 'conductor@empresademo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+51 987654322', '87654321', 'conductor', NULL, 1, NULL, NULL, NULL, '2025-11-20 02:55:53'),
(3, 1, 'Supervisor Demo', 'supervisor@empresademo.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '+51 987654323', '11223344', 'supervisor', NULL, 1, NULL, NULL, NULL, '2025-11-20 02:55:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tb_vehiculos`
--

CREATE TABLE `tb_vehiculos` (
  `id` int(11) NOT NULL,
  `cliente_id` int(11) NOT NULL,
  `placa` varchar(20) NOT NULL,
  `marca` varchar(50) DEFAULT NULL,
  `modelo` varchar(50) DEFAULT NULL,
  `anio` year(4) DEFAULT NULL,
  `color` varchar(30) DEFAULT NULL,
  `conductor_asignado` int(11) DEFAULT NULL,
  `kilometraje` int(11) DEFAULT NULL,
  `estado` enum('activo','mantenimiento','inactivo') DEFAULT 'activo',
  `fecha_creacion` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tb_vehiculos`
--

INSERT INTO `tb_vehiculos` (`id`, `cliente_id`, `placa`, `marca`, `modelo`, `anio`, `color`, `conductor_asignado`, `kilometraje`, `estado`, `fecha_creacion`) VALUES
(1, 1, 'ABC-123', 'Toyota', 'Hilux', '2023', 'Blanco', 2, 15000, 'activo', '2025-11-20 02:55:53'),
(2, 1, 'DEF-456', 'Nissan', 'Frontier', '2022', 'Negro', 2, 25000, 'activo', '2025-11-20 02:55:53');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `role`, `is_active`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@sistema.com', '$2y$10$eqieciCjbL7tTgnmTgRukOeTGsurmOMWsZ6L2F9lhZdRRHeXIk1Bq', 'Administrador del Sistema', 'admin', 1, '2025-11-20 05:05:24', '2025-11-20 05:05:24'),
(2, 'faguilar', 'faguilar@rocotodigital.com', '$2y$10$jgXcxUqzRRCcsyk8HnSr5.DACmnDMedihbd8EBLO9fbzv6LIwaUsK', 'Jose Fernando Aguilar Rivas', 'user', 1, '2025-11-20 20:26:17', '2025-11-20 14:38:23'),
(3, 'jeyko', 'jeyko@rocotodigital.com', '$2y$10$SohNiz5P7kJNZVp4S5PDjeKfqXCf3vaecQ0wXmhGKeWXeWm5m1iFe', 'Jeyko Aguilar Jove', 'user', 1, '2025-11-20 20:59:20', '2025-11-20 20:59:20');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `system_config`
--
ALTER TABLE `system_config`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `config_key` (`config_key`);

--
-- Indices de la tabla `tb_alcoholimetros`
--
ALTER TABLE `tb_alcoholimetros`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_serie_cliente` (`cliente_id`,`numero_serie`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `tb_alertas`
--
ALTER TABLE `tb_alertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `tb_auditoria`
--
ALTER TABLE `tb_auditoria`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fecha_accion` (`fecha_accion`);

--
-- Indices de la tabla `tb_clientes`
--
ALTER TABLE `tb_clientes`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `ruc` (`ruc`),
  ADD UNIQUE KEY `token_api` (`token_api`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indices de la tabla `tb_configuraciones`
--
ALTER TABLE `tb_configuraciones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `tb_dispositivos_moviles`
--
ALTER TABLE `tb_dispositivos_moviles`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `device_id` (`device_id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tb_documentos`
--
ALTER TABLE `tb_documentos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_subio` (`usuario_subio`);

--
-- Indices de la tabla `tb_facturacion`
--
ALTER TABLE `tb_facturacion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `plan_id` (`plan_id`);

--
-- Indices de la tabla `tb_licencias_conductor`
--
ALTER TABLE `tb_licencias_conductor`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `numero_licencia` (`numero_licencia`),
  ADD KEY `conductor_id` (`conductor_id`),
  ADD KEY `fecha_vencimiento` (`fecha_vencimiento`);

--
-- Indices de la tabla `tb_mantenimientos`
--
ALTER TABLE `tb_mantenimientos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `alcoholimetro_id` (`alcoholimetro_id`),
  ADD KEY `tecnico_responsable` (`tecnico_responsable`);

--
-- Indices de la tabla `tb_notificaciones`
--
ALTER TABLE `tb_notificaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `usuario_id` (`usuario_id`);

--
-- Indices de la tabla `tb_planes`
--
ALTER TABLE `tb_planes`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `tb_pruebas`
--
ALTER TABLE `tb_pruebas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `alcoholimetro_id` (`alcoholimetro_id`),
  ADD KEY `conductor_id` (`conductor_id`),
  ADD KEY `supervisor_id` (`supervisor_id`),
  ADD KEY `vehiculo_id` (`vehiculo_id`),
  ADD KEY `fecha_prueba` (`fecha_prueba`),
  ADD KEY `hash_verificacion` (`hash_verificacion`);

--
-- Indices de la tabla `tb_reportes`
--
ALTER TABLE `tb_reportes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `tb_sesiones`
--
ALTER TABLE `tb_sesiones`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `token_sesion` (`token_sesion`),
  ADD KEY `usuario_id` (`usuario_id`),
  ADD KEY `fecha_expiracion` (`fecha_expiracion`);

--
-- Indices de la tabla `tb_turnos`
--
ALTER TABLE `tb_turnos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `tb_usuarios`
--
ALTER TABLE `tb_usuarios`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD UNIQUE KEY `dni_cliente` (`cliente_id`,`dni`),
  ADD KEY `cliente_id` (`cliente_id`);

--
-- Indices de la tabla `tb_vehiculos`
--
ALTER TABLE `tb_vehiculos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `unique_placa_cliente` (`cliente_id`,`placa`),
  ADD KEY `cliente_id` (`cliente_id`),
  ADD KEY `conductor_asignado` (`conductor_asignado`);

--
-- Indices de la tabla `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `system_config`
--
ALTER TABLE `system_config`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_alcoholimetros`
--
ALTER TABLE `tb_alcoholimetros`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tb_alertas`
--
ALTER TABLE `tb_alertas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tb_auditoria`
--
ALTER TABLE `tb_auditoria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_clientes`
--
ALTER TABLE `tb_clientes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tb_configuraciones`
--
ALTER TABLE `tb_configuraciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tb_dispositivos_moviles`
--
ALTER TABLE `tb_dispositivos_moviles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_documentos`
--
ALTER TABLE `tb_documentos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_facturacion`
--
ALTER TABLE `tb_facturacion`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_licencias_conductor`
--
ALTER TABLE `tb_licencias_conductor`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `tb_mantenimientos`
--
ALTER TABLE `tb_mantenimientos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_notificaciones`
--
ALTER TABLE `tb_notificaciones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_planes`
--
ALTER TABLE `tb_planes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `tb_pruebas`
--
ALTER TABLE `tb_pruebas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_reportes`
--
ALTER TABLE `tb_reportes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_sesiones`
--
ALTER TABLE `tb_sesiones`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `tb_turnos`
--
ALTER TABLE `tb_turnos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `tb_usuarios`
--
ALTER TABLE `tb_usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `tb_vehiculos`
--
ALTER TABLE `tb_vehiculos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `tb_alcoholimetros`
--
ALTER TABLE `tb_alcoholimetros`
  ADD CONSTRAINT `tb_alcoholimetros_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`);

--
-- Filtros para la tabla `tb_alertas`
--
ALTER TABLE `tb_alertas`
  ADD CONSTRAINT `tb_alertas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`);

--
-- Filtros para la tabla `tb_auditoria`
--
ALTER TABLE `tb_auditoria`
  ADD CONSTRAINT `tb_auditoria_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`),
  ADD CONSTRAINT `tb_auditoria_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `tb_usuarios` (`id`);

--
-- Filtros para la tabla `tb_clientes`
--
ALTER TABLE `tb_clientes`
  ADD CONSTRAINT `tb_clientes_ibfk_1` FOREIGN KEY (`plan_id`) REFERENCES `tb_planes` (`id`);

--
-- Filtros para la tabla `tb_configuraciones`
--
ALTER TABLE `tb_configuraciones`
  ADD CONSTRAINT `tb_configuraciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`);

--
-- Filtros para la tabla `tb_dispositivos_moviles`
--
ALTER TABLE `tb_dispositivos_moviles`
  ADD CONSTRAINT `tb_dispositivos_moviles_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`),
  ADD CONSTRAINT `tb_dispositivos_moviles_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `tb_usuarios` (`id`);

--
-- Filtros para la tabla `tb_documentos`
--
ALTER TABLE `tb_documentos`
  ADD CONSTRAINT `tb_documentos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`),
  ADD CONSTRAINT `tb_documentos_ibfk_2` FOREIGN KEY (`usuario_subio`) REFERENCES `tb_usuarios` (`id`);

--
-- Filtros para la tabla `tb_facturacion`
--
ALTER TABLE `tb_facturacion`
  ADD CONSTRAINT `tb_facturacion_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`),
  ADD CONSTRAINT `tb_facturacion_ibfk_2` FOREIGN KEY (`plan_id`) REFERENCES `tb_planes` (`id`);

--
-- Filtros para la tabla `tb_licencias_conductor`
--
ALTER TABLE `tb_licencias_conductor`
  ADD CONSTRAINT `tb_licencias_conductor_ibfk_1` FOREIGN KEY (`conductor_id`) REFERENCES `tb_usuarios` (`id`);

--
-- Filtros para la tabla `tb_mantenimientos`
--
ALTER TABLE `tb_mantenimientos`
  ADD CONSTRAINT `tb_mantenimientos_ibfk_1` FOREIGN KEY (`alcoholimetro_id`) REFERENCES `tb_alcoholimetros` (`id`),
  ADD CONSTRAINT `tb_mantenimientos_ibfk_2` FOREIGN KEY (`tecnico_responsable`) REFERENCES `tb_usuarios` (`id`);

--
-- Filtros para la tabla `tb_notificaciones`
--
ALTER TABLE `tb_notificaciones`
  ADD CONSTRAINT `tb_notificaciones_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`),
  ADD CONSTRAINT `tb_notificaciones_ibfk_2` FOREIGN KEY (`usuario_id`) REFERENCES `tb_usuarios` (`id`);

--
-- Filtros para la tabla `tb_pruebas`
--
ALTER TABLE `tb_pruebas`
  ADD CONSTRAINT `tb_pruebas_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`),
  ADD CONSTRAINT `tb_pruebas_ibfk_2` FOREIGN KEY (`alcoholimetro_id`) REFERENCES `tb_alcoholimetros` (`id`),
  ADD CONSTRAINT `tb_pruebas_ibfk_3` FOREIGN KEY (`conductor_id`) REFERENCES `tb_usuarios` (`id`),
  ADD CONSTRAINT `tb_pruebas_ibfk_4` FOREIGN KEY (`supervisor_id`) REFERENCES `tb_usuarios` (`id`),
  ADD CONSTRAINT `tb_pruebas_ibfk_5` FOREIGN KEY (`vehiculo_id`) REFERENCES `tb_vehiculos` (`id`);

--
-- Filtros para la tabla `tb_reportes`
--
ALTER TABLE `tb_reportes`
  ADD CONSTRAINT `tb_reportes_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`);

--
-- Filtros para la tabla `tb_sesiones`
--
ALTER TABLE `tb_sesiones`
  ADD CONSTRAINT `tb_sesiones_ibfk_1` FOREIGN KEY (`usuario_id`) REFERENCES `tb_usuarios` (`id`);

--
-- Filtros para la tabla `tb_turnos`
--
ALTER TABLE `tb_turnos`
  ADD CONSTRAINT `tb_turnos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`);

--
-- Filtros para la tabla `tb_usuarios`
--
ALTER TABLE `tb_usuarios`
  ADD CONSTRAINT `tb_usuarios_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`);

--
-- Filtros para la tabla `tb_vehiculos`
--
ALTER TABLE `tb_vehiculos`
  ADD CONSTRAINT `tb_vehiculos_ibfk_1` FOREIGN KEY (`cliente_id`) REFERENCES `tb_clientes` (`id`),
  ADD CONSTRAINT `tb_vehiculos_ibfk_2` FOREIGN KEY (`conductor_asignado`) REFERENCES `tb_usuarios` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
