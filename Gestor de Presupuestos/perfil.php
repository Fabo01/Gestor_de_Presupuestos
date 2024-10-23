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

$stmt = $db->prepare("SELECT usuario, nombre, apellido, email, nacionalidad, foto FROM Usuarios WHERE ID_usuario = ?");
if (!$stmt) {
    error_log("Error al preparar la consulta: " . $db->error);
    $error_message = "Ocurrió un error al cargar tu perfil. Por favor, inténtalo de nuevo más tarde.";
} else {
    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar la consulta: " . $stmt->error);
        $error_message = "Ocurrió un error al cargar tu perfil. Por favor, inténtalo de nuevo más tarde.";
    } else {
        $result = $stmt->get_result();
        if ($row = $result->fetch_assoc()) {
            $username = htmlspecialchars($row['usuario']);
            $email = htmlspecialchars($row['email']);
            $nombre = htmlspecialchars($row['nombre']);
            $apellido = htmlspecialchars($row['apellido']);
            $nacionalidad = htmlspecialchars($row['nacionalidad']);
            $foto_perfil = htmlspecialchars($row['foto']);
        } else {
            $error_message = "Usuario no encontrado.";
        }
    }
    $stmt->close();
}

if (isset($error_message)) {
    echo "<p>$error_message</p>";
    exit();
}
?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Perfil de Usuario</title>
    <link rel="stylesheet" href="perfil.css">
</head>
<body>

<!-- Header -->
<header class="header">
    <div class="logo">
        Gestor de Presupuestos
    </div>
    <nav class="navbar">
        <ul>
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="bancos.php">Bancos</a></li>
            <li><a href="transacciones.php">Transacciones</a></li>
            <li><a href="logout.php">Cerrar Sesion</a></li>
        </ul>
    </nav>
    <!-- Botón de tres puntos para mostrar la barra lateral -->
    <button class="toggle-sidebar-btn-left" onclick="toggleSidebar()">&#x22EE;</button>
</header>

<!-- Barra lateral fuera de la página por defecto -->
<aside id="sidebar" class="sidebar">
    <ul>
        <li><a href="dashboard.php">Inicio</a></li>
        <li><a href="bancos.php">Tus Cuentas</a></li>
        <li><a href="categorias.php">Tus Categorias</a></li>
        <li><a href="articulos.php">Ver Articulos</a></li>
        <li><a href="estadisticas.php">Estadisticas</a></li>
        <li><a href="logros.php">Logros</a></li>
    </ul>
    <!-- Botón de tres puntos dentro de la barra lateral para ocultarla -->
    <button class="toggle-sidebar-btn-inside" onclick="toggleSidebar()">&#x22EE;</button>
</aside>

<!-- Sección Principal -->
<main>
    <div class="profile-container">
        <div class="profile-header">
            <img src="<?php echo htmlspecialchars($foto_perfil); ?>" alt="Avatar" onerror="this.src='https://via.placeholder.com/50';">
            <div class="profile-info">
                <h2><?php echo htmlspecialchars($username); ?></h2>
                <p>Email: <?php echo htmlspecialchars($email); ?></p>
            </div>
        </div>
    </div>

    <div class="content-sections">
        <div class="button-frame" onclick="toggleFrame('personalizacionFrame')">
            <button>Personalizacion</button>
        </div>
        <div id="personalizacionFrame" class="frame">
            <div class="frame-content">
                <form method="POST" action="guardar_color.php">
                    <label for="color">Color de botones:</label><br>
                    <select name="color" id="color">
                        <option value="celeste">Celeste</option>
                        <option value="rojo">Rojo</option>
                        <option value="verde">Verde</option>
                    </select><br><br>
                    <button type="submit">Guardar</button>
                </form>
            </div>
        </div>

        <div class="button-frame" onclick="toggleFrame('seguridadFrame')">
            <button>Seguridad</button>
        </div>
        <div id="seguridadFrame" class="frame">
            <div class="frame-content">
                <p>Opciones de seguridad...</p>
            </div>
        </div>

        <div class="button-frame" onclick="toggleFrame('logrosFrame')">
            <button>Logros</button>
        </div>
        <div id="logrosFrame" class="frame">
            <div class="frame-content">
                <p>Logros del usuario...</p>
            </div>
        </div>

        <div class="button-frame" onclick="toggleFrame('graficoFrame')">
            <button>Grafico</button>
        </div>
        <div id="graficoFrame" class="frame">
            <div class="frame-content">
                <p>Gráfico de ejemplo...</p>
            </div>
        </div>
    </div>
</main>

<!-- Cargar el archivo JavaScript -->
<script src="perfil.js"></script>

</body>
</html>
