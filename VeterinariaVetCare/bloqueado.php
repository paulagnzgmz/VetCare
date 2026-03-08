<?php
// =============================================
// bloqueado.php — Licencia caducada
// Se muestra cuando la licencia ha vencido
// o no existe. Redirige a pago.php
// =============================================
session_start();
session_destroy();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Acceso suspendido - VetCare</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="estilos.css">
    <style>
        body { display:flex; justify-content:center; align-items:center; min-height:100vh; background:#f4f3f0; }
        .bloq-caja { background:#fff; border:1px solid #e3e1db; border-radius:12px; padding:48px 40px; text-align:center; max-width:420px; width:100%; }
        .bloq-icono { font-size:48px; margin-bottom:16px; }
        .bloq-titulo { font-family:'DM Serif Display',serif; font-size:24px; margin-bottom:10px; color:#181816; }
        .bloq-texto { font-size:14px; color:#70706a; line-height:1.7; margin-bottom:28px; }
        .bloq-btn { display:block; width:100%; padding:11px; background:#1d4ed8; color:#fff; border:none; border-radius:8px; font-size:14px; font-weight:500; cursor:pointer; text-decoration:none; font-family:'DM Sans',sans-serif; margin-bottom:12px; }
        .bloq-btn:hover { background:#1e40af; }
        .bloq-contacto { font-size:12px; color:#70706a; }
        .bloq-contacto a { color:#1d4ed8; text-decoration:none; }
    </style>
</head>
<body>
<div class="bloq-caja">
    <div class="bloq-icono">🔒</div>
    <h1 class="bloq-titulo">Acceso suspendido</h1>
    <p class="bloq-texto">
        La suscripción de esta clínica ha caducado o no está activa.<br>
        Renueva tu plan para volver a acceder a VetCare.
    </p>
    <a href="pago.php" class="bloq-btn">Ver planes y renovar →</a>
    <p class="bloq-contacto">
        ¿Problemas con tu pago? Contacta con soporte en<br>
        <a href="mailto:soporte@vetcare.com">soporte@vetcare.com</a>
    </p>
</div>
</body>
</html>
