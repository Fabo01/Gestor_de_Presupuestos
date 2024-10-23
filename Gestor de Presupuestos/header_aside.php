<!-- header_aside.php -->
<header class="header">
    <div class="logo">
        Gestor de Presupuestos
    </div>
    <nav class="navbar">
        <ul>
            <li><a href="perfil.php">Perfil</a></li>
            <li><a href="bancos.php">Bancos</a></li>
            <li><a href="transacciones.php">Transacciones</a></li>
            <li><a href="logout.php">Cerrar Sesi�n</a></li>
        </ul>
    </nav>
    <button class="toggle-sidebar-btn-left" onclick="toggleSidebar()">&#x22EE;</button>
</header>

<aside id="sidebar" class="sidebar">
    <ul>
        <li><a href="dashboard.php">Inicio</a></li>
        <li><a href="bancos.php">Tus Cuentas</a></li>
        <li><a href="categorias.php">Tus Categor�as</a></li>
        <li><a href="articulos.php">Ver Art�culos</a></li>
        <li><a href="estadisticas.php">Estad�sticas</a></li>
        <li><a href="logros.php">Logros</a></li>
    </ul>
    <button class="toggle-sidebar-btn-inside" onclick="toggleSidebar()">&#x22EE;</button>
</aside>
