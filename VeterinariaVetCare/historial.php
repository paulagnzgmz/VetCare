<?php
// Comprobar sesion
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Conectar a BD
include "includes/config.php";

// Calcular iniciales
$nombre_completo = $_SESSION['nombre'];
$partes = explode(" ", $nombre_completo);
$iniciales = strtoupper(substr($partes[0], 0, 1));
if (isset($partes[1])) {
    $iniciales = $iniciales . strtoupper(substr($partes[1], 0, 1));
}

// Consultar citas segun rol (Historial)
if ($_SESSION['rol'] == 'cliente') {
    $email_sesion = $_SESSION['email'];
    $sql_cliente = "SELECT id_cliente FROM clientes WHERE email = '$email_sesion'";
    $res_cliente = mysqli_query($conexion, $sql_cliente);
    $fila_cliente = mysqli_fetch_assoc($res_cliente);

    // Si no es cliente o no lo encuentra, ponemos 0 para que no rompa el SQL
    $id_cliente = ($fila_cliente) ? $fila_cliente['id_cliente'] : 0;

    $sql = "SELECT c.id_cita, c.fecha_hora, c.motivo, p.nombre AS mascota, 
                   u.nombre_completo AS veterinario, c.estado
            FROM citas c
            JOIN pacientes p ON c.id_paciente = p.id_paciente
            JOIN usuarios u ON c.id_usuario = u.id_usuario
            WHERE p.id_cliente = $id_cliente 
            AND c.estado = 'completada' 
            AND c.activo = 1
            ORDER BY c.fecha_hora DESC";
} else {
    // Para Admin y Recepcionistas (Luis)
    $sql = "SELECT c.id_cita, 
                   c.fecha_hora AS fecha, 
                   c.motivo, 
                   p.nombre AS mascota, 
                   p.especie,
                   u.nombre_completo AS veterinario, 
                   c.estado,
                   cl.nombre AS nombre_cliente, 
                   cl.apellidos AS apellido_cliente,
                   c.diagnostico, 
                   c.tratamiento, 
                   c.peso
            FROM citas c
            JOIN pacientes p ON c.id_paciente = p.id_paciente
            JOIN clientes cl ON p.id_cliente = cl.id_cliente
            JOIN usuarios u ON c.id_usuario = u.id_usuario
            WHERE c.estado = 'completada' 
            AND c.activo = 1
            ORDER BY c.fecha_hora DESC";
}

$resultado = mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Historial - Clinica Veterinaria</title>
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

    <!-- MENU LATERAL -->
    <aside class="menu-lateral">
        <div class="menu-logo">
            <img src="img/logo.png" alt="Logo VetCare" class="logo-img">
            Vet<span>Care</span>
        </div>

        <nav class="menu-nav">
            <a href="calendario.php" class="menu-enlace">Calendario</a>
            <a href="historial.php" class="menu-enlace activo">Historial clinico</a>
            <?php if ($_SESSION['rol'] == 'cliente') { ?>
                <a href="cliente_detalle.php?id=<?php echo $_SESSION['id_cliente']; ?>" class="menu-enlace">Mi ficha</a>
            <?php } ?>
            <?php if ($_SESSION['rol'] != 'cliente') { ?>
                <a href="citas.php" class="menu-enlace">Gestión de citas</a>
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

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenedor-principal">
        <main class="contenido">
            <?php if (!empty($_SESSION['aviso_licencia'])) { ?>
                <div class="mensaje-sesion error">
                    ⚠️ <?php echo $_SESSION['aviso_licencia']; ?>
                </div>
            <?php } ?>
            <div class="cabecera-pagina">
                <div>
                    <h1 class="titulo-pagina">Historial clinico</h1>
                    <p class="subtitulo-pagina">
                        <?php if ($_SESSION['rol'] == 'cliente') { ?>
                            Historial medico de tus mascotas
                        <?php } else { ?>
                            Registro de todas las consultas
                        <?php } ?>
                    </p>
                </div>
            </div>

            <?php if ($_SESSION['rol'] == 'cliente') { ?>
                <div class="aviso visible">
                    Mostrando unicamente las consultas de tus mascotas.
                </div>
            <?php } ?>

            <div class="tarjeta">
                <div class="tarjeta-cuerpo">
                    <table id="tablaHistorial" style="width:100%">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Mascota</th>
                                <th>Especie</th>
                                <th>Cliente</th>
                                <th>Veterinario</th>
                                <th>Motivo</th>
                                <th>Diagnóstico</th>
                                <th>Tratamiento</th>
                                <th>Peso (kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($fila = mysqli_fetch_assoc($resultado)) { ?>
                                <tr>
                                    <td><?php echo date('d/m/Y H:i', strtotime($fila['fecha'])); ?></td>
                                    <td><?php echo $fila['mascota']; ?></td>
                                    <td><?php echo $fila['especie']; ?></td>
                                    <td>
                                        <?php
                                        if ($_SESSION['rol'] == 'cliente') {
                                            echo $_SESSION['nombre'];
                                        } else {
                                            echo $fila['nombre_cliente'] . ' ' . $fila['apellido_cliente'];
                                        }
                                        ?>
                                    </td>
                                    <td><?php echo $fila['veterinario']; ?></td>
                                    <td><?php echo $fila['motivo']; ?></td>
                                    <td><?php echo $fila['diagnostico'] ? $fila['diagnostico'] : '-'; ?></td>
                                    <td><?php echo $fila['tratamiento'] ? $fila['tratamiento'] : '-'; ?></td>
                                    <td><?php echo $fila['peso'] ? $fila['peso'] . ' kg' : '-'; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-16 texto-gris">
                <a href="logout.php">Cerrar sesion</a>
            </p>

        </main>
        <footer class="pie-pagina">
            <p>&copy; 2026 VetCare Clínica Veterinaria — Laredo, Cantabria</p>
        </footer>
    </div>

    <script>
        $(document).ready(function () {
            $("#tablaHistorial").DataTable({
                language: {
                    search: "Buscar:",
                    lengthMenu: "Mostrar _MENU_ registros",
                    info: "Mostrando _START_ a _END_ de _TOTAL_ registros",
                    infoEmpty: "Sin registros",
                    zeroRecords: "No se encontraron resultados",
                    paginate: {
                        first: "Primero",
                        last: "Ultimo",
                        next: "Siguiente",
                        previous: "Anterior"
                    }
                },
                pageLength: 5,
                lengthMenu: [5, 10],
                order: [[0, "desc"]]
            });
        });
    </script>

</body>

</html>