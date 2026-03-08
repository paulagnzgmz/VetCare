<?php
// Comprobar sesion
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

// Conectar a BD
include "includes/config.php";

// Actualizar automáticamente citas pasadas a completadas
$sql_update = "UPDATE citas 
               SET estado = 'completada' 
               WHERE DATE(fecha_hora) < CURDATE() 
               AND estado IN ('pendiente', 'confirmada')";
mysqli_query($conexion, $sql_update);

// Calcular iniciales del usuario para el avatar
$nombre_completo = $_SESSION['nombre'];
$partes = explode(" ", $nombre_completo);
$iniciales = strtoupper(substr($partes[0], 0, 1));
if (isset($partes[1])) {
    $iniciales = $iniciales . strtoupper(substr($partes[1], 0, 1));
}

// Consultar citas segun rol
if ($_SESSION['rol'] == 'cliente') {
    $sql_cliente = "SELECT id_cliente FROM clientes WHERE email = '" . $_SESSION['email'] . "'";
    $res_cliente = mysqli_query($conexion, $sql_cliente);
    $fila_cliente = mysqli_fetch_assoc($res_cliente);
    $id_cliente = isset($fila_cliente['id_cliente']) ? $fila_cliente['id_cliente'] : 0;

    $sql = "SELECT c.*, p.nombre AS mascota, p.especie
            FROM citas c
            JOIN pacientes p ON c.id_paciente = p.id_paciente
            WHERE p.id_cliente = $id_cliente
            ORDER BY c.fecha_hora";
} else {
    $sql = "SELECT c.*, p.nombre AS mascota, p.especie,
                   cl.nombre AS nombre_cliente, cl.apellidos AS apellido_cliente,
                   u.nombre_completo AS veterinario
            FROM citas c
            JOIN pacientes p ON c.id_paciente = p.id_paciente
            JOIN clientes cl ON p.id_cliente = cl.id_cliente
            JOIN usuarios u ON c.id_usuario = u.id_usuario
            ORDER BY c.fecha_hora";
}

$resultado = mysqli_query($conexion, $sql);

// Convertir a JSON para JavaScript
$citas = array();
while ($fila = mysqli_fetch_assoc($resultado)) {
    if ($fila['estado'] == 'confirmada') {
        $color = "#2563eb";
    } else if ($fila['estado'] == 'pendiente') {
        $color = "#d97706";
    } else {
        $color = "#6b7280";
    }

    $cita = array(
        'id' => $fila['id_cita'],
        'title' => $fila['mascota'] . ' - ' . $fila['motivo'],
        'start' => $fila['fecha_hora'],
        'color' => $color,
        'mascota' => $fila['mascota'],
        'especie' => $fila['especie'],
        'estado' => $fila['estado'],
        'veterinario' => isset($fila['veterinario']) ? $fila['veterinario'] : '',
        'cliente' => isset($fila['nombre_cliente']) ? $fila['nombre_cliente'] . ' ' . $fila['apellido_cliente'] : $_SESSION['nombre']
    );
    $citas[] = $cita;
}
$citas_json = json_encode($citas);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Calendario - Clinica Veterinaria</title>
    <link href="fullcalendar/index.global.min.css" rel="stylesheet">
    <script src="fullcalendar/index.global.min.js"></script>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="estilos.css?v=700">
</head>

<body>

    <!-- MENU LATERAL -->
    <aside class="menu-lateral">
        <div class="menu-logo">
            <img src="img/logo.png" alt="Logo VetCare" class="logo-img">
            Vet<span>Care</span>
        </div>

        <nav class="menu-nav">
            <a href="calendario.php" class="menu-enlace activo">Calendario</a>
            <a href="historial.php" class="menu-enlace">Historial clinico</a>
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
                <div class="usuario-iniciales">
                    <?php echo $iniciales; ?>
                </div>
                <div class="usuario-datos">
                    <span class="usuario-nombre">
                        <?php echo $_SESSION['nombre']; ?>
                    </span>
                    <span class="usuario-rol">
                        <?php echo $_SESSION['rol']; ?>
                    </span>
                </div>
            </div>
        </div>
    </aside>

    <!-- CONTENIDO PRINCIPAL -->
    <div class="contenedor-principal">
        <main class="contenido">

            <?php if (!empty($_SESSION['aviso_licencia'])) { ?>
                <div class="mensaje-sesion error">
                    <?php echo $_SESSION['aviso_licencia']; ?>
                </div>
            <?php } ?>

            <div class="cabecera-pagina">
                <div>
                    <h1 class="titulo-pagina">Calendario de citas</h1>
                    <p class="subtitulo-pagina">
                        <?php if ($_SESSION['rol'] == 'cliente') { ?>
                            Tus proximas citas
                        <?php } else { ?>
                            Todas las citas del centro
                        <?php } ?>
                    </p>
                </div>
                <div class="leyenda">
                    <div class="leyenda-item">
                        <div class="leyenda-punto leyenda-punto-confirmada"></div>Confirmada
                    </div>
                    <div class="leyenda-item">
                        <div class="leyenda-punto leyenda-punto-pendiente"></div>Pendiente
                    </div>
                    <div class="leyenda-item">
                        <div class="leyenda-punto leyenda-punto-completada"></div>Completada
                    </div>
                </div>
            </div>

            <div class="layout-calendario">
                <div class="tarjeta tarjeta-calendario">
                    <div id="calendario"></div>
                </div>

                <div class="panel-detalle">
                    <div class="detalle-vacio" id="detalleVacio">
                        <p>Selecciona una cita en el calendario para ver su detalle</p>
                    </div>
                    <div class="detalle-contenido" id="detalleContenido">
                        <div class="detalle-cabecera">
                            <h3 class="detalle-titulo" id="detalleTitulo"></h3>
                            <span class="etiqueta" id="detalleEstado"></span>
                        </div>
                        <div class="detalle-filas" id="detalleFilas"></div>
                    </div>
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
        const citas = <?php echo $citas_json; ?>;
    </script>
    <script src="script.js"></script>

</body>

</html>