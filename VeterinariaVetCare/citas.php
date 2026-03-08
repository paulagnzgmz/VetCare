<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['rol'] == 'cliente') {
    header("Location: calendario.php");
    exit();
}

include "includes/config.php";

// Calcular iniciales
$nombre_completo = $_SESSION['nombre'];
$partes = explode(" ", $nombre_completo);
$iniciales = strtoupper(substr($partes[0], 0, 1));
if (isset($partes[1])) {
    $iniciales = $iniciales . strtoupper(substr($partes[1], 0, 1));
}

// Mensajes de sesión
$mensaje = $_SESSION['mensaje'] ?? "";
$error = $_SESSION['error'] ?? "";
unset($_SESSION['mensaje'], $_SESSION['error']);

// Procesar formulario para añadir cita
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'añadir') {

    $id_paciente = $_POST['id_paciente'];
    $id_usuario = $_POST['id_usuario'];
    $fecha = $_POST['fecha'];
    $hora = $_POST['hora'];
    $motivo = trim($_POST['motivo']);
    $estado = $_POST['estado'];
    $notas = trim($_POST['notas']);

    $fecha_hora = $fecha . ' ' . $hora . ':00';

    $sql_insert = "INSERT INTO citas (id_paciente, id_usuario, fecha_hora, motivo, estado, notas, activo) 
                   VALUES ($id_paciente, $id_usuario, '$fecha_hora', '$motivo', '$estado', " . ($notas ? "'$notas'" : "NULL") . ", 1)";

    if (mysqli_query($conexion, $sql_insert)) {
        $mensaje = "Cita añadida correctamente";
    } else {
        $error = "Error al añadir la cita: " . mysqli_error($conexion);
    }
}

// Obtener todas las citas activas
$sql = "SELECT c.*, 
               p.nombre AS mascota, p.especie,
               cl.nombre AS nombre_cliente, cl.apellidos AS apellidos_cliente,
               u.nombre_completo AS veterinario
        FROM citas c
        JOIN pacientes p ON c.id_paciente = p.id_paciente
        JOIN clientes cl ON p.id_cliente = cl.id_cliente
        JOIN usuarios u ON c.id_usuario = u.id_usuario
        WHERE c.activo = 1
        ORDER BY c.fecha_hora DESC";
$resultado = mysqli_query($conexion, $sql);

// Obtener lista de pacientes para el formulario
$sql_pacientes = "SELECT p.id_paciente, p.nombre, p.especie, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente
                  FROM pacientes p
                  JOIN clientes c ON p.id_cliente = c.id_cliente
                  WHERE p.activo = 1
                  ORDER BY c.apellidos, p.nombre";
$pacientes = mysqli_query($conexion, $sql_pacientes);

// Obtener lista de veterinarios
$sql_veterinarios = "SELECT id_usuario, nombre_completo 
                     FROM usuarios 
                     WHERE rol = 'veterinario' 
                     ORDER BY nombre_completo";
$veterinarios = mysqli_query($conexion, $sql_veterinarios);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Gestión de Citas - Clinica Veterinaria</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css"
        rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
    <!-- AÑADIDO -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@sweetalert2/theme-bootstrap-4/bootstrap-4.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="estilos.css?v=556">
</head>

