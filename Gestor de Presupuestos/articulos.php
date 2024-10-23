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

$user_id = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
if (!$user_id) {
    session_destroy();
    header('Location: index.php');
    exit();
}

$articulos_por_pagina = 5;

// Obtener el número total de artículos
$stmt_total = $db->prepare("SELECT COUNT(*) FROM Articulos");
if (!$stmt_total) {
    error_log("Error al preparar la consulta: " . $db->error);
    $error_message = "Ocurrió un error al cargar los artículos. Por favor, inténtalo de nuevo más tarde.";
} else {
    if (!$stmt_total->execute()) {
        error_log("Error al ejecutar la consulta: " . $stmt_total->error);
        $error_message = "Ocurrió un error al cargar los artículos. Por favor, inténtalo de nuevo más tarde.";
    } else {
        $stmt_total->bind_result($total_articulos);
        $stmt_total->fetch();
        $stmt_total->close();

        $total_paginas = ceil($total_articulos / $articulos_por_pagina);

        $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
        if ($pagina_actual < 1) $pagina_actual = 1;
        if ($pagina_actual > $total_paginas) $pagina_actual = $total_paginas;

        $offset = ($pagina_actual - 1) * $articulos_por_pagina;

        $stmt = $db->prepare("
            SELECT A.ID_articulo, A.titulo, A.contenido, A.fecha_creacion, U.usuario
            FROM Articulos A
            INNER JOIN Usuarios U ON A.ID_usuario = U.ID_usuario
            ORDER BY A.fecha_creacion DESC
            LIMIT ?, ?
        ");
        if (!$stmt) {
            error_log("Error al preparar la consulta: " . $db->error);
            $error_message = "Ocurrió un error al cargar los artículos. Por favor, inténtalo de nuevo más tarde.";
        } else {
            $stmt->bind_param('ii', $offset, $articulos_por_pagina);
            if (!$stmt->execute()) {
                error_log("Error al ejecutar la consulta: " . $stmt->error);
                $error_message = "Ocurrió un error al cargar los artículos. Por favor, inténtalo de nuevo más tarde.";
            } else {
                $result = $stmt->get_result();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Foro</title>
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/articulos.css">
</head>
<body>
    <header class="navbar">
    <?php if (isset($_SESSION['user_id'])): ?>
        <button id="menu-btn" class="menu-btn">&#9776;</button>
        <?php endif; ?>
        <div class="logo">Gestor de Presupuestos</div>
        <nav class="nav">
            <ul>
                <!-- Verificamos si el usuario ha iniciado sesión -->
                <?php if (isset($_SESSION['user_id'])): ?>
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
                <li>
                    <a href="perfil.php">
                        <button class="btn btn-perfil">Perfil</button>
                    </a>
                </li>
                <li> 
                    <a href="logout.php">
                        <button class="btn btn-logout">Cerrar Sesión</button>
                    </a></li>
                <?php else: ?>
                    <li><a href="index.php">Iniciar Sesión</a></li>
                <?php endif; ?>
            </ul>
        </nav>
    </header>

    <aside id="sidebar" class="sidebar">
        <button id="close-btn" class="close-btn">&times;</button>

        <ul>
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="bancos.php">Tus Cuentas</a></li>
            <li><a href="categorias.php">Tus Categorías</a></li>
            <li><a href="articulos.php">Ver Artículos</a></li>
            <li><a href="estadisticas.php">Estadísticas</a></li>
            <li><a href="logros.php">Logros</a></li>
        </ul>
    </aside>

    <nav class="nav-foro">
        <ul>
            <li><a href="crear_articulo.php">Crear Artículo</a></li>
            <li><a href="mis_articulos.php">Mis Artículos</a></li>
        </ul>
    </nav>

    <main class="main-articulo">
        <section id="articulos">
            <h2>Artículos Recientes</h2>

            <?php
            if (isset($error_message)) {
                echo "<p>$error_message</p>";
            } else {
                while ($row = $result->fetch_assoc()) {
                    // Formatear la fecha
                    $fecha_formateada = date('d/m/Y H:i', strtotime($row['fecha_creacion']));

                    echo "<article>
                            <h3>" . htmlspecialchars($row['titulo']) . "</h3>
                            <p>" . nl2br(htmlspecialchars(substr($row['contenido'], 0, 200))) . "...</p>
                            <small>Escrito por: " . htmlspecialchars($row['usuario']) . " el " . htmlspecialchars($fecha_formateada) . "</small>
                            <br>
                            <a href='ver_articulo.php?id_articulo=" . urlencode($row['ID_articulo']) . "'>Leer más</a>
                        </article>
                        <hr>";
                }
                $stmt->close();

                echo '<div class="paginacion">';
                if ($pagina_actual > 1) {
                    echo '<a href="articulos.php?pagina=' . ($pagina_actual - 1) . '">&laquo; Anterior</a>';
                }

                for ($i = 1; $i <= $total_paginas; $i++) {
                    if ($i == $pagina_actual) {
                        echo '<span class="pagina-actual">' . $i . '</span>';
                    } else {
                        echo '<a href="articulos.php?pagina=' . $i . '">' . $i . '</a>';
                    }
                }

                if ($pagina_actual < $total_paginas) {
                    echo '<a href="articulos.php?pagina=' . ($pagina_actual + 1) . '">Siguiente &raquo;</a>';
                }
                echo '</div>';
            }
            ?>
        </section>
    </main>
    <footer>
        <p>&copy; 2024 Foro de Artículos Informativos. Todos los derechos reservados.</p>
    </footer>

    <script src="js/menu_lateral.js"></script>
</body>
</html>
