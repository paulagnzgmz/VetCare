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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['rol'] == 'veterinario' && isset($_POST['accion']) && $_POST['accion'] == 'añadir') {

    $id_cliente = $_POST['id_cliente'];
    $nombre = trim($_POST['nombre']);
    $especie = trim($_POST['especie']);
    $raza = trim($_POST['raza']);
    $fecha_nac = $_POST['fecha_nac'];
    $sexo = $_POST['sexo'];
    $foto = NULL;

    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidas = array('jpg', 'jpeg', 'png', 'gif');

        if (in_array($extension, $permitidas)) {
            $nombre_archivo = uniqid() . '.' . $extension;
            $ruta_destino = 'uploads/mascotas/' . $nombre_archivo;

            if (!is_dir('uploads/mascotas')) {
                mkdir('uploads/mascotas', 0777, true);
            }

            if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                $foto = $ruta_destino;
            } else {
                $error = "Error al subir la foto";
            }
        } else {
            $error = "Solo se permiten imágenes (JPG, PNG, GIF)";
        }
    }

    if ($error == "") {
        $sql_insert = "INSERT INTO pacientes (id_cliente, nombre, especie, raza, fecha_nac, sexo, foto, activo) 
                       VALUES ($id_cliente, '$nombre', '$especie', " . ($raza ? "'$raza'" : "NULL") . ", " .
            ($fecha_nac ? "'$fecha_nac'" : "NULL") . ", " . ($sexo ? "'$sexo'" : "NULL") . ", " .
            ($foto ? "'$foto'" : "NULL") . ", 1)";

        if (mysqli_query($conexion, $sql_insert)) {
            $mensaje = "Mascota añadida correctamente";
        } else {
            $error = "Error al añadir la mascota: " . mysqli_error($conexion);
        }
    }
}

$sql = "SELECT p.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente
        FROM pacientes p
        JOIN clientes c ON p.id_cliente = c.id_cliente
        WHERE p.activo = 1
        ORDER BY c.apellidos, p.nombre";
$resultado = mysqli_query($conexion, $sql);

