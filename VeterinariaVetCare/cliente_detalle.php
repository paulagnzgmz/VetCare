<?php
session_start();
if (!isset($_SESSION['id_usuario'])) {
    header("Location: login.php");
    exit();
}

if ($_SESSION['rol'] == 'cliente') {
    if (!isset($_GET['id']) || $_GET['id'] != $_SESSION['id_cliente']) {
        header("Location: calendario.php");
        exit();
    }
}

include "includes/config.php";

if (!isset($_GET['id'])) {
    header("Location: clientes.php");
    exit();
}

$id_cliente = mysqli_real_escape_string($conexion, $_GET['id']);

// Iniciales
$nombre_completo = $_SESSION['nombre'];
$partes = explode(" ", $nombre_completo);
$iniciales = strtoupper(substr($partes[0], 0, 1));
if (isset($partes[1])) {
    $iniciales .= strtoupper(substr($partes[1], 0, 1));
}

// Consultas
$sql_cliente = "SELECT c.*, u.email FROM clientes c JOIN usuarios u ON c.id_usuario = u.id_usuario WHERE c.id_cliente = $id_cliente";
$res_cliente = mysqli_query($conexion, $sql_cliente);
if (mysqli_num_rows($res_cliente) == 0) {
    header("Location: clientes.php");
    exit();
}
$cliente = mysqli_fetch_assoc($res_cliente);

$res_mascotas = mysqli_query($conexion, "SELECT * FROM pacientes WHERE id_cliente = $id_cliente ORDER BY nombre");
$res_citas = mysqli_query($conexion, "SELECT c.*, p.nombre AS mascota, u.nombre_completo AS veterinario FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente JOIN usuarios u ON c.id_usuario = u.id_usuario WHERE p.id_cliente = $id_cliente ORDER BY c.fecha_hora DESC LIMIT 10");
$res_historial = mysqli_query($conexion, "SELECT c.id_cita, c.fecha_hora AS fecha, p.nombre AS mascota, u.nombre_completo AS veterinario, c.motivo, c.diagnostico, c.tratamiento, c.peso FROM citas c JOIN pacientes p ON c.id_paciente = p.id_paciente JOIN usuarios u ON c.id_usuario = u.id_usuario WHERE p.id_cliente = $id_cliente AND c.estado = 'completada' AND c.activo = 1 ORDER BY c.fecha_hora DESC LIMIT 10");
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Ficha Cliente - Clinica Veterinaria</title>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="estilos.css?v=<?php echo time(); ?>">
</head>

<body>

    <aside class="menu-lateral">
        <div class="menu-logo"><img src="img/logo.png" alt="Logo" class="logo-img"> Vet<span>Care</span></div>
        <nav class="menu-nav">
            <a href="calendario.php" class="menu-enlace">Calendario</a>
            <a href="historial.php" class="menu-enlace">Historial clínico</a>
            <?php if ($_SESSION['rol'] == 'cliente') { ?>
                <a href="cliente_detalle.php?id=<?php echo $_SESSION['id_cliente']; ?>" class="menu-enlace activo">Mi
                    ficha</a>
            <?php } else { ?>
                <a href="citas.php" class="menu-enlace">Gestión de citas</a>
                <a href="pacientes.php" class="menu-enlace">Pacientes</a>
                <a href="clientes.php" class="menu-enlace activo">Clientes</a>
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
            <div class="mb-20"><a href="clientes.php" class="enlace-volver">← Volver</a></div>
            <div class="cabecera-pagina">
                <h1 class="titulo-pagina"><?php echo $cliente['nombre'] . ' ' . $cliente['apellidos']; ?></h1>
                <p class="subtitulo-pagina">Ficha del cliente</p>
            </div>

            <div class="tarjeta mb-20">
                <div class="tarjeta-cuerpo">
                    <h3 class="seccion-titulo">Datos de contacto</h3>
                    <div class="datos-grid">
                        <div>
                            <p class="dato-etiqueta">Email</p>
                            <p class="dato-valor"><?php echo $cliente['email']; ?></p>
                        </div>
                        <div>
                            <p class="dato-etiqueta">Teléfono</p>
                            <p class="dato-valor"><?php echo $cliente['telefono'] ?: '-'; ?></p>
                        </div>
                        <div class="campo-completo">
                            <p class="dato-etiqueta">Dirección</p>
                            <p class="dato-valor"><?php echo $cliente['direccion'] ?: '-'; ?></p>
                        </div>
                    </div>
                </div>
            </div>

            <div class="tarjeta mb-20">
                <div class="tarjeta-cuerpo">
                    <h3 class="seccion-titulo">Mascotas</h3>
                    <div class="mascotas-grid">
                        <?php while ($m = mysqli_fetch_assoc($res_mascotas)) { ?>
                            <div class="mascota-tarjeta">
                                <?php if (!empty($m['foto'])) { ?>
                                    <img src="<?php echo $m['foto']; ?>" alt="<?php echo $m['nombre']; ?>" class="mascota-foto">
                                <?php } else { ?>
                                    <div class="mascota-sin-foto">Sin foto</div>
                                <?php } ?>

                                <h4 class="mascota-nombre"><?php echo $m['nombre']; ?></h4>
                                <p class="mascota-dato"><strong>Especie:</strong> <?php echo $m['especie']; ?></p>
                                <p class="mascota-dato"><strong>Raza:</strong> <?php echo $m['raza'] ?: '-'; ?></p>
                            </div>
                        <?php } ?>
                    </div>
                </div>
            </div>

            <div class="tarjeta mb-20">
                <div class="tarjeta-cuerpo">
                    <h3 class="seccion-titulo">Citas y Consultas</h3>
                    <table class="tabla-simple">
                        <thead>
                            <tr>
                                <th>Fecha</th>
                                <th>Mascota</th>
                                <th>Motivo</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($c = mysqli_fetch_assoc($res_citas)) { ?>
                                <tr>
                                    <td><?php echo date('d/m/Y', strtotime($c['fecha_hora'])); ?></td>
                                    <td><?php echo $c['mascota']; ?></td>
                                    <td><?php echo $c['motivo']; ?></td>
                                </tr>
                            <?php } ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <p class="mt-16"><a href="logout.php">Cerrar sesión</a></p>
        </main>

        <footer class="pie-pagina">
            <p>&copy; 2026 VetCare Clínica Veterinaria — Laredo, Cantabria</p>
        </footer>
    </div>
</body>

</html>