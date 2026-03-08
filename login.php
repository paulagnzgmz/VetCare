<?php
session_start();

if (isset($_SESSION['id_usuario'])) {
    header("Location: calendario.php");
    exit();
}

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    include "includes/config.php";

    $email = trim($_POST['email']);
    $password = trim($_POST['password']);
    $md5 = md5($password);

    $sql = "SELECT * FROM usuarios WHERE email = '$email' AND password = '$md5'";
    $resultado = mysqli_query($conexion, $sql);

    if (mysqli_num_rows($resultado) == 1) {
        $usuario = mysqli_fetch_assoc($resultado);

        $_SESSION['id_usuario'] = $usuario['id_usuario'];
        $_SESSION['nombre'] = $usuario['nombre_completo'];
        $_SESSION['rol'] = $usuario['rol'];
        $_SESSION['email'] = $usuario['email'];
        if ($usuario['rol'] == 'cliente') {
            $sql_cliente = "SELECT id_cliente FROM clientes WHERE id_usuario = '{$usuario['id_usuario']}'";
            $res_cliente = mysqli_query($conexion, $sql_cliente);
            $cliente_row = mysqli_fetch_assoc($res_cliente);
            $_SESSION['id_cliente'] = $cliente_row['id_cliente'];
        }
        
        header("Location: calendario.php");
        exit();

    } else {
        $error = "Email o contrasena incorrectos";
    }
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <title>Login - Clinica Veterinaria</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@400;500&family=DM+Serif+Display&display=swap"
        rel="stylesheet">
    <link rel="stylesheet" href="estilos.css">
    <style>
        body {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding-top: 80px;
            min-height: 100vh;
            background-image: url('img/fondo.png');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }

        .caja-login {
            background: #fff;
            border: 1px solid #e3e1db;
            border-radius: 8px;
            padding: 40px;
            width: 360px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.50);
        }

        .login-titulo {
            font-family: 'DM Serif Display', serif;
            font-size: 22px;
            margin-bottom: 6px;
        }

        .login-subtitulo {
            font-size: 13px;
            color: #70706a;
            margin-bottom: 28px;
        }

        .campo {
            margin-bottom: 16px;
        }

        .campo label {
            display: block;
            font-size: 13px;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .campo input {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #e3e1db;
            border-radius: 8px;
            font-size: 14px;
            font-family: 'DM Sans', sans-serif;
        }

        .campo input:focus {
            outline: 2px solid #1d4ed8;
            outline-offset: 1px;
        }

        .boton-login {
            width: 100%;
            padding: 10px;
            background: #1d4ed8;
            color: #fff;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            margin-top: 8px;
        }

        .boton-login:hover {
            background: #1e40af;
        }

        .error {
            background: #fef2f2;
            border: 1px solid #fecaca;
            border-radius: 8px;
            padding: 10px 14px;
            font-size: 13px;
            color: #dc2626;
            margin-bottom: 16px;
        }
    </style>
</head>

<body>

    <div class="caja-login">
        <h1 class="login-titulo">Clinica Veterinaria</h1>
        <p class="login-subtitulo">Inicia sesion para continuar</p>

        <?php if ($error != "") { ?>
            <div class="error"><?php echo $error; ?></div>
        <?php } ?>

        <form method="POST" action="login.php" autocomplete="off">
            <div class="campo">
                <label for="email">Email</label>
                <input type="text" id="email" name="email" autocomplete="off">
            </div>

            <div class="campo">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" autocomplete="off">
            </div>

            <button type="submit" class="boton-login">Entrar</button>
        </form>
    </div>

</body>

</html>