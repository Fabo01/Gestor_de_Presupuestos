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
    echo "Presupuesto no encontrado.";
    exit();
}

$stmt = $db->prepare("SELECT `desc`, fecha, Monto FROM Transacciones WHERE ID_presupuesto = ?");
$stmt->bind_param('i', $ID_presupuesto);
$stmt->execute();
$result = $stmt->get_result();
$stmt->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Transacciones del Presupuesto</title>
</head>
<body>
    <h1>Transacciones del presupuesto</h1>
    <p>Gasto mensual: <?php echo htmlspecialchars($gasto_mensual); ?></p>
    <p>Saldo restante: <?php echo htmlspecialchars($saldo_restante); ?></p>

    <ul>
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                echo "<li>" . htmlspecialchars($row['fecha']) . " - " . htmlspecialchars($row['desc']) . ": $" . htmlspecialchars($row['Monto']) . "</li>";
            }
        } else {
            echo "<li>No hay transacciones asociadas a este presupuesto.</li>";
        }
        ?>
    </ul>

    <a href="añadir_transaccion.php?id_presupuesto=<?php echo htmlspecialchars($ID_presupuesto); ?>">Añadir nueva transacción</a>
    <a href="ver_presupuestos.php?id_categoria=<?php echo htmlspecialchars($_GET['id_categoria']); ?>">Volver a Presupuesto</a>
</body>
</html>
