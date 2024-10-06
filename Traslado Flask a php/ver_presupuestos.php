<?php
require 'Conex.inc';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id_categoria'])) {
    header('Location: dashboard.php?error=categoria_no_seleccionada');
    exit();
}

$ID_categoria = $_GET['id_categoria'];

$stmt = $db->prepare("SELECT nombre FROM Categoria WHERE ID_categoria = ?");
$stmt->bind_param('i', $ID_categoria);
$stmt->execute();
$stmt->bind_result($nombre_categoria);
$stmt->fetch();
$stmt->close();

if (!$nombre_categoria) {
    echo "Categoría no encontrada.";
    exit();
}

$stmt = $db->prepare("SELECT ID_presupuesto, gasto_mensual, saldo_restante FROM Presupuestos WHERE ID_categoria = ?");
$stmt->bind_param('i', $ID_categoria);
$stmt->execute();
$result = $stmt->get_result();
$presupuesto = $result->fetch_assoc();
$stmt->close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Presupuesto de la Categoría <?php echo htmlspecialchars($nombre_categoria); ?></title>
</head>
<body>
    <h1>Presupuesto para la categoría: <?php echo htmlspecialchars($nombre_categoria); ?></h1>

    <?php if ($presupuesto): ?>
        <p>Gasto mensual: <?php echo htmlspecialchars($presupuesto['gasto_mensual']); ?></p>
        <p>Saldo restante: <?php echo htmlspecialchars($presupuesto['saldo_restante']); ?></p>
        <p><a href="ver_transacciones.php?id_presupuesto=<?php echo htmlspecialchars($presupuesto['ID_presupuesto']); ?>">Ver transacciones de este presupuesto</a></p>
    <?php else: ?>
        <p>Esta categoría no tiene un presupuesto asignado.</p>
        <p><a href="añadir_presupuesto.php?id_categoria=<?php echo htmlspecialchars($ID_categoria); ?>">Añadir presupuesto a esta categoría</a></p>
    <?php endif; ?>

    <a href="ver_categorias.php?id_banco=<?php echo htmlspecialchars($_GET['id_banco']); ?>">Volver a Categorías</a>
</body>
</html>
