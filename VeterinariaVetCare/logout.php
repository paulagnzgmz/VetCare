<?php
// =============================================
// logout.php — Cerrar sesion
// Destruye todos los datos de la sesion
// y manda al usuario al login
// =============================================

session_start();

// Destruir todos los datos de la sesion
session_destroy();

// Volver al login
header("Location: login.php");
exit();
?>
