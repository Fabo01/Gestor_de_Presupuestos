<?php
// Iniciamos la sesión si es necesario
session_start();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Información y Ayuda</title>
    <link rel="stylesheet" href="CSS/style.css">
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
                <li><a href="ver_perfil.php">Perfil</a></li>
                <li><a href="logout.php">Cerrar Sesión</a></li>
            <?php else: ?>
                <li><a href="index.php">Iniciar Sesión</a></li>
            <?php endif; ?>
        </ul>
    </nav>
</header>

<?php if (isset($_SESSION['user_id'])): ?>
<aside id="sidebar" class="sidebar">
    <button id="close-btn" class="close-btn">&times;</button>
    <ul>
        <li><a href="dashboard.php">Inicio</a></li>
        <li><a href="informacion.php">Información</a></li>
        <li><a href="estadistica.php">Estadísticas</a></li>
        <li><a href="logros.php">Logros</a></li>
    </ul>
</aside>
<?php endif; ?>

<main>
    <h1>Información y Ayuda</h1>

    <!-- Barra de navegación para las secciones -->
    <div class="tab-navigation">
        <button class="tab-button active" onclick="openTab(event, 'info')">Acerca de</button>
        <button class="tab-button" onclick="openTab(event, 'guia')">Guía de Uso</button>
        <button class="tab-button" onclick="openTab(event, 'consejos')">Consejos Financieros</button>
    </div>

    <!-- Sección Acerca de -->
    <div id="info" class="tab-content" style="display: block;">
        <h2>Acerca de</h2>
        <p>Bienvenido a nuestro Gestor de Presupuestos, una herramienta diseñada para ayudarte a administrar tus finanzas personales de manera efectiva. Nuestra aplicación te permite controlar tus ingresos y gastos, establecer presupuestos, y alcanzar tus metas financieras.</p>
        <!-- Añade más información sobre la página aquí -->
    </div>

    <!-- Sección Guía de Uso -->
    <div id="guia" class="tab-content" style="display: none;">
        <h2>Guía de Uso</h2>
        <p>Para comenzar a utilizar el Gestor de Presupuestos, sigue estos pasos:</p>
        <ol>
            <li><strong>Registro:</strong> Crea una cuenta proporcionando tu información básica.</li>
            <li><strong>Agregar Cuentas Bancarias:</strong> Añade tus cuentas bancarias para comenzar a gestionar tus finanzas.</li>
            <li><strong>Crear Categorías:</strong> Organiza tus gastos e ingresos en categorías personalizadas.</li>
            <li><strong>Registrar Transacciones:</strong> Añade transacciones para llevar un seguimiento detallado de tus movimientos financieros.</li>
            <li><strong>Revisar Estadísticas:</strong> Utiliza nuestras herramientas de estadísticas para analizar tu situación financiera.</li>
        </ol>
        <!-- Añade más orientación al usuario aquí -->
    </div>

    <!-- Sección Consejos Financieros -->
    <div id="consejos" class="tab-content" style="display: none;">
        <h2>Consejos Financieros</h2>
        <ul>
            <li><strong>Establece un Presupuesto:</strong> Define límites claros para tus gastos y ajústate a ellos.</li>
            <li><strong>Ahorra Regularmente:</strong> Destina una parte de tus ingresos al ahorro cada mes.</li>
            <li><strong>Evita Deudas Innecesarias:</strong> Maneja tus tarjetas de crédito con responsabilidad.</li>
            <li><strong>Informa tus Decisiones:</strong> Investiga antes de realizar inversiones o grandes compras.</li>
            <li><strong>Revisa tus Finanzas:</strong> Regularmente analiza tus ingresos y gastos para identificar áreas de mejora.</li>
        </ul>
        <!-- Añade más consejos financieros aquí -->
    </div>
</main>

<footer>
    <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
</footer>

<script src="JS/menu_lateral.js"></script>
<script src="JS/informacion.js"></script>
</body>
</html>
