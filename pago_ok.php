<?php
// =============================================
// pago_ok.php — Confirmación de pago
// PayPal redirige aquí tras una suscripción
// correcta. Activa la licencia en la BD.
// =============================================

session_start();
include "includes/config.php";

$sub_id = $_GET['sub_id'] ?? '';
$plan = $_GET['plan'] ?? '';
$meses = (int) ($_GET['meses'] ?? 1);

if (!$sub_id) {
    header("Location: pago.php");
    exit();
}

// Calcular fechas
$fecha_inicio = date('Y-m-d');
$fecha_vencimiento = date('Y-m-d', strtotime("+$meses months"));

// Borrar licencia anterior si existe e insertar la nueva
mysqli_query($conexion, "DELETE FROM licencia");
$sql = "INSERT INTO licencia (activa, plan, fecha_inicio, fecha_vencimiento, paypal_sub_id)
        VALUES (1, '$plan', '$fecha_inicio', '$fecha_vencimiento', '$sub_id')";
mysqli_query($conexion, $sql);
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Pago completado - VetCare</title>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: #f4f3f0;
        }

        .ok-caja {
            background: #fff;
            border: 1px solid #e3e1db;
            border-radius: 12px;
            padding: 48px 40px;
            text-align: center;
            max-width: 420px;
            width: 100%;
        }

        .ok-icono {
            font-size: 48px;
            margin-bottom: 16px;
        }

        .ok-titulo {
            font-family: 'DM Serif Display', serif;
            font-size: 24px;
            margin-bottom: 8px;
        }

        .ok-texto {
            font-size: 14px;
            color: #70706a;
            margin-bottom: 6px;
        }

        .ok-plan {
            display: inline-block;
            background: #eff4ff;
            color: #1d4ed8;
            font-size: 13px;
            font-weight: 600;
            padding: 6px 16px;
            border-radius: 20px;
            margin: 12px 0 24px;
        }

        .ok-detalle {
            font-size: 12px;
            color: #70706a;
            margin-bottom: 24px;
        }

        .ok-btn {
            display: block;
            width: 100%;
            padding: 11px;
            background: #1d4ed8;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            font-family: 'DM Sans', sans-serif;
        }

        .ok-btn:hover {
            background: #1e40af;
        }
    </style>
</head>

<body>
    <div class="ok-caja">
        <div class="ok-icono">✅</div>
        <h1 class="ok-titulo">¡Suscripción activada!</h1>
        <p class="ok-texto">Tu plan ha sido activado correctamente.</p>
        <span class="ok-plan"><?php echo htmlspecialchars($plan); ?></span>
        <p class="ok-detalle">
            Activo hasta el <strong><?php echo date('d/m/Y', strtotime($fecha_vencimiento)); ?></strong><br>
            ID de suscripción: <code><?php echo htmlspecialchars($sub_id); ?></code>
        </p>
        <a href="login.php" class="ok-btn">Iniciar sesión →</a>
        <footer class="pie-pagina">
            <p>&copy; 2026 VetCare Clínica Veterinaria — Laredo, Cantabria</p>
        </footer>
    </div>
</body>

</html>