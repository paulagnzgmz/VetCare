<?php
// =============================================
// paciente_detalle.php — Ficha completa del animal
// Muestra datos básicos + historial de citas completadas
// Accesible para todos los roles menos cliente
// =============================================

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

if (!isset($_GET['id'])) {
    header("Location: pacientes.php");
    exit();
}

$id_paciente = (int) $_GET['id'];

// Calcular iniciales
$nombre_completo = $_SESSION['nombre'];
$partes = explode(" ", $nombre_completo);
$iniciales = strtoupper(substr($partes[0], 0, 1));
if (isset($partes[1])) {
    $iniciales .= strtoupper(substr($partes[1], 0, 1));
}

// Datos del paciente y su propietario
$sql_paciente = "SELECT p.*, c.nombre AS nombre_cliente, c.apellidos AS apellidos_cliente,
                        c.telefono, c.email AS email_cliente, c.id_cliente
                 FROM pacientes p
                 JOIN clientes c ON p.id_cliente = c.id_cliente
                 WHERE p.id_paciente = $id_paciente AND p.activo = 1";
$res_paciente = mysqli_query($conexion, $sql_paciente);

if (mysqli_num_rows($res_paciente) == 0) {
    header("Location: pacientes.php");
    exit();
}

$paciente = mysqli_fetch_assoc($res_paciente);

// Calcular edad si hay fecha de nacimiento
$edad = '';
if ($paciente['fecha_nac']) {
    $nacimiento = new DateTime($paciente['fecha_nac']);
    $hoy = new DateTime();
    $diff = $nacimiento->diff($hoy);
    if ($diff->y > 0) {
        $edad = $diff->y . ' año' . ($diff->y > 1 ? 's' : '');
    } else if ($diff->m > 0) {
        $edad = $diff->m . ' mes' . ($diff->m > 1 ? 'es' : '');
    } else {
        $edad = $diff->d . ' día' . ($diff->d > 1 ? 's' : '');
    }
}

// Historial médico: citas completadas con diagnóstico
$sql_historial = "SELECT c.*, u.nombre_completo AS veterinario
                  FROM citas c
                  JOIN usuarios u ON c.id_usuario = u.id_usuario
                  WHERE c.id_paciente = $id_paciente
                    AND c.estado = 'completada'
                    AND c.activo = 1
                  ORDER BY c.fecha_hora DESC";
$res_historial = mysqli_query($conexion, $sql_historial);

// Próximas citas (pendientes o confirmadas)
$sql_proximas = "SELECT c.*, u.nombre_completo AS veterinario
                 FROM citas c
                 JOIN usuarios u ON c.id_usuario = u.id_usuario
                 WHERE c.id_paciente = $id_paciente
                   AND c.estado IN ('pendiente', 'confirmada')
                   AND c.activo = 1
                 ORDER BY c.fecha_hora ASC";
