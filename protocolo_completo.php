<?php
// protocolo_completo.php
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/includes/Database.php';

$page_title = 'Protocolo Completo de Alcoholímetro';
$breadcrumbs = [
    'index.php' => 'Dashboard',
    'protocolo_completo.php' => 'Protocolo Completo'
];

require_once __DIR__ . '/includes/header.php';

$db = new Database();
$user_id = $_SESSION['user_id'] ?? 0;
$cliente_id = $_SESSION['cliente_id'] ?? 0;

// OBTENER DATOS DEL CLIENTE Y USUARIO
$cliente = [];
$usuario = [];
$alcoholimetros = [];
$ubicaciones = [];

try {
    // Obtener datos del cliente
    $stmt = $db->prepare("SELECT nombre_empresa FROM clientes WHERE id = ?");
    $stmt->execute([$cliente_id]);
    $cliente = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Obtener datos del usuario actual
    $stmt = $db->prepare("SELECT nombre, apellido, dni, rol FROM usuarios WHERE id = ?");
    $stmt->execute([$user_id]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC) ?: [];
    
    // Obtener alcoholímetros del cliente
    $stmt = $db->prepare("SELECT id, numero_serie, nombre_activo, marca, modelo, fecha_calibracion FROM alcoholimetros WHERE cliente_id = ? AND estado = 'activo'");
    $stmt->execute([$cliente_id]);
    $alcoholimetros = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // OBTENER UBICACIONES DESDE LA BASE DE DATOS
    $stmt = $db->prepare("SELECT nombre_ubicacion FROM ubicaciones_cliente WHERE cliente_id = ? AND estado = 1 ORDER BY nombre_ubicacion");
    $stmt->execute([$cliente_id]);
    $ubicaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (Exception $e) {
    // En caso de error, usar valores por defecto
    error_log("Error cargando datos: " . $e->getMessage());
}
?>

<div class="content-body">
    <!-- HEADER -->
    <div class="dashboard-header">
        <div class="welcome-section">
            <h1><?php echo $page_title; ?></h1>
            <p class="dashboard-subtitle">Registro completo del protocolo de alcoholemia según normativa</p>
        </div>
        <div class="header-actions">
            <a href="index.php" class="btn btn-outline">
                <i class="fas fa-arrow-left"></i>Volver al Dashboard
            </a>
        </div>
    </div>

    <!-- ALERTAS -->
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

    <!-- FORMULARIO PRINCIPAL CON TABS -->
    <div class="crud-container">
        <form id="formProtocoloCompleto" method="POST" class="modal-form">
            
            <!-- NAVEGACIÓN POR TABS -->
            <div class="tabs-navigation">
                <div class="tabs-header">
                    <button type="button" class="tab-btn active" data-tab="tab-operacion">
                        <i class="fas fa-clipboard-list"></i>
                        <span>Operación</span>
                    </button>
                    <button type="button" class="tab-btn" data-tab="tab-chequeo">
                        <i class="fas fa-check-circle"></i>
                        <span>Lista de Chequeo</span>
                    </button>
                    <button type="button" class="tab-btn" data-tab="tab-pruebas">
                        <i class="fas fa-vial"></i>
                        <span>Pruebas Individuales</span>
                    </button>
                    <button type="button" class="tab-btn" data-tab="tab-consentimiento">
                        <i class="fas fa-file-signature"></i>
                        <span>Acta Consentimiento</span>
                    </button>
                    <button type="button" class="tab-btn" data-tab="tab-encuesta">
                        <i class="fas fa-question-circle"></i>
                        <span>Encuesta Preliminar</span>
                    </button>
                    <button type="button" class="tab-btn" data-tab="tab-widmark">
                        <i class="fas fa-chart-line"></i>
                        <span>Registro Widmark</span>
                    </button>
                </div>
            </div>

            <!-- CONTENIDO DE TABS -->
            <div class="tabs-content">
                
                <!-- TAB 1: OPERACIÓN -->
                <div id="tab-operacion" class="tab-pane active">
    <div class="card">
        <div class="card-header">
            <h3><i class="fas fa-clipboard-list"></i> Operación de Alcoholemia</h3>
        </div>
        <div class="card-body">

                
                <!-- DATOS GENERALES -->
                <div class="form-section">
                    <h4 class="section-title">DATOS GENERALES</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="empresa" class="form-label">Empresa</label>
                            <!-- Empresa -->
							<input type="text" id="empresa" name="empresa" class="form-control" 
								   value="<?php echo htmlspecialchars($cliente['nombre_empresa'] ?? 'Empresa Demo S.A.C.'); ?>" readonly>

                        </div>
                        
                        <div class="form-group">
							<label for="sede_area_unidad" class="form-label">Sede/Area/Unidad</label>
							<select id="sede_area_unidad" name="sede_area_unidad" class="form-control" required>
								<option value="">Seleccionar</option>
								<?php if (!empty($ubicaciones)): ?>
									<?php foreach ($ubicaciones as $ubicacion): ?>
										<option value="<?php echo htmlspecialchars($ubicacion['nombre_ubicacion']); ?>">
											<?php echo htmlspecialchars($ubicacion['nombre_ubicacion']); ?>
										</option>
									<?php endforeach; ?>
								<?php else: ?>
									<!-- Opciones por defecto en caso de que no haya ubicaciones en la BD -->
									<option value="Lima/Planta 1">Lima/Planta 1</option>
									<option value="Lima/Planta 2">Lima/Planta 2</option>
									<option value="Lima/Planta 3">Lima/Planta 3</option>
									<option value="Almacen">Almacen</option>
								<?php endif; ?>
							</select>
						</div>
                        
                        <div class="form-group">
                            <label for="lugar_pruebas" class="form-label">Lugar de las pruebas</label>
                            <input type="text" id="lugar_pruebas" name="lugar_pruebas" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha" class="form-label">Fecha</label>
                            <input type="date" id="fecha" name="fecha" class="form-control" 
                                   value="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="plan_motivo" class="form-label">Plan o motivo</label>
                            <select id="plan_motivo" name="plan_motivo" class="form-control" required>
                                <option value="">Seleccionar</option>
                                <option value="Control Diario">Control Diario</option>
                                <option value="Aleatorio">Aleatorio</option>
                                <option value="Semanal">Semanal</option>
                                <option value="Mensual">Mensual</option>
                                <option value="Sospecha">Sospecha</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="hora_inicio" class="form-label">Hora de inicio de pruebas</label>
                            <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="hora_cierre" class="form-label">Hora de cierre de pruebas</label>
                            <input type="time" id="hora_cierre" name="hora_cierre" class="form-control" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cantidad_pruebas" class="form-label">Cantidad de pruebas realizadas</label>
                            <input type="number" id="cantidad_pruebas" name="cantidad_pruebas" 
                                   class="form-control" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cantidad_positivas" class="form-label">Cantidad de pruebas positivas</label>
                            <input type="number" id="cantidad_positivas" name="cantidad_positivas" 
                                   class="form-control" min="0" required>
                        </div>
                        
                        <div class="form-group">
                            <label for="cantidad_negativas" class="form-label">Cantidad de pruebas negativas</label>
                            <input type="number" id="cantidad_negativas" name="cantidad_negativas" 
                                   class="form-control" min="0" required>
                        </div>
                    </div>
                </div>

                <!-- OPERADOR O RESPONSABLE -->
                <div class="form-section">
                    <h4 class="section-title">OPERADOR O RESPONSABLE DE LA PRUEBA</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="operador_dni" class="form-label">DNI</label>
                            <!-- Operador DNI -->
							<input type="text" id="operador_dni" name="operador_dni" class="form-control" 
								   value="<?php echo htmlspecialchars($usuario['dni'] ?? '12345678'); ?>" readonly>

                        </div>
                        
                        <div class="form-group">
                            <label for="operador_nombre" class="form-label">Nombre y Apellido</label>
                            <!-- Operador Nombre -->
							<input type="text" id="operador_nombre" name="operador_nombre" class="form-control" 
								   value="<?php echo htmlspecialchars(($usuario['nombre'] ?? '') . ' ' . ($usuario['apellido'] ?? '')); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="operador_cargo" class="form-label">Cargo</label>
                            <!-- Operador Cargo -->
							<input type="text" id="operador_cargo" name="operador_cargo" class="form-control" 
								   value="<?php echo htmlspecialchars($usuario['rol'] ?? 'admin'); ?>" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Firma</label>
                            <div class="firma-container">
                                <div id="firma-pad-operador" class="firma-pad">
                                    <canvas id="firmaCanvasOperador" width="400" height="150" 
                                            style="border: 1px solid #ddd; border-radius: 4px;"></canvas>
                                    <div class="firma-actions">
                                        <button type="button" id="limpiarFirmaOperador" class="btn btn-outline btn-sm">
                                            <i class="fas fa-eraser"></i> Limpiar Firma
                                        </button>
                                    </div>
                                </div>
                                <input type="hidden" id="firmaOperador" name="firma_operador">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- DETALLES DE LA OPERACIÓN -->
                <div class="form-section">
                    <h4 class="section-title">DETALLES DE LA OPERACIÓN</h4>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="alcoholimetro_utilizado" class="form-label">Alcoholímetro Utilizado</label>
                            <select id="alcoholimetro_utilizado" name="alcoholimetro_utilizado" class="form-control" required>
								<option value="">Seleccionar alcoholímetro</option>
								<?php if (!empty($alcoholimetros)): ?>
									<?php foreach ($alcoholimetros as $alc): ?>
										<option value="<?php echo $alc['id']; ?>" 
												data-serie="<?php echo htmlspecialchars($alc['numero_serie']); ?>"
												data-marca="<?php echo htmlspecialchars($alc['marca'] ?? ''); ?>"
												data-modelo="<?php echo htmlspecialchars($alc['modelo'] ?? ''); ?>"
												data-calibracion="<?php echo htmlspecialchars($alc['fecha_calibracion'] ?? ''); ?>">
											<?php echo htmlspecialchars($alc['nombre_activo'] . ' (' . $alc['numero_serie'] . ')'); ?>
										</option>
									<?php endforeach; ?>
								<?php else: ?>
									<!-- Opciones por defecto -->
									<option value="1" data-serie="ALC-001" data-marca="AlcoTest" data-modelo="AL-3000" data-calibracion="2025-01-15">
										Alcoholímetro Principal (ALC-001)
									</option>
									<option value="2" data-serie="ALC-002" data-marca="AlcoTest" data-modelo="AL-2500" data-calibracion="2025-02-20">
										Alcoholímetro Secundario (ALC-002)
									</option>
								<?php endif; ?>
							</select>
                        </div>
                        
                        <div class="form-group">
                            <label for="serie" class="form-label">Serie</label>
                            <input type="text" id="serie" name="serie" class="form-control" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="estado_alcoholimetro" class="form-label">Estado</label>
                            <select id="estado_alcoholimetro" name="estado_alcoholimetro" class="form-control" required>
                                <option value="">Seleccionar estado</option>
                                <option value="Conforme">Conforme</option>
                                <option value="No Conforme">No Conforme</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label for="marca" class="form-label">Marca</label>
                            <input type="text" id="marca" name="marca" class="form-control" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label for="modelo" class="form-label">Modelo</label>
                            <input type="text" id="modelo" name="modelo" class="form-control" readonly>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Certificado de calibración</label>
                            <div>
                                <a href="#" id="ver_certificado" class="btn btn-outline btn-sm" target="_blank">
                                    <i class="fas fa-eye"></i> Ver Certificado
                                </a>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <label for="fecha_calibracion" class="form-label">Fecha de última calibración</label>
                            <input type="text" id="fecha_calibracion" name="fecha_calibracion" class="form-control" readonly>
                        </div>
                        
                        <div class="form-group full-width">
                            <label class="form-label">Condición Ambiental</label>
                            <div class="radio-group-horizontal">
                                <label class="radio-label">
                                    <input type="radio" name="condicion_ambiental" value="Ambiente Controlado" required> 
                                    <span class="radio-custom"></span>
                                    Ambiente Controlado
                                </label>
                                <label class="radio-label">
                                    <input type="radio" name="condicion_ambiental" value="Libre de alcohol en ambiente" required> 
                                    <span class="radio-custom"></span>
                                    Libre de alcohol en ambiente
                                </label>
                            </div>
                        </div>
                        
                        <div class="form-group full-width">
                            <label for="foto_alcoholimetro" class="form-label">Foto del alcoholímetro</label>
                            <input type="file" id="foto_alcoholimetro" name="foto_alcoholimetro" 
                                   class="form-control" accept="image/*">
                            <small class="form-text">Formatos permitidos: JPG, PNG, GIF (Máx. 5MB)</small>
                        </div>
                    </div>
                </div>

        </div>
    </div>
</div>


                <!-- TAB 2: LISTA DE CHEQUEO -->
                <div id="tab-chequeo" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-check-circle"></i> Lista de Chequeo</h3>
                        </div>
                        <div class="card-body">
                            <!-- CONTENIDO LISTA DE CHEQUEO -->
                        </div>
                    </div>
                </div>

                <!-- TAB 3: PRUEBAS INDIVIDUALES -->
                <div id="tab-pruebas" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-vial"></i> Pruebas Individuales</h3>
                        </div>
                        <div class="card-body">
                            <!-- CONTENIDO PRUEBAS INDIVIDUALES -->
                        </div>
                    </div>
                </div>

                <!-- TAB 4: ACTA DE CONSENTIMIENTO -->
                <div id="tab-consentimiento" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-file-signature"></i> Acta de Consentimiento (Para Casos Positivos)</h3>
                        </div>
                        <div class="card-body">
                            <!-- CONTENIDO ACTA DE CONSENTIMIENTO -->
                        </div>
                    </div>
                </div>

                <!-- TAB 5: ENCUESTA PRELIMINAR -->
                <div id="tab-encuesta" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-question-circle"></i> Encuesta Preliminar (Para Casos Positivos)</h3>
                        </div>
                        <div class="card-body">
                            <!-- CONTENIDO ENCUESTA PRELIMINAR -->
                        </div>
                    </div>
                </div>

                <!-- TAB 6: REGISTRO WIDMARK -->
                <div id="tab-widmark" class="tab-pane">
                    <div class="card">
                        <div class="card-header">
                            <h3><i class="fas fa-chart-line"></i> Registro Widmark (Para Casos Positivos)</h3>
                        </div>
                        <div class="card-body">
                            <!-- CONTENIDO REGISTRO WIDMARK -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- BOTÓN DE ENVÍO -->
            <div class="form-actions">
                <button type="button" class="btn btn-outline" onclick="window.history.back()">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Guardar Protocolo Completo
                </button>
            </div>
        </form>
    </div>
</div>

<style>
/* ESTILOS ESPECÍFICOS PARA PROTOCOLO COMPLETO CON TABS */

/* SISTEMA DE TABS */
.tabs-navigation {
    background: white;
    border-radius: 12px 12px 0 0;
    border: 1px solid var(--border);
    border-bottom: none;
    overflow: hidden;
}

.tabs-header {
    display: flex;
    overflow-x: auto;
    background: var(--light);
}

.tab-btn {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    padding: 1rem 1.5rem;
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    cursor: pointer;
    transition: var(--transition);
    white-space: nowrap;
    color: var(--gray);
    font-weight: 500;
}

.tab-btn:hover {
    background: rgba(132, 6, 31, 0.05);
    color: var(--primary);
}

.tab-btn.active {
    background: white;
    border-bottom-color: var(--primary);
    color: var(--primary);
    font-weight: 600;
}

.tab-btn i {
    font-size: 1rem;
}

.tabs-content {
    background: white;
    border: 1px solid var(--border);
    border-top: none;
    border-radius: 0 0 12px 12px;
    overflow: hidden;
}

.tab-pane {
    display: none;
    padding: 0;
}

.tab-pane.active {
    display: block;
}

/* BOTONES DE ACCIÓN */
.form-actions {
    display: flex;
    justify-content: flex-end;
    gap: 1rem;
    margin-top: 2rem;
    padding-top: 1.5rem;
    border-top: 1px solid var(--border);
}

/* RESPONSIVE */
@media (max-width: 1024px) {
    .tabs-header {
        flex-wrap: wrap;
    }
    
    .tab-btn {
        flex: 1;
        min-width: 120px;
        justify-content: center;
    }
}

@media (max-width: 768px) {
    .tabs-header {
        flex-direction: column;
    }
    
    .tab-btn {
        width: 100%;
        justify-content: flex-start;
    }
    
    .form-actions {
        flex-direction: column;
    }
}

/* ESTILOS ESPECÍFICOS PARA EL FORMULARIO DE OPERACIÓN */
.form-section {
    margin-bottom: 2rem;
    padding: 1.5rem;
    background: #f8f9fa;
    border-radius: 8px;
    border: 1px solid #e9ecef;
}

.section-title {
    color: var(--primary);
    margin-bottom: 1rem;
    padding-bottom: 0.5rem;
    border-bottom: 2px solid var(--primary);
    font-size: 1.1rem;
    font-weight: 600;
}

.form-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 1rem;
}

