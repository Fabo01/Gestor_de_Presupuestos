<!-------------------------------------------- Codigo php -------------------------------------->
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
<!---------------------------------------------------------------------------------------------------------------------------->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <header>
            <div class="perfil">
                <img src="img/user.jpg" alt="Perfil">
                <p>Usuario: <?php echo htmlspecialchars($_SESSION['user']); ?></p>
            </div>
            <h1>Gestor de Presupuestos</h1>
            <nav class="inicio">
                <ul>
                    <li><a href="#">Ver articulos</a></li>
                    <li><a href="#">Perfil</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
        </header>

<!-------------------------------------------- Codigo php -------------------------------------->

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
<!---------------------------------------------------------------------------------------------------------------------------->

    <main>
        <h2>Mis Cuentas</h2>
        <div class="container-cuentas">
            <a href="añadir_nuevo_banco.php">Añadir Banco</a>
            <ul>

<!-------------------------------------------- Codigo php -------------------------------------->
            <?php
            $stmt = $db->prepare("SELECT ID_cuentabanco, banco FROM Cuentas_de_banco WHERE ID_usuario = ?");
            $stmt->bind_param('i', $_SESSION['user_id']);
            $stmt->execute();
            $result = $stmt->get_result();

            while ($row = $result->fetch_assoc()) {
                echo "<li><p>" . htmlspecialchars($row['banco']) . " <a href='ver_categorias.php?id_banco=" . htmlspecialchars($row['ID_cuentabanco']) . "'>Ver Categorias</a> </p></li>";
            }
            $stmt->close();
            ?>
<!---------------------------------------------------------------------------------------------------------------------------->

            </ul>
        </div>

        <h2>Mis categorías</h2>
        <div class="container-categorias">
            <ul>

<!-------------------------------------------- Codigo php -------------------------------------->
                <?php
                $stmt = $db->prepare("SELECT Categoria.nombre, Cuentas_de_banco.banco FROM Categoria 
                                    INNER JOIN Cuentas_de_banco ON Categoria.ID_cuentabanco = Cuentas_de_banco.ID_cuentabanco 
                                    WHERE Cuentas_de_banco.ID_usuario = ? ");
                $stmt->bind_param('i', $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<li>Cuenta: " . htmlspecialchars($row['banco']) . " - Categoría: " . htmlspecialchars($row['nombre']) . "</li>";
                }
                $stmt->close();
                ?>
<!---------------------------------------------------------------------------------------------------------------------------->
            </ul>
        </div>
    </main>
    <footer>
        <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
    </footer>
</body>
</html>
