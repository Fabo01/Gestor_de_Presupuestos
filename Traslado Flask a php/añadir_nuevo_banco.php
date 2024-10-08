<?php
require 'Conex.inc';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$mensaje = '';//para confirmacion
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'banco':
            $mensaje = "Banco añadido correctamente.";
            break;
    }
//validacion de errores
} elseif (isset($_GET['error'])) {
    switch ($_GET['error']) {
        case 'banco_vacio':
            $mensaje = "El campo del banco no puede estar vacío.";
            break;
        case 'banco_existente':
            $mensaje = "El banco ya está registrado.";
            break;
        case 'fallo_bd':
            $mensaje = "Error al añadir el banco. Intenta de nuevo.";
            break;
        case 'acceso_denegado':
            $mensaje = "Acceso denegado. Por favor, inicia sesión.";
            break;
    }
}


?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <h1>Bienvenido, Usuario ID: <?php echo htmlspecialchars($_SESSION['user']); ?></h1>

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <h2>Añadir un nuevo banco</h2>
    <form action="añadir_banco.php" method="POST">
        <label for="banco">Nombre del Banco:</label>
        <input type="text" id="banco" name="banco" required>
        <button type="submit" name="submit_banco">Añadir Banco</button>
    </form>

    <a href="dashboard.php">Volver al Dashboard</a>
</body>
</html>
