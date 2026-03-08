<?php
session_start();
if (!isset($_SESSION['id_usuario']) || $_SESSION['rol'] != 'veterinario') {
    header("Location: pacientes.php");
    exit();
}

include "includes/config.php";

$accion = $_POST['accion'] ?? '';

// ═══════════════════════════════════════════════════════
// EDITAR PACIENTE
// ═══════════════════════════════════════════════════════
if ($accion == 'editar') {
    $id_paciente = $_POST['id_paciente'];
    $nombre      = trim($_POST['nombre']);
    $especie     = trim($_POST['especie']);
    $raza        = trim($_POST['raza']);
    $fecha_nac   = $_POST['fecha_nac'];
    $sexo        = $_POST['sexo'];
    $foto_actual = $_POST['foto_actual'];
    $foto        = $foto_actual; // Mantener foto actual por defecto
    
    // Procesar nueva foto si se subió
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $extension = strtolower(pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION));
        $permitidas = array('jpg', 'jpeg', 'png', 'gif');
        
        if (in_array($extension, $permitidas)) {
            $nombre_archivo = uniqid() . '.' . $extension;
            $ruta_destino = 'uploads/mascotas/' . $nombre_archivo;
            
            if (!is_dir('uploads/mascotas')) {
                mkdir('uploads/mascotas', 0777, true);
            }
            
            if (move_uploaded_file($_FILES['foto']['tmp_name'], $ruta_destino)) {
                // Eliminar foto anterior si existe
                if ($foto_actual && file_exists($foto_actual)) {
                    unlink($foto_actual);
                }
                $foto = $ruta_destino;
            }
        }
    }
    
    $sql = "UPDATE pacientes 
            SET nombre = '$nombre',
                especie = '$especie',
                raza = " . ($raza ? "'$raza'" : "NULL") . ",
                fecha_nac = " . ($fecha_nac ? "'$fecha_nac'" : "NULL") . ",
                sexo = " . ($sexo ? "'$sexo'" : "NULL") . ",
                foto = " . ($foto ? "'$foto'" : "NULL") . "
            WHERE id_paciente = $id_paciente";
    
    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje'] = "Mascota actualizada correctamente";
    } else {
        $_SESSION['error'] = "Error al actualizar la mascota: " . mysqli_error($conexion);
    }
}

// ═══════════════════════════════════════════════════════
// ELIMINAR PACIENTE (BORRADO LÓGICO)
// ═══════════════════════════════════════════════════════
else if ($accion == 'eliminar') {
    $id_paciente = $_POST['id_paciente'];
    
    // Borrado lógico: marcar como inactivo
    $sql = "UPDATE pacientes SET activo = 0 WHERE id_paciente = $id_paciente";
    
    if (mysqli_query($conexion, $sql)) {
        $_SESSION['mensaje'] = "Mascota eliminada correctamente";
    } else {
        $_SESSION['error'] = "Error al eliminar la mascota: " . mysqli_error($conexion);
    }
}

header("Location: pacientes.php");
exit();
?>
