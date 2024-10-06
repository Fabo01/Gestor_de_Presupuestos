<?php
require 'Conex.inc';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id_presupuesto'])) {
    header('Location: dashboard.php?error=presupuesto_no_seleccionado');
    exit();
}

$ID_presupuesto = $_GET['id_presupuesto'];

$stmt = $db->prepare("SELECT gasto_mensual, saldo_restante FROM Presupuestos WHERE ID_presupuesto = ?");
$stmt->bind_param('i', $ID_presupuesto);
$stmt->execute();
$stmt->bind_result($gasto_mensual, $saldo_restante);
$stmt->fetch();
$stmt->close();

if (!$gasto_mensual) {
    echo "Presupuesto no encontrado o no tienes permiso para añadir transacciones.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $descripcion = trim($_POST['descripcion']);
    $monto = floatval($_POST['monto']);

    if (empty($descripcion) || $monto <= 0) {
        echo "La descripción no puede estar vacía y el monto debe ser un número positivo.";
    }else {
        $stmt = $db->prepare("INSERT INTO Transacciones (ID_presupuesto, `desc`, fecha, Monto) VALUES (?, ?, NOW(), ?)");
        $stmt->bind_param('isi', $ID_presupuesto, $descripcion, $monto);

        if ($stmt->execute()) {
            $nuevo_saldo = $saldo_restante - $monto;
            $stmt = $db->prepare("UPDATE Presupuestos SET saldo_restante = ? WHERE ID_presupuesto = ?");
            $stmt->bind_param('ii', $nuevo_saldo, $ID_presupuesto);
            $stmt->execute();
            $stmt->close();

            header("Location: transacciones.php?id_presupuesto=" . $ID_presupuesto . "&success=transaccion");
            exit();
        } else {
            echo "Error al añadir la transacción.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Transacción al Presupuesto</title>
</head>
<body>
    <h1>Añadir Transacción al Presupuesto</h1>
    <p>Gasto mensual: <?php echo htmlspecialchars($gasto_mensual); ?></p>
    <p>Saldo restante: <?php echo htmlspecialchars($saldo_restante); ?></p>

    <form action="añadir_transaccion.php?id_presupuesto=<?php echo htmlspecialchars($ID_presupuesto); ?>" method="POST">
        <label for="descripcion">Descripción:</label>
        <input type="text" name="descripcion" id="descripcion" required><br><br>

        <label for="monto">Monto:</label>
        <input type="number" name="monto" id="monto" min="1" required><br><br>

        <button type="submit">Añadir transacción</button>
    </form>

    <a href="transacciones.php?id_presupuesto=<?php echo htmlspecialchars($ID_presupuesto); ?>">Volver a Transacciones</a>
</body>
</html>
