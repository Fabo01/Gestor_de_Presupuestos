<?php
require 'Conex.inc';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$mensaje = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'banco':
            $mensaje = "Banco añadido correctamente.";
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
        <input type="text" name="banco" placeholder="Nombre del banco" required>
        <button type="submit" name="submit_banco">Añadir banco</button>
    </form>

    </body>
</html>
