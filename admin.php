<?php
// =============================================
// admin.php — Panel de administración
// Solo accesible para el rol 'admin'
// Permite gestionar usuarios del sistema:
// veterinarios, recepcionistas y otros admins
// =============================================

session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['rol'] != 'admin') {
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

// ─── Añadir usuario ───────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'añadir') {
    $nombre = trim($_POST['nombre']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $rol = $_POST['rol'];

    // Roles permitidos para crear desde admin
    $roles_permitidos = ['veterinario', 'recepcionista', 'admin'];
    if (!in_array($rol, $roles_permitidos)) {
        $error = "Rol no válido";
    } else {
        $sql_check = "SELECT id_usuario FROM usuarios WHERE email = '$email'";
        $res_check = mysqli_query($conexion, $sql_check);

        if (mysqli_num_rows($res_check) > 0) {
            $error = "Ya existe un usuario con ese email";
        } else {
            $md5 = md5($password);
            $sql = "INSERT INTO usuarios (nombre_completo, email, password, rol)
                    VALUES ('$nombre', '$email', '$md5', '$rol')";
            if (mysqli_query($conexion, $sql)) {
                $mensaje = "Usuario añadido correctamente";
            } else {
                $error = "Error al añadir el usuario: " . mysqli_error($conexion);
            }
        }
    }
}

// Obtener todos los usuarios que NO son clientes
$sql = "SELECT * FROM usuarios WHERE rol != 'cliente' ORDER BY rol, nombre_completo";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Administración - Clinica Veterinaria</title>
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
            <a href="citas.php" class="menu-enlace">Gestión de citas</a>
            <a href="pacientes.php" class="menu-enlace">Pacientes</a>
            <a href="clientes.php" class="menu-enlace">Clientes</a>
            <a href="admin.php" class="menu-enlace activo">Administración</a>
            <a href="api_citas.php" class="menu-enlace">API Citas</a>

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

            <div class="cabecera-pagina">
                <div>
                    <h1 class="titulo-pagina">Administración</h1>
                    <p class="subtitulo-pagina">Gestión de usuarios del sistema</p>
                </div>
            </div>

            <?php if ($mensaje != "") { ?>
                <div class="mensaje-sesion exito"><?php echo $mensaje; ?></div>
            <?php } ?>
            <?php if ($error != "") { ?>
                <div class="mensaje-sesion error"><?php echo $error; ?></div>
            <?php } ?>

            <!-- Formulario añadir usuario -->
            <div class="tarjeta tarjeta-form">
                <div class="tarjeta-cuerpo">
                    <h3 class="form-seccion-titulo">Añadir nuevo usuario</h3>

                    <form method="POST" action="admin.php" autocomplete="off">
                        <input type="hidden" name="accion" value="añadir">

                        <div class="form-grid-4">

                            <div>
                                <label class="form-label">Nombre completo</label>
                                <input type="text" name="nombre" required class="form-input">
                            </div>

                            <div>
                                <label class="form-label">Email</label>
                                <input type="email" name="email" required class="form-input">
                            </div>

                            <div>
                                <label class="form-label">Contraseña</label>
                                <input type="password" name="password" required class="form-input">
                            </div>

                            <div>
                                <label class="form-label">Rol</label>
                                <select name="rol" required class="form-input">
                                    <option value="">Seleccionar...</option>
                                    <option value="veterinario">Veterinario</option>
                                    <option value="recepcionista">Recepcionista</option>
                                    <option value="admin">Administrador</option>
                                </select>
                            </div>

                        </div>

                        <div class="form-submit">
                            <button type="submit" class="btn-submit">
                                Crear usuario
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Tabla de usuarios -->
            <div class="tarjeta">
                <div class="tarjeta-cuerpo">
                    <table id="tablaUsuarios" class="tabla-wrapper">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
                                <tr>
                                    <td><?php echo $fila['nombre_completo']; ?></td>
                                    <td><?php echo $fila['email']; ?></td>
                                    <td>
                                        <span class="etiqueta etiqueta-<?php echo $fila['rol']; ?>">
                                            <?php echo ucfirst($fila['rol']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="acciones-columna">
                                            <?php if ($fila['id_usuario'] != $_SESSION['id_usuario']) { ?>
                                                <button class="btn-accion btn-editar"
                                                    onclick='editarUsuario(<?php echo json_encode($fila); ?>)'>Editar</button>
                                                <form method="POST" class="form-accion-fila" action="procesar_admin.php"
                                                    onsubmit="return confirm('¿Eliminar este usuario? Esta acción no se puede deshacer.')">
                                                    <input type="hidden" name="accion" value="eliminar">
                                                    <input type="hidden" name="id_usuario"
                                                        value="<?php echo $fila['id_usuario']; ?>">
                                                    <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
                                                </form>
                                            <?php } else { ?>
                                                <span class="texto-usuario-actual">Usuario actual</span>
                                            <?php } ?>
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

    <!-- Modal Editar Usuario -->
    <div id="modalEditarUsuario" class="modal">
        <div class="modal-contenido">
            <div class="modal-cabecera">
                <h3 class="modal-titulo">Editar Usuario</h3>
                <button class="modal-cerrar" onclick="cerrarModalUsuario()">&times;</button>
            </div>
            <form method="POST" action="procesar_admin.php">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_usuario" id="edit_id_usuario">

                <div class="modal-cuerpo">
                    <div class="form-grid">

                        <div class="form-campo form-campo-completo">
                            <label>Nombre completo</label>
                            <input type="text" name="nombre" id="edit_nombre" required>
                        </div>

                        <div class="form-campo">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" required>
                        </div>

                        <div class="form-campo">
                            <label>Rol</label>
                            <select name="rol" id="edit_rol" required>
                                <option value="veterinario">Veterinario</option>
                                <option value="recepcionista">Recepcionista</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>

                        <div class="form-campo form-campo-completo">
                            <label>Nueva contraseña <span class="form-label-hint">(dejar vacío para no
                                    cambiarla)</span></label>
                            <input type="password" name="password" id="edit_password">
                        </div>

                    </div>
                </div>

                <div class="modal-pie">
                    <button type="button" class="btn-accion btn-cancelar"
                        onclick="cerrarModalUsuario()">Cancelar</button>
                    <button type="submit" class="btn-accion btn-guardar">Guardar cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $("#tablaUsuarios").DataTable({
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ usuarios",
                    infoEmpty: "Sin registros",
                    zeroRecords: "No se encontraron resultados",
                    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
                },
                pageLength: 10,
                order: [[2, "asc"], [0, "asc"]]
            });
        });
    </script>
    <script src="script.js"></script>

</body>

</html>