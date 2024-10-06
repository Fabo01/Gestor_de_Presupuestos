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
    echo "Banco no encontrado o no tienes permiso para añadir categorías a este banco.";
    exit();
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $categoria = trim($_POST['categoria']);

    if (empty($categoria)) {
        echo "El nombre de la categoría no puede estar vacío.";
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) FROM Categoria WHERE ID_cuentabanco = ? AND nombre = ?");
        $stmt->bind_param('is', $ID_cuentabanco, $categoria);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo "Error: Ya existe una categoría con este nombre para el banco seleccionado.";
        } else {
            // Insertar la nueva categoría
            $stmt = $db->prepare("INSERT INTO Categoria (ID_cuentabanco, nombre) VALUES (?, ?)");
            $stmt->bind_param('is', $ID_cuentabanco, $categoria);

            if ($stmt->execute()) {
                header("Location: categorias.php?id_banco=" . $ID_cuentabanco . "&success=categoria");
                exit();
            } else {
                echo "Error al añadir la categoría.";
            }
            $stmt->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir nueva categoría al Banco <?php echo htmlspecialchars($nombre_banco); ?></title>
</head>
<body>
    <h1>Añadir nueva categoría al banco: <?php echo htmlspecialchars($nombre_banco); ?></h1>

    <form action="añadir_categoria_especifica.php?id_banco=<?php echo htmlspecialchars($ID_cuentabanco); ?>" method="POST">
        <label for="categoria">Nombre de la categoría:</label>
        <input type="text" name="categoria" id="categoria" placeholder="Nombre de la categoría" required><br><br>

        <button type="submit">Añadir categoría</button>
    </form>

    <a href="categorias.php?id_banco=<?php echo htmlspecialchars($ID_cuentabanco); ?>">Volver a Categorías del Banco</a>
</body>
</html>
