<?php
// =============================================
// config.php — Conexión a la base de datos
// y comprobación de licencia activa.
//
// Se incluye en todas las páginas que necesiten
// acceder a MySQL. Si la licencia ha caducado,
// redirige automáticamente a bloqueado.php
// =============================================

$host     = "localhost";
$usuario  = "root";
$password = "";
$base     = "veterinaria";

$conexion = mysqli_connect($host, $usuario, $password, $base);

if (!$conexion) {
    die("Error al conectar: " . mysqli_connect_error());
}

mysqli_set_charset($conexion, "utf8");

// ── Comprobación de licencia ──────────────────
// Páginas que NO deben comprobar la licencia
$pagina_actual  = basename($_SERVER['PHP_SELF']);
$paginas_libres = ['bloqueado.php', 'pago.php', 'pago_ok.php', 'login.php', 'logout.php'];

if (!in_array($pagina_actual, $paginas_libres)) {

    $res_lic  = mysqli_query($conexion, "SELECT * FROM licencia LIMIT 1");
    $licencia = mysqli_fetch_assoc($res_lic);
    $hoy      = date('Y-m-d');

    // Calcular fecha límite con periodo de gracia
    $gracia       = $licencia ? (int)$licencia['periodo_gracia'] : 0;
    $fecha_limite = $licencia
        ? date('Y-m-d', strtotime($licencia['fecha_vencimiento'] . " +{$gracia} days"))
        : null;

    $licencia_ok = $licencia
        && $licencia['activa'] == 1
        && $fecha_limite >= $hoy;

    if (!$licencia_ok) {
    // El admin puede entrar aunque esté caducada
    if (!isset($_SESSION['rol']) || $_SESSION['rol'] != 'admin') {
        header("Location: bloqueado.php");
        exit();
    }
}

    // Aviso si quedan menos de 7 días
    if ($licencia && $licencia['fecha_vencimiento'] < date('Y-m-d', strtotime('+7 days'))) {
        $_SESSION['aviso_licencia'] = "Tu suscripción vence el " .
            date('d/m/Y', strtotime($licencia['fecha_vencimiento'])) .
            ". <a href='pago.php'>Renovar ahora</a>";
    }
}
?>