.form-group {
    display: flex;
    flex-direction: column;
}

.form-group.full-width {
    grid-column: 1 / -1;
}

.form-label {
    font-weight: 600;
    margin-bottom: 0.5rem;
    color: #333;
}

.form-control {
    padding: 0.75rem;
    border: 1px solid #ddd;
    border-radius: 4px;
    font-size: 0.9rem;
    transition: border-color 0.3s ease;
}

.form-control:focus {
    outline: none;
    border-color: var(--primary);
    box-shadow: 0 0 0 2px rgba(132, 6, 31, 0.1);
}

.form-control:read-only {
    background-color: #f8f9fa;
    color: #666;
    cursor: not-allowed;
}

/* FIRMA DIGITAL */
.firma-container {
    margin-top: 0.5rem;
}

.firma-pad {
    border: 1px solid #ddd;
    border-radius: 4px;
    padding: 1rem;
    background: white;
}

.firma-actions {
    margin-top: 0.5rem;
    text-align: center;
}

/* RADIO BUTTONS HORIZONTALES */
.radio-group-horizontal {
    display: flex;
    gap: 2rem;
    flex-wrap: wrap;
}

.radio-label {
    display: flex;
    align-items: center;
    gap: 0.5rem;
    cursor: pointer;
    padding: 0.5rem 0;
}