$res_proximas = mysqli_query($conexion, $sql_proximas);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title><?php echo $paciente['nombre']; ?> - Ficha Paciente</title>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="estilos.css?v=800">
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

            <div class="mb-20">
                <a href="pacientes.php" class="enlace-volver">← Volver a pacientes</a>
            </div>

            <div class="cabecera-pagina">
                <div>
                    <h1 class="titulo-pagina"><?php echo $paciente['nombre']; ?></h1>
                    <p class="subtitulo-pagina">
                        <?php echo $paciente['especie']; ?><?php echo $paciente['raza'] ? ' · ' . $paciente['raza'] : ''; ?>
                    </p>
                </div>
            </div>

            <!-- Datos del paciente -->
            <div class="tarjeta mb-20">
                <div class="tarjeta-cuerpo">
                    <h3 class="seccion-titulo">Datos del animal</h3>

                    <div style="display: flex; gap: 30px; align-items: flex-start; flex-wrap: wrap;">

                        <div class="foto-detalle">
                            <?php if (!empty($paciente['foto'])): ?>
                                <img src="<?php echo $paciente['foto']; ?>" alt="Foto de <?php echo $paciente['nombre']; ?>"
                                    style="width: 220px; height: 220px; object-fit: cover; border-radius: 15px; border: 3px solid #49b1b9; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                            <?php else: ?>
                                <div
                                    style="width: 220px; height: 220px; background: #f4f3f0; border-radius: 15px; display: flex; align-items: center; justify-content: center; color: #70706a; border: 2px dashed #e3e1db;">
                                    <span>Sin foto disponible</span>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div style="flex: 1; min-width: 300px;">
                            <div class="datos-grid">
                                <div>
                                    <p class="dato-etiqueta">Especie</p>
                                    <p class="dato-valor"><?php echo $paciente['especie']; ?></p>
                                </div>
                                <div>
                                    <p class="dato-etiqueta">Raza</p>
                                    <p class="dato-valor"><?php echo $paciente['raza'] ? $paciente['raza'] : '-'; ?></p>
                                </div>
                                <div>
                                    <p class="dato-etiqueta">Sexo</p>
                                    <p class="dato-valor">
                                        <?php echo $paciente['sexo'] ? ucfirst($paciente['sexo']) : '-'; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="dato-etiqueta">Fecha de nacimiento</p>
                                    <p class="dato-valor">
                                        <?php echo $paciente['fecha_nac'] ? date('d/m/Y', strtotime($paciente['fecha_nac'])) : '-'; ?>
                                        <?php echo isset($edad) ? " ($edad)" : ''; ?>
                                    </p>
                                </div>
                                <div>
                                    <p class="dato-etiqueta">Propietario</p>
                                    <p class="dato-valor">
                                        <a href="cliente_detalle.php?id=<?php echo $paciente['id_cliente']; ?>"
                                            class="enlace-tabla">
                                            <?php echo $paciente['nombre_cliente'] . ' ' . $paciente['apellidos_cliente']; ?>
                                        </a>
                                    </p>
                                </div>
                                <div>
                                    <p class="dato-etiqueta">Contacto</p>
                                    <p class="dato-valor">
                                        <?php echo $paciente['telefono'] ? $paciente['telefono'] : '-'; ?>
                                    </p>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Próximas citas -->
            <div class="tarjeta mb-20">
                <div class="tarjeta-cuerpo">
                    <h3 class="seccion-titulo">Próximas citas</h3>
                    <?php if (mysqli_num_rows($res_proximas) == 0) { ?>
                        <p class="texto-gris">No hay citas pendientes o confirmadas.</p>
                    <?php } else { ?>
                        <table class="tabla-simple">
                            <thead>
                                <tr>
                                    <th>Fecha y hora</th>
                                    <th>Motivo</th>
                                    <th>Veterinario</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php while ($cita = mysqli_fetch_assoc($res_proximas)) { ?>
                                    <tr>
                                        <td><?php echo date('d/m/Y H:i', strtotime($cita['fecha_hora'])); ?></td>
                                        <td><?php echo $cita['motivo']; ?></td>
                                        <td><?php echo $cita['veterinario']; ?></td>
                                        <td>
                                            <span class="etiqueta etiqueta-<?php echo $cita['estado']; ?>">
                                                <?php echo ucfirst($cita['estado']); ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    <?php } ?>
                </div>
            </div>

            <!-- Historial médico -->
            <div class="tarjeta">
                <div class="tarjeta-cuerpo">
                    <h3 class="seccion-titulo">Historial médico</h3>
                    <?php if (mysqli_num_rows($res_historial) == 0) { ?>
                        <p class="texto-gris">No hay visitas completadas registradas.</p>
                    <?php } else { ?>
                        <?php while ($visita = mysqli_fetch_assoc($res_historial)) { ?>
                            <div class="historial-item">
                                <div class="historial-item-cabecera">
                                    <div>
                                        <span class="historial-item-titulo"><?php echo $visita['motivo']; ?></span>
                                        <span class="historial-item-meta">
                                            <?php echo date('d/m/Y H:i', strtotime($visita['fecha_hora'])); ?>
                                            · <?php echo $visita['veterinario']; ?>
                                        </span>
                                    </div>
                                    <?php if ($visita['peso']) { ?>
                                        <span class="historial-item-peso">
                                            <?php echo $visita['peso']; ?> kg
                                        </span>
                                    <?php } ?>
                                </div>

                                <?php if ($visita['diagnostico']) { ?>
                                    <div class="historial-item-seccion">
                                        <p class="historial-item-etiqueta">Diagnóstico</p>
                                        <p class="historial-item-texto"><?php echo $visita['diagnostico']; ?></p>
                                    </div>
                                <?php } ?>

                                <?php if ($visita['tratamiento']) { ?>
                                    <div>
                                        <p class="historial-item-etiqueta">Tratamiento</p>
                                        <p class="historial-item-texto"><?php echo $visita['tratamiento']; ?></p>
                                    </div>
                                <?php } ?>

                                <?php if ($visita['notas']) { ?>
                                    <div class="historial-item-notas">
                                        <p class="historial-item-etiqueta">Notas</p>
                                        <p class="historial-item-notas-texto"><?php echo $visita['notas']; ?></p>
                                    </div>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    <?php } ?>
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

</body>

</html>