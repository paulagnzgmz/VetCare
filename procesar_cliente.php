<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] == 'cliente') {
    header("Location: login.php");
    exit();
}

include "includes/config.php";

$accion = $_POST['accion'] ?? '';

// ═══════════════════════════════════════════════════════
// EDITAR CLIENTE
// ═══════════════════════════════════════════════════════
if ($accion == 'editar') {
    $id_cliente = $_POST['id_cliente'];
    $nombre     = trim($_POST['nombre']);
    $apellidos  = trim($_POST['apellidos']);
    $telefono   = trim($_POST['telefono']);
    $email      = trim($_POST['email']);
    $direccion  = trim($_POST['direccion']);
    
    $sql = "UPDATE clientes 
            SET nombre = '$nombre',
                apellidos = '$apellidos',
                telefono = '$telefono',
                email = '$email',
                direccion = " . ($direccion ? "'$direccion'" : "NULL") . "
            WHERE id_cliente = $id_cliente";
    
    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje'] = "Cliente actualizado correctamente";
    } else {
        $_SESSION['error'] = "Error al actualizar el cliente: " . mysqli_error($conexion);
    }
}

// ═══════════════════════════════════════════════════════
// ELIMINAR CLIENTE (BORRADO LÓGICO)
// ═══════════════════════════════════════════════════════
else if ($accion == 'eliminar') {
    $id_cliente = $_POST['id_cliente'];
    
    // Borrado lógico: marcar como inactivo
    $sql = "UPDATE clientes SET activo = 0 WHERE id_cliente = $id_cliente";
    
    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje'] = "Cliente eliminado correctamente";
    } else {
        $_SESSION['error'] = "Error al eliminar el cliente: " . mysqli_error($conexion);
    }
}

header("Location: clientes.php");
exit();
?>