.radio-custom {
    width: 18px;
    height: 18px;
    border: 2px solid #ddd;
    border-radius: 50%;
    position: relative;
    transition: all 0.3s ease;
}

.radio-label input[type="radio"] {
    display: none;
}

.radio-label input[type="radio"]:checked + .radio-custom {
    border-color: var(--primary);
}

.radio-label input[type="radio"]:checked + .radio-custom::after {
    content: '';
    width: 10px;
    height: 10px;
    background: var(--primary);
    border-radius: 50%;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
}

/* RESPONSIVE */
@media (max-width: 768px) {
    .form-grid {
        grid-template-columns: 1fr;
    }
    
    .radio-group-horizontal {
        flex-direction: column;
        gap: 1rem;
    }
    
    .firma-pad canvas {
        width: 100% !important;
        height: 120px !important;
    }
}
</style>

<script>
// SISTEMA DE TABS
document.addEventListener('DOMContentLoaded', function() {
    const tabBtns = document.querySelectorAll('.tab-btn');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const targetTab = this.getAttribute('data-tab');
            
            // Remover clase active de todos los botones y paneles
            tabBtns.forEach(b => b.classList.remove('active'));
            tabPanes.forEach(p => p.classList.remove('active'));
            
            // Agregar clase active al botón y panel actual
            this.classList.add('active');
            document.getElementById(targetTab).classList.add('active');
        });
    });
});

