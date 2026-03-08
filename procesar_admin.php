<?php
// =============================================
// procesar_admin.php — Procesa acciones del panel admin
// Solo accesible para el rol 'admin'
// =============================================

session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'admin') {
    header("Location: login.php");
    exit();
}

include "includes/config.php";

$accion = $_POST['accion'] ?? '';

// ═══════════════════════════════════════════════════════
// EDITAR USUARIO
// ═══════════════════════════════════════════════════════
if ($accion == 'editar') {
    $id_usuario = (int) $_POST['id_usuario'];
    $nombre     = trim($_POST['nombre']);
    $email      = trim($_POST['email']);
    $rol        = $_POST['rol'];
    $password   = trim($_POST['password']);

    $roles_permitidos = ['veterinario', 'recepcionista', 'admin'];
    if (!in_array($rol, $roles_permitidos)) {
        $_SESSION['error'] = "Rol no válido";
        header("Location: admin.php");
        exit();
    }

    // Comprobar email duplicado (excluyendo el propio usuario)
    $sql_check = "SELECT id_usuario FROM usuarios WHERE email = '$email' AND id_usuario != $id_usuario";
    $res_check = mysqli_query($conexion, $sql_check);
    if (mysqli_num_rows($res_check) > 0) {
        $_SESSION['error'] = "Ya existe otro usuario con ese email";
        header("Location: admin.php");
        exit();
    }

    if ($password != "") {
        // Cambiar contraseña también
        $md5 = md5($password);
        $sql = "UPDATE usuarios
                SET nombre_completo = '$nombre',
                    email = '$email',
                    rol = '$rol',
                    password = '$md5'
                WHERE id_usuario = $id_usuario";
    } else {
        // Mantener contraseña actual
        $sql = "UPDATE usuarios
                SET nombre_completo = '$nombre',
                    email = '$email',
                    rol = '$rol'
                WHERE id_usuario = $id_usuario";
    }

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje'] = "Usuario actualizado correctamente";
    } else {
        $_SESSION['error'] = "Error al actualizar el usuario: " . mysqli_error($conexion);
    }
}

// ═══════════════════════════════════════════════════════
// ELIMINAR USUARIO
// No se puede eliminar el propio admin que está en sesión
// ═══════════════════════════════════════════════════════
else if ($accion == 'eliminar') {
    $id_usuario = (int) $_POST['id_usuario'];

    if ($id_usuario == $_SESSION['id_usuario']) {
        $_SESSION['error'] = "No puedes eliminar tu propio usuario";
        header("Location: admin.php");
        exit();
    }

    $sql = "DELETE FROM usuarios WHERE id_usuario = $id_usuario AND rol != 'cliente'";

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje'] = "Usuario eliminado correctamente";
    } else {
        $_SESSION['error'] = "Error al eliminar el usuario: " . mysqli_error($conexion);
    }
}

header("Location: admin.php");
exit();
?>