<body>

    <aside class="menu-lateral">
        <div class="menu-logo">
            <img src="img/logo.png" alt="Logo VetCare" class="logo-img">
            Vet<span>Care</span>
        </div>
        <nav class="menu-nav">
            <a href="calendario.php" class="menu-enlace">Calendario</a>
            <a href="historial.php" class="menu-enlace">Historial clínico</a>
            <?php if ($_SESSION['rol'] != 'cliente') { ?>
                <a href="citas.php" class="menu-enlace activo">Gestión de citas</a>
                <a href="pacientes.php" class="menu-enlace">Pacientes</a>
                <a href="clientes.php" class="menu-enlace">Clientes</a>
                <?php if ($_SESSION['rol'] == 'admin') { ?>
                    <a href="admin.php" class="menu-enlace">Administración</a>
                    <a href="api_citas.php" class="menu-enlace">API Citas</a>
                <?php } ?>
            <?php } ?>
        </nav>
        <div class="menu-pie">
            <div class="usuario-activo">
                <div class="usuario-iniciales"><?php echo $iniciales; ?></div>
                <div class="usuario-datos">
                    <span class="usuario-nombre"><?php echo $_SESSION['nombre']; ?></span>
                    <span class="usuario-rol"><?php echo $_SESSION['rol']; ?></span>
                </div>
            </div>
        </div>
    </aside>

    <div class="contenedor-principal">
        <main class="contenido">
            <?php if (!empty($_SESSION['aviso_licencia'])) { ?>
                <div class="mensaje-sesion error">
                    ⚠️ <?php echo $_SESSION['aviso_licencia']; ?>
                </div>
            <?php } ?>
            <div class="cabecera-pagina">
                <div>
                    <h1 class="titulo-pagina">Gestión de citas</h1>
                    <p class="subtitulo-pagina">Programar y administrar citas de la clínica</p>
                </div>
            </div>

            <?php if ($mensaje != "") { ?>
                <div class="mensaje-sesion exito"><?php echo $mensaje; ?></div>
            <?php } ?>

            <?php if ($error != "") { ?>
                <div class="mensaje-sesion error"><?php echo $error; ?></div>
            <?php } ?>

            <div class="tarjeta tarjeta-form">
                <div class="tarjeta-cuerpo">
                    <h3 class="form-seccion-titulo">Programar nueva cita</h3>

                    <form method="POST" action="citas.php" autocomplete="off">
                        <input type="hidden" name="accion" value="añadir">

                        <div class="form-grid-3">

                            <div>
                                <label class="form-label">Mascota</label>
                                <select name="id_paciente" id="select-mascota" required class="form-input">
                                    <option value="">Seleccionar...</option>
                                    <?php
                                    mysqli_data_seek($pacientes, 0);
                                    while ($paciente = mysqli_fetch_assoc($pacientes)) {
                                        ?>
                                        <option value="<?php echo $paciente['id_paciente']; ?>">
                                            <?php echo $paciente['nombre'] . ' (' . $paciente['especie'] . ') - ' . $paciente['nombre_cliente'] . ' ' . $paciente['apellidos_cliente']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Veterinario</label>
                                <select name="id_usuario" required class="form-input">
                                    <option value="">Seleccionar...</option>
                                    <?php
                                    mysqli_data_seek($veterinarios, 0);
                                    while ($vet = mysqli_fetch_assoc($veterinarios)) {
                                        ?>
                                        <option value="<?php echo $vet['id_usuario']; ?>">
                                            <?php echo $vet['nombre_completo']; ?>
                                        </option>
                                    <?php } ?>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Estado</label>
                                <select name="estado" required class="form-input">
                                    <option value="pendiente">Pendiente</option>
                                    <option value="confirmada" selected>Confirmada</option>
                                    <option value="completada">Completada</option>
                                </select>
                            </div>

                            <div>
                                <label class="form-label">Fecha</label>
                                <input type="date" name="fecha" required class="form-input">
                            </div>

                            <div>
                                <label class="form-label">Hora</label>
                                <input type="time" name="hora" required class="form-input">
                            </div>

                            <div>
                                <label class="form-label">Motivo</label>
                                <input type="text" name="motivo" required placeholder="Ej: Vacunación"
                                    class="form-input">
                            </div>

                            <div class="form-campo-span3">
                                <label class="form-label">Notas (opcional)</label>
                                <textarea name="notas" rows="2" class="form-input"></textarea>
                            </div>

                        </div>

                        <div class="form-submit">
                            <button type="submit" class="btn-submit">
                                Programar cita
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="tarjeta">
                <div class="tarjeta-cuerpo">
                    <table id="tablaCitas" class="tabla-wrapper">
                        <thead>
                            <tr>
                                <th>Fecha/Hora</th>
                                <th>Mascota</th>
                                <th>Cliente</th>
                                <th>Veterinario</th>
                                <th>Motivo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            mysqli_data_seek($resultado, 0);
                            while ($fila = mysqli_fetch_assoc($resultado)) {
                                ?>
                                <tr>
                                    <td data-order="<?php echo $fila['fecha_hora']; ?>">
                                        <?php echo date('d/m/Y H:i', strtotime($fila['fecha_hora'])); ?>
                                    </td>
                                    <td><?php echo $fila['mascota'] . ' (' . $fila['especie'] . ')'; ?></td>
                                    <td><?php echo $fila['nombre_cliente'] . ' ' . $fila['apellidos_cliente']; ?></td>
                                    <td><?php echo $fila['veterinario']; ?></td>
                                    <td><?php echo $fila['motivo']; ?></td>
                                    <td>
                                        <span class="etiqueta etiqueta-<?php echo $fila['estado']; ?>">
                                            <?php echo ucfirst($fila['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="acciones-columna">
                                            <?php if (in_array($fila['estado'], ['pendiente', 'confirmada'])) { ?>
                                                <?php if ($_SESSION['rol'] == 'veterinario') { ?>
                                                    <button class="btn-accion btn-completar"
                                                        onclick='abrirModalCompletar(<?php echo json_encode($fila); ?>)'>Completar</button>
                                                <?php } else { ?>
                                                    <div class="btn-placeholder"></div>
                                                <?php } ?>
                                            <?php } else { ?>
                                                <div class="btn-placeholder"></div>
                                            <?php } ?>
                                            <button class="btn-accion btn-editar"
                                                onclick='editarCita(<?php echo json_encode($fila); ?>)'>Editar</button>
                                            <!--CAMBIADO-->
                                            <form method="POST" action="procesar_cita.php"
                                                id="form-eliminar-<?php echo $fila['id_cita']; ?>" style="display:inline;">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id_cita" value="<?php echo $fila['id_cita']; ?>">
                                                <button type="button" class="btn-accion btn-eliminar"
                                                    onclick="confirmarEliminar(<?php echo $fila['id_cita']; ?>)">
                                                    Eliminar
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="cerrar-sesion">
                <a href="logout.php">Cerrar sesión</a>
            </p>

        </main>
        <footer class="pie-pagina">
            <p>&copy; 2026 VetCare Clínica Veterinaria — Laredo, Cantabria</p>
        </footer>
    </div>

    <!-- Modal Editar Cita -->
    <div id="modalEditarCita" class="modal">
        <div class="modal-contenido">
            <div class="modal-cabecera">
                <h3 class="modal-titulo">Editar Cita</h3>
                <button class="modal-cerrar" onclick="cerrarModal()">&times;</button>
            </div>
            <form method="POST" action="procesar_cita.php">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_cita" id="edit_id_cita">

                <div class="modal-cuerpo">
                    <div class="form-grid">
                        <div class="form-campo">
                            <label>Mascota</label>
                            <select name="id_paciente" id="edit_id_paciente" required>
                                <?php
                                mysqli_data_seek($pacientes, 0);
                                while ($p = mysqli_fetch_assoc($pacientes)) {
                                    echo "<option value='{$p['id_paciente']}'>{$p['nombre']} ({$p['especie']}) - {$p['nombre_cliente']} {$p['apellidos_cliente']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-campo">
                            <label>Veterinario</label>
                            <select name="id_usuario" id="edit_id_usuario" required>
                                <?php
                                mysqli_data_seek($veterinarios, 0);
                                while ($v = mysqli_fetch_assoc($veterinarios)) {
                                    echo "<option value='{$v['id_usuario']}'>{$v['nombre_completo']}</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div class="form-campo">
                            <label>Fecha</label>
                            <input type="date" name="fecha" id="edit_fecha" required>
                        </div>

                        <div class="form-campo">
                            <label>Hora</label>
                            <input type="time" name="hora" id="edit_hora" required>
                        </div>

                        <div class="form-campo">
                            <label>Motivo</label>
                            <input type="text" name="motivo" id="edit_motivo" required>
                        </div>

                        <div class="form-campo">
                            <label>Estado</label>
                            <select name="estado" id="edit_estado" required>
                                <option value="pendiente">Pendiente</option>
                                <option value="confirmada">Confirmada</option>
                                <option value="completada">Completada</option>
                                <option value="cancelada">Cancelada</option>
                            </select>
                        </div>

                        <div class="form-campo form-campo-completo">
                            <label>Notas</label>
                            <textarea name="notas" id="edit_notas" rows="3"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-pie">
                    <button type="button" class="btn-accion btn-cancelar" onclick="cerrarModal()">Cancelar</button>
                    <button type="submit" class="btn-accion btn-guardar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Completar Cita -->
    <div id="modalCompletarCita" class="modal">
        <div class="modal-contenido">
            <div class="modal-cabecera">
                <h3 class="modal-titulo">Completar Cita</h3>
                <button class="modal-cerrar" onclick="cerrarModalCompletar()">&times;</button>
            </div>
            <form method="POST" action="procesar_cita.php">
                <input type="hidden" name="accion" value="completar">
                <input type="hidden" name="id_cita" id="completar_id_cita">
                <div class="modal-cuerpo">
                    <p id="completar_info" class="completar-info"></p>
                    <div class="form-grid">
                        <div class="form-campo form-campo-completo">
                            <label>Diagnóstico</label>
                            <textarea name="diagnostico" id="completar_diagnostico" rows="3"
                                placeholder="Describe el estado del animal..."></textarea>
                        </div>
                        <div class="form-campo form-campo-completo">
                            <label>Tratamiento <span class="form-label-hint">(opcional)</span></label>
                            <textarea name="tratamiento" id="completar_tratamiento" rows="3"
                                placeholder="Medicación, instrucciones..."></textarea>
                        </div>
                        <div class="form-campo">
                            <label>Peso (kg) <span class="form-label-hint">(opcional)</span></label>
                            <input type="number" name="peso" id="completar_peso" step="0.01" min="0"
                                placeholder="Ej: 28.5">
                        </div>
                    </div>
                </div>
                <div class="modal-pie">
                    <button type="button" class="btn-accion btn-cancelar"
                        onclick="cerrarModalCompletar()">Cancelar</button>
                    <button type="submit" class="btn-accion btn-guardar">Guardar y completar</button>
                </div>
            </form>
        </div>
    </div>
    <!--AÑADIDO-->
    <script>
        function confirmarEliminar(idCita) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "La cita se marcará como inactiva en el sistema.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74c3c',
                cancelButtonColor: '#95a5a6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                reverseButtons: true,
                focusCancel: true,
                borderRadius: '12px'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form-eliminar-' + idCita).submit();
                }
            });
        }
    </script>

    <script>
        $(document).ready(function () {
            $("#tablaCitas").DataTable({
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ citas",
                    infoEmpty: "Sin registros",
                    zeroRecords: "No se encontraron resultados",
                    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
                },
                pageLength: 10,
                order: [[0, "desc"]]
            });

            $('#select-mascota').select2({
                placeholder: "Buscar mascota...",
                allowClear: true,
                width: '100%'
            });
        });
    </script>
    <script src="script.js"></script>

</body>

</html>