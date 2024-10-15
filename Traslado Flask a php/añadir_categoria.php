<?php
require 'Conex.inc';
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

if (!isset($_GET['id_banco'])) {
    header('Location: dashboard.php');
    exit();
}

$ID_cuentabanco = $_GET['id_banco'];

$stmt = $db->prepare("SELECT nombre_banco FROM Cuentas_de_banco WHERE ID_cuentabanco = ? AND ID_usuario = ?");
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
    $presupuesto = trim($_POST['presupuesto']);

    if (empty($categoria) || empty($presupuesto)) {
        echo "El nombre de la categoría y el presupuesto no pueden estar vacíos.";
    } elseif (!is_numeric($presupuesto) || $presupuesto <= 0) {
        echo "El presupuesto debe ser un número mayor que 0.";
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) FROM Categoria WHERE ID_cuentabanco = ? AND nombre = ?");
        $stmt->bind_param('is', $ID_cuentabanco, $categoria);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            echo "Error: Ya existe una categoría con este nombre.";
        } else {
            $stmt = $db->prepare("INSERT INTO Categoria (ID_cuentabanco, nombre, presupuesto_mensual, saldo_restante) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('isii', $ID_cuentabanco, $categoria, $presupuesto, $presupuesto);

            if ($stmt->execute()) {
                header("Location: ver_categorias.php?id_banco=" . $ID_cuentabanco . "&success=categoria");
                exit();
            } else {
                echo "Error al añadir la categoría.";
            }
            $stmt->close();
        }
    }
}
?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Añadir nueva categoría al Banco <?php echo htmlspecialchars($nombre_banco); ?></title>
    <link rel="stylesheet" href="CSS/intro_datos.css">
</head>
<body>

    <header class="navbar">
        <button id="menu-btn" class="menu-btn">&#9776;</button>
        <div class="logo">Gestor de Presupuestos</div>
        <nav class="nav">
            <ul>
                <li><a href="#"><button class="btn btn-boletines">Boletines</button></a></li>
                <li>
                    <div class="user-dropdown">
                        <img src="img/user.jpg" alt="Perfil" class="user-avatar">
                        <span>Usuario: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
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
            <li><a href="#">Estadísticas</a></li>
            <li><a href="#">Logros</a></li>
        </ul>
    </aside>

    <main>
        <div class="container-gestion compact">
            <h2><?php echo htmlspecialchars($nombre_banco); ?> - Crear Nueva Categoría</h2>

            <form action="añadir_categoria.php?id_banco=<?php echo htmlspecialchars($ID_cuentabanco); ?>" method="POST" class="form-style">
                <div class="form-group">
                    <label for="categoria">Nombre de la categoría:</label>
                    <input type="text" name="categoria" id="categoria" placeholder="Nombre de la categoría" required><br><br>
                </div>

                <div class="form-group">
                    <label for="presupuesto">Presupuesto de la categoría:</label>
                    <input type="number" name="presupuesto" id="presupuesto" placeholder="Presupuesto de la categoría" required><br><br>
                </div>

                <div class="button-group compact-buttons">
                    <button type="submit" class="btn btn-categorias">Añadir categoría</button>
                </div>
            </form>

            <div class="button-group compact-buttons">
                <a href="ver_categorias.php?id_banco=<?php echo htmlspecialchars($ID_cuentabanco); ?>" class="btn btn-banco">Regresar</a>
            </div>
        </div>
    </main>

    <footer>
        <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
    </footer>

<script src="JS/menu_lateral.js"></script>
</body>
</html>
