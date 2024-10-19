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
        }
    }
?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Dashboard</title>
    <link rel="stylesheet" href="style.css">
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
            <li><a href="dashboard.php">Inicio</a></li>
            <li><a href="articulos.php">Ver Articulos</a></li>
            <li><a href="estadistica.php">Estadísticas</a></li>
            <li><a href="logros.php">Logros</a></li>
        </ul>
    </aside>
    <?php if (!empty($mensaje)): ?>
        <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
    <?php endif; ?>

    <main>
        <h2>Añadir un nuevo banco</h2>
        <form action="añadir_banco.php" method="POST">
            <label for="banco">Banco: </label>
            <input type="text" name="banco" placeholder="Nombre del banco" required>
            <select id="banco" name="banco" required>
                <option value="0">Selecciona tu banco.</option>
                <option value="Banco de Chile">Banco de Chile</option>
                <option value="BCI">BCI</option>
                <option value="Banco Estado">Banco Estado</option>
                <option value="Santander">Santander</option>
                <option value="Itau">Itaú</option>
                <option value="Scotiabank">Scotiabank</option>
                <option value="Falabella">Banco Falabella</option>
                <option value="Ripley">Banco Ripley</option>
                <option value="Security">Banco Security</option>
                <option value="Otro">otro</option>
            </select>

            <label for="tipo">Tipo de Cuenta: </label>
            <select name="tipo-cnta" id="tipo-cnta">
                <option value="Cuenta Rut">Cuenta Rut</option>
                <option value="Cuenta Vista">Cuenta Vista</option>
                <option value="Cuenta de Ahorro">Cuenta de Ahorro</option>
                <option value="Cuenta Corriente">Cuenta Corriente</option>
            </select>
            <label for="cuenta">Nombre: </label>
            <input type="text" name="cuenta" placeholder="Nombre de la Cuenta" required>

            <button type="submit" name="submit_banco">Añadir banco</button>

        </form>
    </main>

    <footer>
        <p>&copy; 2024 Foro de Artículos Informativos. Todos los derechos reservados.</p>
    </footer>
    
    </body>
</html>
