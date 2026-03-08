<?php
// =============================================
// pago.php — Página de suscripción VetCare
// Accesible sin login, se llega aquí cuando
// la licencia ha caducado o no existe
// =============================================

session_start();
include "includes/config.php";

// Si ya está activa la licencia, redirigir
$res_lic = mysqli_query($conexion, "SELECT * FROM licencia LIMIT 1");
$licencia = mysqli_fetch_assoc($res_lic);
$hoy = date('Y-m-d');
if ($licencia && $licencia['activa'] == 1 && $licencia['fecha_vencimiento'] >= $hoy) {
    header("Location: login.php");
    exit();
}

$PAYPAL_CLIENT_ID = "AWTrv2DC1VyVoR0FIecAQ0AucQ6f_6lDl6NgTXduYfrD10vUSILCCRYD7c-WLGFeBpLXDGetM6BxW4tH";

// IDs de los planes creados en PayPal Sandbox
$planes = [
    'lite_mensual' => ['id' => 'P-5R252855S2301431CNGTJ23A', 'nombre' => 'VetCare Lite', 'precio' => '29€/mes', 'periodo' => 'mensual'],
    'pro_mensual' => ['id' => 'P-1SU80373W9292410HNGTJ4XQ', 'nombre' => 'VetCare Pro', 'precio' => '59€/mes', 'periodo' => 'mensual'],
    'hospital_mensual' => ['id' => 'P-5LK91083RC540704PNGTJ5AI', 'nombre' => 'VetCare Hospital', 'precio' => '99€/mes', 'periodo' => 'mensual'],
    'lite_anual' => ['id' => 'P-1HU97224H2312945VNGTJ5JI', 'nombre' => 'VetCare Lite', 'precio' => '313€/año', 'periodo' => 'anual'],
    'pro_anual' => ['id' => 'P-7RJ35744B20459903NGTJ5OQ', 'nombre' => 'VetCare Pro', 'precio' => '637€/año', 'periodo' => 'anual'],
    'hospital_anual' => ['id' => 'P-3FS8679536080025MNGTJ5TQ', 'nombre' => 'VetCare Hospital', 'precio' => '1.069€/año', 'periodo' => 'anual'],
];
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Suscripción - VetCare</title>
    <link
        href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            min-height: 100vh;
            padding: 40px 20px;
            background: #f4f3f0;
        }

        .pago-contenedor {
            width: 100%;
            max-width: 860px;
        }

        .pago-logo {
            font-family: 'DM Serif Display', serif;
            font-size: 26px;
            text-align: center;
            margin-bottom: 6px;
        }

        .pago-logo span {
            color: #1d4ed8;
        }

        .pago-subtitulo {
            text-align: center;
            color: #70706a;
            font-size: 14px;
            margin-bottom: 36px;
        }

        .pago-toggle {
            display: flex;
            justify-content: center;
            gap: 0;
            margin-bottom: 28px;
        }

        .pago-toggle button {
            padding: 9px 28px;
            border: 1px solid #e3e1db;
            background: #fff;
            font-family: 'DM Sans', sans-serif;
            font-size: 14px;
            cursor: pointer;
            transition: all .15s;
        }

        .pago-toggle button:first-child {
            border-radius: 8px 0 0 8px;
        }

        .pago-toggle button:last-child {
            border-radius: 0 8px 8px 0;
            border-left: none;
        }

        .pago-toggle button.activo {
            background: #1d4ed8;
            color: #fff;
            border-color: #1d4ed8;
        }

        .planes-grid {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr;
            gap: 20px;
            margin-bottom: 20px;
        }

        .plan-tarjeta {
            background: #fff;
            border: 1px solid #e3e1db;
            border-radius: 12px;
            padding: 28px 24px;
            display: flex;
            flex-direction: column;
            gap: 16px;
            transition: box-shadow .2s;
        }

        .plan-tarjeta:hover {
            box-shadow: 0 4px 16px rgba(0, 0, 0, .08);
        }

        .plan-tarjeta.destacado {
            border-color: #1d4ed8;
            box-shadow: 0 0 0 2px #1d4ed820;
        }

        .plan-badge {
            display: inline-block;
            background: #1d4ed8;
            color: #fff;
            font-size: 11px;
            font-weight: 600;
            padding: 3px 10px;
            border-radius: 20px;
            width: fit-content;
        }

        .plan-nombre {
            font-family: 'DM Serif Display', serif;
            font-size: 20px;
        }

        .plan-precio {
            font-size: 28px;
            font-weight: 600;
            color: #181816;
        }

        .plan-precio span {
            font-size: 14px;
            font-weight: 400;
            color: #70706a;
        }

        .plan-features {
            list-style: none;
            display: flex;
            flex-direction: column;
            gap: 8px;
            flex: 1;
        }

        .plan-features li {
            font-size: 13px;
            color: #70706a;
            padding-left: 18px;
            position: relative;
        }

        .plan-features li::before {
            content: "✓";
            position: absolute;
            left: 0;
            color: #16a34a;
            font-weight: 600;
        }

        .plan-btn-wrap {
            min-height: 52px;
        }

        .paypal-btn-placeholder {
            width: 100%;
            height: 44px;
            background: #f4f3f0;
            border-radius: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            color: #70706a;
        }

        .pago-aviso {
            text-align: center;
            font-size: 12px;
            color: #70706a;
            margin-top: 8px;
        }

        .pago-login {
            text-align: center;
            font-size: 13px;
            margin-top: 20px;
        }

        .pago-login a {
            color: #1d4ed8;
            text-decoration: none;
        }

        .planes-anual {
            display: none;
        }

        .ahorro-badge {
            font-size: 11px;
            background: #ecfdf5;
            color: #065f46;
            padding: 3px 8px;
            border-radius: 20px;
            font-weight: 500;
        }
    </style>
