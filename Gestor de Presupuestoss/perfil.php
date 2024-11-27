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

$stmt = $db->prepare("SELECT usuario, nombre, apellido, email, nacionalidad, nacimiento, foto FROM Usuarios WHERE ID_usuario = ?");
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
            $nacimiento = htmlspecialchars($row['nacimiento']);
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
    <title>Noticias Tecnologicas</title>
    <link rel="stylesheet" href="CSS/perfil.css">
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
                        <span><?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
                    </div>
                </li>
                <li><a href="perfil.php">Perfil</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
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
            </ul>
        </aside>

    <main>

    <div class="dashboard-container">

        <section id="perfil">
            <div class="perfil-container">
                <h2>Perfil de Usuario</h2>
                <div class="perfil-content">
                    <img src="img/user.jpg" alt="Imagen de perfil" class="perfil-img" id="perfil-img">
                    <div id="image-menu" class="image-menu hidden">
                        <img src="img/opcion1.jpg" alt="Opción 1" class="menu-img">
                        <img src="img/opcion2.jpg" alt="Opción 2" class="menu-img">
                        <img src="img/opcion3.jpg" alt="Opción 3" class="menu-img">
                    </div>
                    <div class="detalles-perfil">
                        <h4>Usuario: <?php echo htmlspecialchars($_SESSION['usuario']); ?></h4>
                        <p>Nombre: <?php echo $nombre; ?></p>
                        <p>Apellido: <?php echo $apellido; ?></p>
                        <p>Email: <?php echo $email; ?></p>
                        <p>Nacionalidad: <?php echo $nacionalidad; ?></p>
                        <p>Fecha de Nacimiento: <?php echo $nacimiento; ?></p>
                    </div>
                </div>

            </div>
        </section>

        <div class="content-navigation">
            <!-- Botón 1: Personalización -->
            <div class="button-frame" onclick="toggleFrame('personalizacionFrame')">
                <button>Personalización</button>
            </div>
        
            <!-- Botón 2: Seguridad -->
            <div class="button-frame" onclick="toggleFrame('seguridadFrame')">
                <button>Seguridad</button>
            </div>
        
        </div>
        
        <div class="content-sections">
            <!-- Frame: Personalización -->
            <div id="personalizacionFrame" class="frame">
                <h2>Personalización</h2>
                <div class="frame-content">
                    <button class="boton-perfil" id="theme-toggle">Modo Oscuro</button>
                </div>
            </div>
        
            <!-- Frame: Seguridad -->
            <div id="seguridadFrame" class="frame">
                <h2>Seguridad</h2>
                <div class="frame-content">
                    <p>Aquí puedes configurar las opciones de seguridad de tu cuenta:</p>
                    <ul>
                        <li><button class="boton-perfil" onclick="alert('Cambiar contraseña')">Cambiar contraseña</button></li>
                        <li><button class="boton-perfil" id="boton-eliminar" onclick="alert('Eliminar la cuenta')">Eliminar la Cuenta</button> <small>!Perderas todo tu progreso!</small> </li>
                    </ul>
                </div>
            </div>
        </div>
        
        
        
    </main>

    <footer>

        <div class="container">
            <p>&copy; Interesante 2024. Todos los derechos reservados.</p>
        </div>
    </footer>

    <script src="JS/menu_lateral.js"></script>
    <script src="JS/perfil.js"></script>

</body>
</html>