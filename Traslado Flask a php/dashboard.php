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

    <header class="navbar">
        <button id="menu-btn" class="menu-btn">&#9776;</button>
            <div class="logo">
                Gestor de Presupuestos
            </div>

            <nav class="nav">
                <ul>
                    <li>
                        <a href="#">
                            <button class="btn btn-boletines">Boletines</button>
                        </a>
                    </li>

                    <li>
                        <div class="user-dropdown">
                            <img src="img/user.jpg" alt="Perfil" class="user-avatar">
                            <span>Usuario: <?php echo htmlspecialchars($_SESSION['user']); ?></span>
                        </div>
                    </li>

                    <li><a href="#">Perfil</a></li>
                    <li><a href="logout.php">Cerrar Sesión</a></li>
                </ul>
            </nav>
    </header>

    <aside id="sidebar" class="sidebar">
        <button id="close-btn" class="close-btn">&times;</button>

        <ul>
            <li><a href="#">Ver Artículos</a></li>
            <li><a href="#">Estadisticas</a></li>
            <li><a href="#">Logros</a></li>
        </ul>
    </aside>

<!-------------------------------------------- Codigo php -------------------------------------->

    <?php if (!empty($mensaje)): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
<!---------------------------------------------------------------------------------------------------------------------------->

    <main>
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['user']); ?></h2>
        <div class="container-gestion">

            <h3>Mis cuentas</h3>
            <ul class="lista-cuentas">

                <div class="btn-banco-container">
                    <a href="añadir_nuevo_banco.php">
                        <button class="btn btn-banco">Añadir Banco</button>
                    </a>
                </div> 
<!-------------------------------------------- Codigo php -------------------------------------->
                    <?php
                    $stmt = $db->prepare("SELECT ID_cuentabanco, banco FROM Cuentas_de_banco WHERE ID_usuario = ?");
                    $stmt->bind_param('i', $_SESSION['user_id']);
                    $stmt->execute();
                    $result = $stmt->get_result();

                    while ($row = $result->fetch_assoc()) {
                        echo "<li>
                                <strong>" . htmlspecialchars($row['banco']) . "</strong>
                                <div class='btn-group'>
                                    <a href='ver_categorias.php?id_banco=" . htmlspecialchars($row['ID_cuentabanco']) . "'>
                                        <button class='btn btn-categorias'>Ver Categorías</button>
                                    </a>
                                </div>
                            </li>";
                    }
                    $stmt->close();
                    ?>
<!---------------------------------------------------------------------------------------------------------------------------->
            </ul>

            <h3>Categorías</h3>
            <ul class="lista-categorias">
<!-------------------------------------------- Codigo php -------------------------------------->
                <?php
                $stmt = $db->prepare("SELECT Categoria.nombre, Cuentas_de_banco.banco FROM Categoria 
                                    INNER JOIN Cuentas_de_banco ON Categoria.ID_cuentabanco = Cuentas_de_banco.ID_cuentabanco 
                                    WHERE Cuentas_de_banco.ID_usuario = ? ");
                $stmt->bind_param('i', $_SESSION['user_id']);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<li>Cuenta: " . htmlspecialchars($row['banco']) . " - Categoría: " . htmlspecialchars($row['nombre']) . "
                            <div class='btn-group'>
                                <a href=''>
                                    <button class='btn btn-categorias'>Asignar Presupuesto</button>
                                </a>
                                <a href=''>
                                    <button class='btn btn-categorias'>Agregar Transacción</button>
                                </a>
                                <a href=''>
                                    <button class='btn btn-categorias'>Ver Presupuesto</button>
                                </a>
                            </div>
                         </li>";     
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
    
    <script src="js/menu_lateral.js"></script>
</body>
</html>