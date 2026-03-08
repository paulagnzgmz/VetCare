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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && $_SESSION['rol'] == 'recepcionista' && isset($_POST['accion']) && $_POST['accion'] == 'añadir') {
    $nombre = trim($_POST['nombre']);
    $apellidos = trim($_POST['apellidos']);
    $telefono = trim($_POST['telefono']);
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    $sql_check = "SELECT * FROM usuarios WHERE email = '$email'";
    $res_check = mysqli_query($conexion, $sql_check);

    if (mysqli_num_rows($res_check) > 0) {
        $error = "Ya existe un usuario con ese email";
    } else {
        $sql_usuario = "INSERT INTO usuarios (nombre_completo, email, password, rol) 
                        VALUES ('$nombre $apellidos', '$email', '" . md5($password) . "', 'cliente')";

        if (mysqli_query($conexion, $sql_usuario)) {
            $id_usuario = mysqli_insert_id($conexion);

            $sql_cliente = "INSERT INTO clientes (id_usuario, nombre, apellidos, telefono, email, activo) 
                            VALUES ($id_usuario, '$nombre', '$apellidos', '$telefono', '$email', 1)";

            if (mysqli_query($conexion, $sql_cliente)) {
                $mensaje = "Cliente añadido correctamente";
            } else {
                $error = "Error al crear cliente: " . mysqli_error($conexion);
            }
        } else {
            $error = "Error al crear usuario: " . mysqli_error($conexion);
        }
    }
}

$sql = "SELECT c.*, u.email 
        FROM clientes c
        JOIN usuarios u ON c.id_usuario = u.id_usuario
        WHERE c.activo = 1
        ORDER BY c.apellidos, c.nombre";
$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Clientes - Clinica Veterinaria</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/css/jquery.dataTables.min.css"
        rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/datatables/1.10.21/js/jquery.dataTables.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="estilos.css?v=560">
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
                <a href="pacientes.php" class="menu-enlace">Pacientes</a>
                <a href="clientes.php" class="menu-enlace activo">Clientes</a>
            <?php } ?>
            <?php if ($_SESSION['rol'] == 'admin') { ?>
                <a href="admin.php" class="menu-enlace">Administración</a>
                <a href="api_citas.php" class="menu-enlace">API Citas</a>
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
                    <h1 class="titulo-pagina">Clientes</h1>
                    <p class="subtitulo-pagina">Gestión de clientes y propietarios</p>
                </div>
            </div>

            <?php if ($mensaje != "") { ?>
                <div class="mensaje-sesion exito"><?php echo $mensaje; ?></div>
            <?php } ?>

            <?php if ($error != "") { ?>
                <div class="mensaje-sesion error"><?php echo $error; ?></div>
            <?php } ?>

            <?php if ($_SESSION['rol'] == 'recepcionista') { ?>
                <div class="tarjeta tarjeta-form">
                    <div class="tarjeta-cuerpo">
                        <h3 class="form-seccion-titulo">Añadir nuevo cliente</h3>

                        <form method="POST" action="clientes.php" autocomplete="off">
                            <input type="hidden" name="accion" value="añadir">

                            <div class="form-grid-2">

                                <div>
                                    <label class="form-label">Nombre</label>
                                    <input type="text" name="nombre" required class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Apellidos</label>
                                    <input type="text" name="apellidos" required class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Teléfono</label>
                                    <input type="text" name="telefono" class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Email</label>
                                    <input type="email" name="email" required class="form-input">
                                </div>

                                <div>
                                    <label class="form-label">Contraseña</label>
                                    <input type="password" name="password" required class="form-input">
                                </div>

                            </div>

                            <div class="form-submit">
                                <button type="submit" class="btn-submit">
                                    Añadir cliente
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            <?php } ?>

            <div class="tarjeta">
                <div class="tarjeta-cuerpo">
                    <table id="tablaClientes" class="tabla-wrapper">
                        <thead>
                            <tr>
                                <th>Nombre</th>
                                <th>Apellidos</th>
                                <th>Teléfono</th>
                                <th>Email</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
                                <tr>
                                    <td><?php echo $fila['nombre']; ?></td>
                                    <td><?php echo $fila['apellidos']; ?></td>
                                    <td><?php echo $fila['telefono']; ?></td>
                                    <td><?php echo $fila['email']; ?></td>
                                    <td>
                                        <div class="acciones-columna">
                                            <a href="cliente_detalle.php?id=<?php echo $fila['id_cliente']; ?>"
                                                class="btn-accion-enlace">Ver Ficha</a>
                                            <button class="btn-accion btn-editar"
                                                onclick='editarCliente(<?php echo json_encode($fila); ?>)'>Editar</button>
                                            <form method="POST" class="form-accion-fila" action="procesar_cliente.php"
                                                onsubmit="return confirm('¿Eliminar este cliente?')">
                                                <input type="hidden" name="accion" value="eliminar">
                                                <input type="hidden" name="id_cliente"
                                                    value="<?php echo $fila['id_cliente']; ?>">
                                                <button type="submit" class="btn-accion btn-eliminar">Eliminar</button>
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

    <!-- Modal Editar Cliente -->
    <div id="modalEditarCliente" class="modal">
        <div class="modal-contenido">
            <div class="modal-cabecera">
                <h3 class="modal-titulo">Editar Cliente</h3>
                <button class="modal-cerrar" onclick="cerrarModalCliente()">&times;</button>
            </div>
            <form method="POST" action="procesar_cliente.php">
                <input type="hidden" name="accion" value="editar">
                <input type="hidden" name="id_cliente" id="edit_id_cliente">

                <div class="modal-cuerpo">
                    <div class="form-grid">
                        <div class="form-campo">
                            <label>Nombre</label>
                            <input type="text" name="nombre" id="edit_nombre" required>
                        </div>

                        <div class="form-campo">
                            <label>Apellidos</label>
                            <input type="text" name="apellidos" id="edit_apellidos" required>
                        </div>

                        <div class="form-campo">
                            <label>Teléfono</label>
                            <input type="text" name="telefono" id="edit_telefono">
                        </div>

                        <div class="form-campo">
                            <label>Email</label>
                            <input type="email" name="email" id="edit_email" required>
                        </div>

                        <div class="form-campo form-campo-completo">
                            <label>Dirección</label>
                            <textarea name="direccion" id="edit_direccion" rows="2"></textarea>
                        </div>
                    </div>
                </div>

                <div class="modal-pie">
                    <button type="button" class="btn-accion btn-cancelar"
                        onclick="cerrarModalCliente()">Cancelar</button>
                    <button type="submit" class="btn-accion btn-guardar">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $("#tablaClientes").DataTable({
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ clientes",
                    infoEmpty: "Sin registros",
                    zeroRecords: "No se encontraron resultados",
                    paginate: { first: "Primero", last: "Último", next: "Siguiente", previous: "Anterior" }
                },
                pageLength: 10,
                order: [[1, "asc"]]
            });
        });
    </script>
    <script src="script.js"></script>

</body>

</html>