$sql_clientes = "SELECT id_cliente, nombre, apellidos FROM clientes WHERE activo = 1 ORDER BY apellidos, nombre";
$clientes = mysqli_query($conexion, $sql_clientes);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pacientes - Clinica Veterinaria</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css"
        rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="estilos.css?v=500">
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
                <a href="citas.php" class="menu-enlace">Gestión de citas</a>
                <a href="pacientes.php" class="menu-enlace activo">Pacientes</a>
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
                    <h1 class="titulo-pagina">Pacientes</h1>
                    <p class="subtitulo-pagina">Gestión de mascotas registradas</p>
                </div>
            </div>

            <?php if ($mensaje != "") { ?>
                <div class="mensaje-sesion exito"><?php echo $mensaje; ?></div>
            <?php } ?>

            <?php if ($error != "") { ?>
                <div class="mensaje-sesion error"><?php echo $error; ?></div>
            <?php } ?>

            <?php if ($_SESSION['rol'] == 'veterinario') { ?>
                <div class="tarjeta tarjeta-form">
                    <div class="tarjeta-cuerpo">
                        <h3 class="form-seccion-titulo">Añadir nueva mascota</h3>

                        <form method="POST" action="pacientes.php" autocomplete="off" enctype="multipart/form-data">
                            <input type="hidden" name="accion" value="añadir">

                            <div class="form-grid-4">

                                <div>
                                    <label class="form-label">Cliente</label>
                                    <select name="id_cliente" required class="form-input">
                                        <option value="">Seleccionar...</option>
                                        <?php
                                        mysqli_data_seek($clientes, 0);
                                        while ($cliente = mysqli_fetch_assoc($clientes)) {
                                            ?>
                                            <option value="<?php echo $cliente['id_cliente']; ?>">
                                                <?php echo $cliente['apellidos'] . ', ' . $cliente['nombre']; ?>
                                            </option>
                                        <?php } ?>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="nombre" required class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Especie</label>
                                    <select name="especie" required class="form-input">
                                        <option value="">Seleccionar...</option>
                                        <option value="Perro">Perro</option>
                                        <option value="Gato">Gato</option>
                                        <option value="Conejo">Conejo</option>
                                        <option value="Hamster">Hamster</option>
                                        <option value="Ave">Ave</option>
                                        <option value="Reptil">Reptil</option>
                                        <option value="Otro">Otro</option>
                                    </select>
                                </div>

                                <div>
                                    <label class="form-label">Raza</label>
                                    <input type="text" name="raza" class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Fecha nacimiento</label>
                                    <input type="date" name="fecha_nac" class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Sexo</label>
                                    <select name="sexo" class="form-input">
                                        <option value="">Seleccionar...</option>
                                        <option value="macho">Macho</option>
                                        <option value="hembra">Hembra</option>
                                    </select>
                                </div>

                                <div class="form-campo-span2">
                                    <label class="form-label">Foto (opcional)</label>
                                    <input type="file" name="foto" accept="image/*" class="form-input">
                                </div>

                            </div>

                            <div class="form-submit">
                                <button type="submit" class="btn-submit">
                                    Añadir mascota
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>

            <div class="tarjeta">
                <div class="tarjeta-cuerpo">
                    <table id="tablaPacientes" class="tabla-wrapper">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Especie</th>
                                <th>Raza</th>
                                <th>Propietario</th>
                                <th>Sexo</th>
                                <th>Fecha Nac.</th>
                                <?php if ($_SESSION['rol'] == 'veterinario') { ?>
                                    <th>Acciones</th>
                                <?php } ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
                                <tr>
                                    <td>
                                        <a href="paciente_detalle.php?id=<?php echo $fila['id_paciente']; ?>"
                                            class="enlace-tabla">
                                            <?php echo $fila['nombre']; ?>
                                        </a>
                                    </td>
                                    <td><?php echo $fila['especie']; ?></td>
                                    <td><?php echo $fila['raza'] ? $fila['raza'] : '-'; ?></td>
                                    <td><?php echo $fila['nombre_cliente'] . ' ' . $fila['apellidos_cliente']; ?></td>
                                    <td><?php echo $fila['sexo'] ? ucfirst($fila['sexo']) : '-'; ?></td>
                                    <td><?php echo $fila['fecha_nac'] ? date('d/m/Y', strtotime($fila['fecha_nac'])) : '-'; ?>
                                    </td>
                                    <?php if ($_SESSION['rol'] == 'veterinario') { ?>
                                        <td>
                                            <div class="acciones-columna">
                                                <button class="btn-accion btn-editar"
                                                    onclick='editarPaciente(<?php echo json_encode($fila); ?>)'>Editar</button>
                                                <form method="POST" class="form-accion-fila" action="procesar_paciente.php"
                                                    onsubmit="return confirm('¿Eliminar esta mascota?')">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id_paciente"
                                                        value="<?php echo $fila['id_paciente']; ?>">
                                                    <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
                                                </form>
                                            </div>
                                        </td>
                                    <?php } ?>
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

    <!-- Modal Editar Paciente -->
    <div id="modalEditarPaciente" class="modal">
        <div class="modal-contenido">
            <div class="modal-cabecera">
                <h3 class="modal-titulo">Editar Mascota</h3>
                <button class="modal-cerrar" onclick="cerrarModalPaciente()">&times;</button>
            </div>
            <form method="POST" action="procesar_paciente.php" enctype="multipart/form-data">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_paciente" id="edit_id_paciente">
                <input type="hidden" name="foto_actual" id="edit_foto_actual">

                <div class="modal-cuerpo">
                    <div class="form-grid">
                        <div class="form-campo">
                            <label>Nombre</label>
                            <input type="text" name="nombre" id="edit_nombre_pac" required>
                        </div>

                        <div class="form-campo">
                            <label>Especie</label>
                            <select name="especie" id="edit_especie" required>
                                <option value="Perro">Perro</option>
                                <option value="Gato">Gato</option>
                                <option value="Conejo">Conejo</option>
                                <option value="Hamster">Hamster</option>
                                <option value="Ave">Ave</option>
                                <option value="Reptil">Reptil</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                        <div class="form-campo">
                            <label>Raza</label>
                            <input type="text" name="raza" id="edit_raza">
                        </div>

                        <div class="form-campo">
                            <label>Sexo</label>
                            <select name="sexo" id="edit_sexo">
                                <option value="">Seleccionar...</option>
                                <option value="macho">Macho</option>
                                <option value="hembra">Hembra</option>
                            </select>
                        </div>

                        <div class="form-campo">
                            <label>Fecha nacimiento</label>
                            <input type="date" name="fecha_nac" id="edit_fecha_nac">
                        </div>

                        <div class="form-campo">
                            <label>Nueva foto (opcional)</label>
                            <input type="file" name="foto" accept="image/*">
                        </div>
                    </div>
                </div>

                <div class="modal-pie">
                    <button type="button" class="btn-accion btn-cancelar"
                        onclick="cerrarModalPaciente()">Cancelar</button>
                    <button type="submit" class="btn-accion btn-guardar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $("#tablaPacientes").DataTable({
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ mascotas",
                    infoEmpty: "Sin registros",
                    zeroRecords: "No se encontraron resultados",
                    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
                },
                pageLength: 10,
                order: [[3, "asc"]]
            });
        });
    </script>
    <script src="script.js"></script>

</body>

</html>