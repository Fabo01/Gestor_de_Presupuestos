<!-------------------------------------------- Codigo php -------------------------------------->
<?php
require 'Conex.inc';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id_banco'])) {
    header('Location: dashboard.php?error=banco_no_seleccionado');
    exit();
}

$ID_cuentabanco = $_GET['id_banco'];

$stmt = $db->prepare("SELECT banco FROM Cuentas_de_banco WHERE ID_cuentabanco = ? AND ID_usuario = ?");
$stmt->bind_param('ii', $ID_cuentabanco, $_SESSION['user_id']);
$stmt->execute();
$stmt->bind_result($nombre_banco);
$stmt->fetch();
$stmt->close();

if (!$nombre_banco) {
    echo "Banco no encontrado o no tienes permiso para ver este banco.";
    exit();
}

?>
<!---------------------------------------------------------------------------------------------------------------------------->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías del Banco <?php echo htmlspecialchars($nombre_banco); ?></title>
</head>
<body>
    <h1>Categorías asociadas al banco: <?php echo htmlspecialchars($nombre_banco); ?></h1>

    <p><a href="añadir_categoria_especifica.php?id_banco=<?php echo htmlspecialchars($ID_cuentabanco); ?>">Añadir nueva categoría</a></p>

    <ul>

<!-------------------------------------------- Codigo php -------------------------------------->
        <?php
        $stmt = $db->prepare("SELECT ID_categoria, nombre FROM Categoria WHERE ID_cuentabanco = ?");
        $stmt->bind_param('i', $ID_cuentabanco);
        $stmt->execute();
        $result = $stmt->get_result();

        while ($row = $result->fetch_assoc()) {
            echo "<li><a href='ver_presupuestos.php?id_categoria=" . htmlspecialchars($row['ID_categoria']) . "'>" . htmlspecialchars($row['nombre']) . "</a></li>";
        }
        $stmt->close();
        ?>
<!---------------------------------------------------------------------------------------------------------------------------->

    </ul>

    <a href="dashboard.php">Volver al Dashboard</a>
</body>
</html>
