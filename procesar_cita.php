<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] == 'cliente') {
    header("Location: login.php");
    exit();
}

include "includes/config.php";

$accion = $_POST['accion'] ?? '';

// ═══════════════════════════════════════════════════════
// COMPLETAR CITA — Solo veterinario
// Guarda diagnóstico, tratamiento y peso, y cambia
// el estado a completada
// ═══════════════════════════════════════════════════════
if ($accion == 'completar') {
    if ($_SESSION['rol'] != 'veterinario') {
        header("Location: citas.php");
        exit();
    }

    $id_cita    = (int) $_POST['id_cita'];
    $diagnostico = trim($_POST['diagnostico']);
    $tratamiento = trim($_POST['tratamiento']);
    $peso        = trim($_POST['peso']);

    $sql = "UPDATE citas SET
                estado      = 'completada',
                diagnostico = " . ($diagnostico ? "'$diagnostico'" : "NULL") . ",
                tratamiento = " . ($tratamiento ? "'$tratamiento'" : "NULL") . ",
                peso        = " . ($peso !== '' ? $peso : "NULL") . "
            WHERE id_cita = $id_cita";

    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje'] = "Cita completada y datos médicos guardados correctamente";
    } else {
        $_SESSION['error'] = "Error al completar la cita: " . mysqli_error($conexion);
    }

    header("Location: citas.php");
    exit();
}

// ═══════════════════════════════════════════════════════
// EDITAR CITA
// ═══════════════════════════════════════════════════════
if ($accion == 'editar') {
    $id_cita     = $_POST['id_cita'];
    $id_paciente = $_POST['id_paciente'];
    $id_usuario  = $_POST['id_usuario'];
    $fecha       = $_POST['fecha'];
    $hora        = $_POST['hora'];
    $motivo      = trim($_POST['motivo']);
    $estado      = $_POST['estado'];
    $notas       = trim($_POST['notas']);
    
    $fecha_hora = $fecha . ' ' . $hora . ':00';
    
    $sql = "UPDATE citas 
            SET id_paciente = $id_paciente,
                id_usuario = $id_usuario,
                fecha_hora = '$fecha_hora',
                motivo = '$motivo',
                estado = '$estado',
                notas = " . ($notas ? "'$notas'" : "NULL") . "
            WHERE id_cita = $id_cita";
    
    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje'] = "Cita actualizada correctamente";
    } else {
        $_SESSION['error'] = "Error al actualizar la cita: " . mysqli_error($conexion);
    }
}

// ═══════════════════════════════════════════════════════
// ELIMINAR CITA (BORRADO LÓGICO)
// ═══════════════════════════════════════════════════════
else if ($accion == 'eliminar') {
    $id_cita = $_POST['id_cita'];
    
    // Borrado lógico: marcar como inactivo
    $sql = "UPDATE citas SET activo = 0 WHERE id_cita = $id_cita";
    
    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje'] = "Cita eliminada correctamente";
    } else {
        $_SESSION['error'] = "Error al eliminar la cita: " . mysqli_error($conexion);
    }
}

header("Location: citas.php");
exit();
?>
