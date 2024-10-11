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

    <main>
    
        <h2>Categorías de: <?php echo htmlspecialchars($nombre_banco); ?></h1>
        <div class="container-gestion">
            <h3>Lista de Categorías</h3>
            <ul class="lista-categorias">
                <a class="btn btn-banco" href="añadir_categoria_especifica.php?id_banco=<?php echo htmlspecialchars($ID_cuentabanco); ?>">Añadir nueva categoría</a>
    <!-------------------------------------------- Codigo php -------------------------------------->
                <?php
                $stmt = $db->prepare("SELECT ID_categoria, nombre FROM Categoria WHERE ID_cuentabanco = ?");
                $stmt->bind_param('i', $ID_cuentabanco);
                $stmt->execute();
                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<li>
                            <strong>" . htmlspecialchars($row['nombre']) . "</strong>
                            <div class='btn-group horizontal-buttons'>
                                <a href='ver_presupuestos.php?id_categoria=" . htmlspecialchars($row['ID_categoria']) . "'>
                                <button class='btn btn-categorias'>Ver Presupuesto</button>
                                </a>
                            </div>
                        </li>";
                }
                $stmt->close();
                ?>
    <!---------------------------------------------------------------------------------------------------------------------------->
            </ul>

            <div class="button-group compact-buttons">
                <a href="dashboard.php" class="btn btn-banco">Regresar</a>
            </div>
        </div>
    </main>
    
    <footer>
        <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
    </footer>

    <script src="js/menu_lateral.js"></script>
</body>
</html>
