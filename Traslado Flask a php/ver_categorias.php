<?php
require 'Conex.inc';

session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$ID_cuentabanco = filter_input(INPUT_GET, 'id_banco', FILTER_VALIDATE_INT);

if (!$ID_cuentabanco) {
    header('Location: dashboard.php');
    exit();
}

$stmt = $db->prepare("SELECT nombre_banco FROM Cuentas_de_banco WHERE ID_cuentabanco = ? AND ID_usuario = ?");
if (!$stmt) {
    error_log("Error al preparar la consulta: " . $db->error);
    echo "Ocurrió un error al cargar los datos. Por favor, inténtalo de nuevo más tarde.";
    exit();
}

$stmt->bind_param('ii', $ID_cuentabanco, $_SESSION['user_id']);
if (!$stmt->execute()) {
    error_log("Error al ejecutar la consulta: " . $stmt->error);
    echo "Ocurrió un error al cargar los datos. Por favor, inténtalo de nuevo más tarde.";
    exit();
}

$stmt->bind_result($nombre_banco);
if (!$stmt->fetch()) {
    echo "Banco no encontrado o no tienes permiso para ver este banco.";
    exit();
}
$stmt->close();
?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Categorías del Banco <?php echo htmlspecialchars($nombre_banco); ?></title>
    <link rel="stylesheet" href="CSS/style.css">
</head>
<body>

<header class="navbar">
    <button id="menu-btn" class="menu-btn">&#9776;</button>
    <div class="logo">Gestor de Presupuestos</div>
    <nav class="nav">
        <ul>
            <li><a href="articulos.php"><button class="btn btn-boletines">Boletines</button></a></li>
            <li>
                <div class="user-dropdown">
                    <img src="img/user.jpg" alt="Perfil" class="user-avatar">
                    <span>Usuario: <?php echo htmlspecialchars($_SESSION['username']); ?></span>
                </div>
            </li>
            <li><a href="ver_perfil.php">Perfil</a></li>
            <li><a href="logout.php">Cerrar Sesión</a></li>
        </ul>
    </nav>
</header>

<aside id="sidebar" class="sidebar">
    <button id="close-btn" class="close-btn">&times;</button>
    <ul>
        <li><a href="dashboard.php">Inicio</a></li>
        <li><a href="articulos.php">Ver Artículos</a></li>
        <li><a href="estadistica.php">Estadísticas</a></li>
        <li><a href="logros.php">Logros</a></li>
    </ul>
</aside>

<main>
    <h2>Categorías de: <?php echo htmlspecialchars($nombre_banco); ?></h2>
    <div class="container-gestion">
        <h3>Lista de Categorías</h3>
        <ul class="lista-categorias">
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
            <?php
            $stmt = $db->prepare("SELECT ID_categoria, nombre, presupuesto_mensual, gasto_acumulado, saldo_restante FROM Categoria WHERE ID_cuentabanco = ?");
            if (!$stmt) {
                error_log("Error al preparar la consulta: " . $db->error);
                echo "<p>Ocurrió un error al cargar las categorías. Por favor, inténtalo de nuevo más tarde.</p>";
                exit();
            }

            $stmt->bind_param('i', $ID_cuentabanco);
            if (!$stmt->execute()) {
                error_log("Error al ejecutar la consulta: " . $stmt->error);
                echo "<p>Ocurrió un error al cargar las categorías. Por favor, inténtalo de nuevo más tarde.</p>";
                exit();
            }

            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                    echo "<li>
                            <strong>" . htmlspecialchars($row['nombre']) . "</strong>
                            <div class='btn-group horizontal-buttons'>
                                <a href='gestionar_transaccion.php?id_categoria=" . htmlspecialchars($row['ID_categoria'], ENT_QUOTES, 'UTF-8') . "'>
                                    <button class='btn btn-categorias'>Gestionar Transacciones</button>
                                </a>
                                <a href='asignar_presupuesto.php?id_categoria=" . htmlspecialchars($row['ID_categoria'], ENT_QUOTES, 'UTF-8') . "'>
                                    <button class='btn btn-categorias'>Asignar Presupuesto</button>
                                </a>
                                <a href='ver_presupuesto.php?id_categoria=" . htmlspecialchars($row['ID_categoria'], ENT_QUOTES, 'UTF-8') . "'>
                                    <button class='btn btn-categorias'>Ver Presupuesto</button>
                                </a>
                            </div>
                        </li>";
                }
            } else {
                echo "<li>No hay categorías para este banco.</li>";
            }
            $stmt->close();
            ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
        </ul>

        <div class="button-group compact-buttons">
            <a href="añadir_categoria.php?id_banco=<?php echo htmlspecialchars($ID_cuentabanco, ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-banco">Añadir nueva categoría</a>
        </div>

        <div class="button-group compact-buttons">
            <a href="dashboard.php" class="btn btn-banco">Regresar</a>
        </div>
    </div>
</main>

<footer>
    <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
</footer>

<script src="JS/menu_lateral.js"></script>
</body>
</html>
