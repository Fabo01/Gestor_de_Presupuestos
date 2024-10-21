<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
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

$mensaje = '';
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'banco':
            $mensaje = "Banco añadido correctamente.";
            break;
        case 'categoria':
            $mensaje = "Categoría añadida correctamente.";
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
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

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
                        <a href="informacion.php">
                            <button class="btn btn-boletines">Ayuda</button>
                        </a>
                    </li>

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
            <li><a href="articulos.php">Ver Artículos</a></li>
            <li><a href="estadistica.php">Estadísticas</a></li>
            <li><a href="logros.php">Logros</a></li>
        </ul>
    </aside>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
    <?php if (!empty($mensaje)): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

    <main>
        <h2>Bienvenido, <?php echo htmlspecialchars($_SESSION['name']); ?></h2>
        <div class="container-gestion">

            <h3>Mis cuentas</h3>
            <ul class="lista-cuentas">

                <div class="btn-banco-container">
                    <a href="añadir_nuevo_banco.php">
                        <button class="btn-banco">Añadir Banco</button>
                    </a>
                </div> 

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                <?php
                $stmt = $db->prepare("SELECT ID_banco, banco, tipo, nombre FROM Cuentas_de_banco WHERE ID_usuario = ?");
                if (!$stmt) {
                    error_log("Error al preparar la consulta: " . $db->error);
                    echo "<p>Ocurrió un error al cargar tus cuentas. Por favor, inténtalo de nuevo más tarde.</p>";
                    exit();
                }

                $stmt->bind_param('i', $_SESSION['user_id']);
                if (!$stmt->execute()) {
                    error_log("Error al ejecutar la consulta: " . $stmt->error);
                    echo "<p>Ocurrió un error al cargar tus cuentas. Por favor, inténtalo de nuevo más tarde.</p>";
                    exit();
                }

                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<li>
                            <strong>" . htmlspecialchars($row['banco'] ?? '', ENT_QUOTES, 'UTF-8') . " - " . htmlspecialchars($row['tipo'] ?? '', ENT_QUOTES, 'UTF-8') . " (" . htmlspecialchars($row['nombre'] ?? '', ENT_QUOTES, 'UTF-8') . ")</strong>
                            <div class='btn-group'>
                                <a href='ver_categorias.php?id_banco=" . htmlspecialchars($row['ID_banco'] ?? '', ENT_QUOTES, 'UTF-8') . "'>
                                    <button class='btn-categorias'>Ver Categorías</button>
                                </a>
                            </div>
                        </li>";
                }
                $stmt->close();                
                ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

            </ul>

            <h3>Mis Categorías</h3>
            <ul class="lista-categorias">
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                <?php
                $stmt = $db->prepare("SELECT Categorias.ID_categoria, Categorias.nombre AS categoria_nombre, Cuentas_de_banco.banco, Cuentas_de_banco.tipo, Cuentas_de_banco.nombre AS cuenta_nombre 
                                    FROM Categorias
                                    INNER JOIN Cuentas_de_banco ON Categorias.ID_usuario = Cuentas_de_banco.ID_usuario
                                    WHERE Cuentas_de_banco.ID_usuario = ? ");
                if (!$stmt) {
                    error_log("Error al preparar la consulta: " . $db->error);
                    echo "<p>Ocurrió un error al cargar tus categorías. Por favor, inténtalo de nuevo más tarde.</p>";
                    exit();
                }

                $stmt->bind_param('i', $_SESSION['user_id']);
                if (!$stmt->execute()) {
                    error_log("Error al ejecutar la consulta: " . $stmt->error);
                    echo "<p>Ocurrió un error al cargar tus categorías. Por favor, inténtalo de nuevo más tarde.</p>";
                    exit();
                }

                $result = $stmt->get_result();

                while ($row = $result->fetch_assoc()) {
                    echo "<li>" . htmlspecialchars($row['nombre_cuenta']) . ": " . htmlspecialchars($row['nombre_banco']) . "(" . htmlspecialchars($row['tipo_cuenta']) . ") - Categoría: " . htmlspecialchars($row['nombre']) . "
                            <div class='btn-group'>
                                <a href='gestionar_transaccion.php?id_categoria=" . htmlspecialchars($row['ID_categoria'], ENT_QUOTES, 'UTF-8') . "'>
                                    <button class='btn-categorias'>Gestionar</button>
                                </a>
                            </div>
                        </li>";     
                }
                $stmt->close();
                ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
            </ul>
        </div>
    </main>

    <footer>
        <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
    </footer>
    
    <script src="js/menu_lateral.js"></script>
</body>
</html>
