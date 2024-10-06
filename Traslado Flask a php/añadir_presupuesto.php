<!-------------------------------------------- Codigo php -------------------------------------->
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
    echo "Categoría no encontrada o no tienes permiso para añadir un presupuesto a esta categoría.";
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gasto_mensual = $_POST['gasto_mensual'];
    $saldo_restante = $gasto_mensual;

    if (!is_numeric($gasto_mensual) || $gasto_mensual <= 0) {
        echo "El gasto mensual debe ser un número positivo.";
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) FROM Presupuestos WHERE ID_categoria = ?");
        $stmt->bind_param('i', $ID_categoria);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo "Error: Esta categoría ya tiene un presupuesto asignado.";
        } else {
            $stmt = $db->prepare("INSERT INTO Presupuestos (ID_categoria, gasto_mensual, saldo_restante) VALUES (?, ?, ?)");
            $stmt->bind_param('iii', $ID_categoria, $gasto_mensual, $saldo_restante);

            if ($stmt->execute()) {
                header("Location: presupuestos.php?id_categoria=" . $ID_categoria . "&success=presupuesto");
                exit();
            } else {
                echo "Error al añadir el presupuesto.";
            }
            $stmt->close();
        }
    }
}
?>
<!---------------------------------------------------------------------------------------------------------------------------->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir Presupuesto a la Categoría <?php echo htmlspecialchars($nombre_categoria); ?></title>
</head>
<body>
    <h1>Añadir Presupuesto a la Categoría: <?php echo htmlspecialchars($nombre_categoria); ?></h1>

    <form action="añadir_presupuesto.php?id_categoria=<?php echo htmlspecialchars($ID_categoria); ?>" method="POST">
        <label for="gasto_mensual">Gasto mensual:</label>
        <input type="number" name="gasto_mensual" id="gasto_mensual" min="1" required><br><br>

        <button type="submit">Añadir presupuesto</button>
    </form>

    <a href="presupuestos.php?id_categoria=<?php echo htmlspecialchars($ID_categoria); ?>">Volver a Presupuesto de Categoría</a>
</body>
</html>
