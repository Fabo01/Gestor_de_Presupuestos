
<!-- AUN NO IMPLEMENTADO-->
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
        case 'categoria':
            $mensaje = "Categoría añadida correctamente.";
            break;
        case 'presupuesto':
            $mensaje = "Presupuesto añadido correctamente.";
            break;
        case 'transaccion':
            $mensaje = "Transacción añadida correctamente.";
            break;
        default:
            $mensaje = "";
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
<h2>Añadir una nueva categoría a una cuenta bancaria</h2>
    <form action="añadir_categoria.php" method="POST">
        <label for="cuenta">Selecciona la cuenta de banco:</label>
        <select name="cuenta" id="cuenta" required>

            <?php
            $stmt = $db->prepare("SELECT ID_cuentabanco, banco FROM Cuentas_de_banco WHERE ID_usuario = ?");
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<option value='" . htmlspecialchars($row['ID_cuentabanco']) . "'>" . htmlspecialchars($row['banco']) . "</option>";
            }
            $stmt->close();
            ?>

        </select><br><br>

        <label for="categoria">Nombre de la categoría:</label>
        <input type="text" name="categoria" id="categoria" placeholder="Nombre de la categoría" required><br><br>

        <button type="submit" name="submit_categoria">Añadir categoría</button>
    </form>

    </body>
    </html>