// NAVEGACIÓN ENTRE TABS PROGRAMÁTICA
function irATab(tabId) {
    const tabBtn = document.querySelector(`[data-tab="${tabId}"]`);
    if (tabBtn) {
        tabBtn.click();
    }
}


// SCRIPT PARA EL TAB DE OPERACIÓN
document.addEventListener('DOMContentLoaded', function() {
    // Actualizar datos del alcoholímetro al seleccionar
    const alcoholimetroSelect = document.getElementById('alcoholimetro_utilizado');
    const serieInput = document.getElementById('serie');
    const marcaInput = document.getElementById('marca');
    const modeloInput = document.getElementById('modelo');
    const fechaCalibracionInput = document.getElementById('fecha_calibracion');
    
    alcoholimetroSelect.addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        if (selectedOption.value) {
            serieInput.value = selectedOption.getAttribute('data-serie') || '';
            marcaInput.value = selectedOption.getAttribute('data-marca') || '';
            modeloInput.value = selectedOption.getAttribute('data-modelo') || '';
            
            const fechaCalibracion = selectedOption.getAttribute('data-calibracion');
            if (fechaCalibracion) {
                const fecha = new Date(fechaCalibracion);
                fechaCalibracionInput.value = fecha.toLocaleDateString('es-ES');
            } else {
                fechaCalibracionInput.value = '';
            }
        } else {
            serieInput.value = '';
            marcaInput.value = '';
            modeloInput.value = '';
            fechaCalibracionInput.value = '';
        }
    });

    // FIRMA DIGITAL
    const canvas = document.getElementById('firmaCanvasOperador');
    const ctx = canvas.getContext('2d');
    const firmaInput = document.getElementById('firmaOperador');
    const limpiarBtn = document.getElementById('limpiarFirmaOperador');
    
    let isDrawing = false;
    let lastX = 0;
    let lastY = 0;
    
    // Configurar canvas
    ctx.strokeStyle = '#000';
    ctx.lineWidth = 2;
    ctx.lineJoin = 'round';
    ctx.lineCap = 'round';
    
    // Eventos del mouse
    canvas.addEventListener('mousedown', startDrawing);
    canvas.addEventListener('mousemove', draw);
    canvas.addEventListener('mouseup', stopDrawing);
    canvas.addEventListener('mouseout', stopDrawing);
    
    // Eventos táctiles
    canvas.addEventListener('touchstart', handleTouchStart);
    canvas.addEventListener('touchmove', handleTouchMove);
    canvas.addEventListener('touchend', stopDrawing);
    
    function startDrawing(e) {
        isDrawing = true;
        const pos = getMousePos(canvas, e);
        [lastX, lastY] = [pos.x, pos.y];
    }
    
    function draw(e) {
        if (!isDrawing) return;
        
        e.preventDefault();
        const pos = getMousePos(canvas, e);
        
        ctx.beginPath();
        ctx.moveTo(lastX, lastY);
        ctx.lineTo(pos.x, pos.y);
        ctx.stroke();
        
        [lastX, lastY] = [pos.x, pos.y];
        
        // Actualizar campo hidden con la firma
        firmaInput.value = canvas.toDataURL();
    }
    
    function stopDrawing() {
        isDrawing = false;
        firmaInput.value = canvas.toDataURL();
    }
    
    function getMousePos(canvas, evt) {
        const rect = canvas.getBoundingClientRect();
        let clientX, clientY;
        
        if (evt.type.includes('touch')) {
            clientX = evt.touches[0].clientX;
            clientY = evt.touches[0].clientY;
        } else {
            clientX = evt.clientX;
            clientY = evt.clientY;
        }
        
        return {
            x: clientX - rect.left,
            y: clientY - rect.top
        };
    }
    
    function handleTouchStart(e) {
        e.preventDefault();
        startDrawing(e.touches[0]);
    }
    
    function handleTouchMove(e) {
        e.preventDefault();
        draw(e.touches[0]);
    }
    
    limpiarBtn.addEventListener('click', function() {
        ctx.clearRect(0, 0, canvas.width, canvas.height);
        firmaInput.value = '';
    });
});
</script>

<?php
require_once __DIR__ . '/includes/footer.php';
?>