</head>

<body>

    <div class="pago-contenedor">

        <h1 class="pago-logo">Vet<span>Care</span></h1>
        <p class="pago-subtitulo">Elige el plan que mejor se adapta a tu clínica</p>

        <!-- Toggle mensual / anual -->
        <div class="pago-toggle">
            <button class="activo" id="btnMensual" onclick="mostrarPeriodo('mensual')">Mensual</button>
            <button id="btnAnual" onclick="mostrarPeriodo('anual')">Anual <span
                    class="ahorro-badge">−10%</span></button>
        </div>

        <!-- Planes mensuales -->
        <div class="planes-grid planes-mensual" id="planesMensual">

            <!-- Lite -->
            <div class="plan-tarjeta">
                <div>
                    <p class="plan-nombre">VetCare Lite</p>
                    <p class="plan-precio">29€ <span>/ mes</span></p>
                </div>
                <ul class="plan-features">
                    <li>Calendario de citas</li>
                    <li>Gestión de pacientes</li>
                    <li>Historial clínico</li>
                    <li>Hasta 2 usuarios</li>
                </ul>
                <div class="plan-btn-wrap" id="paypal-lite-mensual"></div>
            </div>

            <!-- Pro -->
            <div class="plan-tarjeta destacado">
                <div>
                    <span class="plan-badge">Más popular</span>
                    <p class="plan-nombre">VetCare Pro</p>
                    <p class="plan-precio">59€ <span>/ mes</span></p>
                </div>
                <ul class="plan-features">
                    <li>Todo lo de Lite</li>
                    <li>Gestión de clientes</li>
                    <li>API REST incluida</li>
                    <li>Hasta 5 usuarios</li>
                    <li>Soporte prioritario</li>
                </ul>
                <div class="plan-btn-wrap" id="paypal-pro-mensual"></div>
            </div>

            <!-- Hospital -->
            <div class="plan-tarjeta">
                <div>
                    <p class="plan-nombre">VetCare Hospital</p>
                    <p class="plan-precio">99€ <span>/ mes</span></p>
                </div>
                <ul class="plan-features">
                    <li>Todo lo de Pro</li>
                    <li>Usuarios ilimitados</li>
                    <li>Multiclínica</li>
                    <li>Soporte 24/7</li>
                    <li>Formación incluida</li>
                </ul>
                <div class="plan-btn-wrap" id="paypal-hospital-mensual"></div>
            </div>

        </div>

        <!-- Planes anuales -->
        <div class="planes-grid planes-anual" id="planesAnual">

            <!-- Lite anual -->
            <div class="plan-tarjeta">
                <div>
                    <p class="plan-nombre">VetCare Lite</p>
                    <p class="plan-precio">313€ <span>/ año</span></p>
                    <p style="font-size:12px;color:#70706a;">~26€/mes — ahorras 35€</p>
                </div>
                <ul class="plan-features">
                    <li>Calendario de citas</li>
                    <li>Gestión de pacientes</li>
                    <li>Historial clínico</li>
                    <li>Hasta 2 usuarios</li>
                </ul>
                <div class="plan-btn-wrap" id="paypal-lite-anual"></div>
            </div>

            <!-- Pro anual -->
            <div class="plan-tarjeta destacado">
                <div>
                    <span class="plan-badge">Más popular</span>
                    <p class="plan-nombre">VetCare Pro</p>
                    <p class="plan-precio">637€ <span>/ año</span></p>
                    <p style="font-size:12px;color:#70706a;">~53€/mes — ahorras 71€</p>
                </div>
                <ul class="plan-features">
                    <li>Todo lo de Lite</li>
                    <li>Gestión de clientes</li>
                    <li>API REST incluida</li>
                    <li>Hasta 5 usuarios</li>
                    <li>Soporte prioritario</li>
                </ul>
                <div class="plan-btn-wrap" id="paypal-pro-anual"></div>
            </div>

            <!-- Hospital anual -->
            <div class="plan-tarjeta">
                <div>
                    <p class="plan-nombre">VetCare Hospital</p>
                    <p class="plan-precio">1.069€ <span>/ año</span></p>
                    <p style="font-size:12px;color:#70706a;">~89€/mes — ahorras 119€</p>
                </div>
                <ul class="plan-features">
                    <li>Todo lo de Pro</li>
                    <li>Usuarios ilimitados</li>
                    <li>Multiclínica</li>
                    <li>Soporte 24/7</li>
                    <li>Formación incluida</li>
                </ul>
                <div class="plan-btn-wrap" id="paypal-hospital-anual"></div>
            </div>

        </div>

        <p class="pago-aviso">🔒 Pago seguro procesado por PayPal · Cancela cuando quieras</p>
        <p class="pago-login">¿Ya tienes una suscripción activa? <a href="login.php">Iniciar sesión</a></p>

        <footer class="pie-pagina">
            <p>&copy; 2026 VetCare Clínica Veterinaria — Laredo, Cantabria</p>
        </footer>
    </div>

    <!-- SDK de PayPal Sandbox -->
    <script
        src="https://www.paypal.com/sdk/js?client-id=AWTrv2DC1VyVoR0FIecAQ0AucQ6f_6lDl6NgTXduYfrD10vUSILCCRYD7c-WLGFeBpLXDGetM6BxW4tH&vault=true&intent=subscription&currency=EUR"></script>
    <script>

        // ── Función para renderizar un botón PayPal ─── Paula mira esto
        function crearBoton(contenedorId, planId, planNombre, periodoMeses) {
            paypal.Buttons({
                style: {
                    shape: 'rect',
                    color: 'blue',
                    layout: 'horizontal',
                    label: 'subscribe',
                    height: 44
                },
                createSubscription: function (data, actions) {
                    return actions.subscription.create({ plan_id: planId });
                },
                onApprove: function (data) {
                    // Redirigir a pago_ok.php con los datos de la suscripción
                    window.location.href = 'pago_ok.php'
                        + '?sub_id=' + data.subscriptionID
                        + '&plan=' + encodeURIComponent(planNombre)
                        + '&meses=' + periodoMeses;
                },
                onError: function (err) {
                    alert('Ha ocurrido un error con el pago. Por favor inténtalo de nuevo.');
                    console.error(err);
                }
            }).render('#' + contenedorId);
        }

        // ── Renderizar todos los botones ──────────────
        // Mensual (1 mes)
        crearBoton('paypal-lite-mensual', 'P-5R252855S2301431CNGTJ23A', 'VetCare Lite', 1);
        crearBoton('paypal-pro-mensual', 'P-1SU80373W9292410HNGTJ4XQ', 'VetCare Pro', 1);
        crearBoton('paypal-hospital-mensual', 'P-5LK91083RC540704PNGTJ5AI', 'VetCare Hospital', 1);

        // Anual (12 meses)
        crearBoton('paypal-lite-anual', 'P-1HU97224H2312945VNGTJ5JI', 'VetCare Lite', 12);
        crearBoton('paypal-pro-anual', 'P-7RJ35744B20459903NGTJ5OQ', 'VetCare Pro', 12);
        crearBoton('paypal-hospital-anual', 'P-3FS8679536080025MNGTJ5TQ', 'VetCare Hospital', 12);

        // ── Toggle mensual / anual ────────────────────
        function mostrarPeriodo(periodo) {
            const esMensual = periodo === 'mensual';
            document.getElementById('planesMensual').style.display = esMensual ? 'grid' : 'none';
            document.getElementById('planesAnual').style.display = esMensual ? 'none' : 'grid';
            document.getElementById('btnMensual').classList.toggle('activo', esMensual);
            document.getElementById('btnAnual').classList.toggle('activo', !esMensual);
        }

    </script>
</body>

</html>