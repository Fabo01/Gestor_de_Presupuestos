<?php
require 'Conex.inc';
session_start();

// Verificar si el usuario ha iniciado sesión
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

// Obtener información del usuario
$user_id = $_SESSION['user_id'];
$usuario = $_SESSION['usuario'];

$error = '';
$mensaje = '';

// Generar token CSRF
if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verificar el token CSRF
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $error = "Token CSRF inválido.";
    } else {
        // Capturar los datos del formulario
        $nombre_banco = ($_POST['banco_select'] === 'otro') ? trim($_POST['nombre_banco']) : trim($_POST['banco_select']);
        $tipo_cuenta = trim($_POST['tipo_cuenta']);
        $nombre_cuenta = trim($_POST['nombre_cuenta']);

        if (empty($nombre_banco) || empty($tipo_cuenta) || empty($nombre_cuenta)) {
            $error = "Por favor, completa todos los campos.";
        } else {
            // Verificar si ya existe una cuenta bancaria con los mismos detalles
            $stmt_check = $db->prepare("SELECT COUNT(*) FROM Cuentas_de_banco WHERE banco = ? AND tipo = ? AND nombre = ? AND ID_usuario = ?");
            $stmt_check->bind_param('sssi', $nombre_banco, $tipo_cuenta, $nombre_cuenta, $user_id);
            $stmt_check->execute();
            $stmt_check->bind_result($count);
            $stmt_check->fetch();
            $stmt_check->close();

            if ($count > 0) {
                $error = "Ya tienes una cuenta bancaria con esos detalles.";
            } else {
                // Insertar la nueva cuenta bancaria
                $stmt = $db->prepare("INSERT INTO Cuentas_de_banco (banco, tipo, nombre, ID_usuario) VALUES (?, ?, ?, ?)");
                $stmt->bind_param('sssi', $nombre_banco, $tipo_cuenta, $nombre_cuenta, $user_id);
                $stmt->execute();
                $stmt->close();

                $mensaje = "Banco agregado correctamente.";
            }
        }
    }
}

// Procesar eliminación de banco
if (isset($_GET['eliminar'])) {
    $id_banco = intval($_GET['eliminar']);
    // Eliminar la cuenta bancaria
    $stmt_delete = $db->prepare("DELETE FROM Cuentas_de_banco WHERE ID_banco = ? AND ID_usuario = ?");
    $stmt_delete->bind_param('ii', $id_banco, $user_id);
    if ($stmt_delete->execute()) {
        $mensaje = "Cuenta bancaria eliminada exitosamente.";
    } else {
        $error = "Error al eliminar la cuenta bancaria.";
    }
    $stmt_delete->close();
}

// Parámetros de búsqueda y paginación
$buscar = isset($_GET['buscar']) ? trim($_GET['buscar']) : '';
$bancos_por_pagina = 10;
$pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
$offset = ($pagina_actual - 1) * $bancos_por_pagina;

// Obtener el número total de bancos
$query_total = "SELECT COUNT(*) FROM Cuentas_de_banco WHERE ID_usuario = ?";
$params_total = [$user_id];
$types_total = 'i';

if (!empty($buscar)) {
    $query_total .= " AND (banco LIKE CONCAT('%', ?, '%') OR nombre LIKE CONCAT('%', ?, '%'))";
    $params_total[] = $buscar;
    $params_total[] = $buscar;
    $types_total .= 'ss';
}

$stmt_total = $db->prepare($query_total);
$stmt_total->bind_param($types_total, ...$params_total);
$stmt_total->execute();
$stmt_total->bind_result($total_bancos);
$stmt_total->fetch();
$stmt_total->close();

// Calcular total de páginas
$total_paginas = ceil($total_bancos / $bancos_por_pagina);

// Obtener las cuentas bancarias del usuario con filtros y paginación
$query = "SELECT ID_banco, banco, tipo, nombre FROM Cuentas_de_banco WHERE ID_usuario = ?";
$params = [$user_id];
$types = 'i';

if (!empty($buscar)) {
    $query .= " AND (banco LIKE CONCAT('%', ?, '%') OR nombre LIKE CONCAT('%', ?, '%'))";
    $params[] = $buscar;
    $params[] = $buscar;
    $types .= 'ss';
}

$query .= " ORDER BY banco ASC LIMIT ?, ?";
$params[] = $offset;
$params[] = $bancos_por_pagina;
$types .= 'ii';

$stmt = $db->prepare($query);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$result = $stmt->get_result();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Bancos</title>
    <link rel="stylesheet" href="CSS/style.css">
    <link rel="stylesheet" href="CSS/intro_datos.css">
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

    <main>

        <h3><?php echo isset($_GET['editar']) ? 'Editar Cuenta Bancaria' : 'Añadir una nueva cuenta bancaria'; ?></h3>

        <!-- Mostrar mensajes de éxito o error -->
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>

        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <?php
        // Si se está editando una cuenta bancaria, obtener sus datos
        $editar_nombre_banco = '';
        $editar_tipo_cuenta = '';
        $editar_nombre_cuenta = '';
        $id_banco_editar = 0;

        if (isset($_GET['editar'])) {
            $id_banco_editar = intval($_GET['editar']);
            $stmt_editar = $db->prepare("SELECT banco, tipo, nombre FROM Cuentas_de_banco WHERE ID_banco = ? AND ID_usuario = ?");
            $stmt_editar->bind_param('ii', $id_banco_editar, $user_id);
            $stmt_editar->execute();
            $stmt_editar->bind_result($editar_nombre_banco, $editar_tipo_cuenta, $editar_nombre_cuenta);
            $stmt_editar->fetch();
            $stmt_editar->close();
        }
        ?>

        <form action="bancos.php" method="POST">
            <!-- Selector de banco o ingresar otro banco -->
            <label for="banco"><br>Selecciona tu banco:</label>
            <select id="banco_select" name="banco_select" onchange="toggleBancoInput(this.value)">
                <option value="Banco de Chile" <?php echo ($editar_nombre_banco == 'Banco de Chile') ? 'selected' : ''; ?>>Banco de Chile</option>
                <option value="Estado" <?php echo ($editar_nombre_banco == 'Banco Estado') ? 'selected' : ''; ?>>Banco Estado</option>
                <option value="Santander" <?php echo ($editar_nombre_banco == 'Banco Santander') ? 'selected' : ''; ?>>Banco Santander</option>
                <option value="BCI" <?php echo ($editar_nombre_banco == 'Banco BCI') ? 'selected' : ''; ?>>Banco BCI</option>
                <option value="otro">Otro (Escribe el nombre del banco)</option>
            </select>

            <!-- Input de nombre del banco solo si selecciona 'Otro' -->
            <label for="nombre_banco"><br>Escribe el nombre del banco:</label>
            <input type="text" id="nombre_banco" name="nombre_banco" placeholder="Escribe el nombre del banco"
                value="<?php echo htmlspecialchars($editar_nombre_banco); ?>" 
                <?php echo ($editar_nombre_banco != '' && !in_array($editar_nombre_banco, ['Banco de Chile', 'Banco Estado', 'Banco Santander', 'Banco BCI'])) ? '' : 'disabled'; ?> required>

            <!-- Tipo de cuenta -->
            <label for="tipo_cuenta"><br>Selecciona el tipo de cuenta:</label>
            <select id="tipo_cuenta" name="tipo_cuenta">
                <option value="Cuenta Vista">Cuenta Vista</option>
                <option value="Cuenta RUT">Cuenta RUT</option>
                <option value="Cuenta de Ahorro">Cuenta de Ahorro</option>
                <option value="Cuenta Corriente">Cuenta Corriente</option>
            </select>

            <!-- Input para el nombre de la cuenta (por ejemplo, "Aquí guardo ahorros") -->
            <label for="nombre_cuenta"><br>Nombre de la cuenta:</label>
            <input type="text" id="nombre_cuenta" name="nombre_cuenta" placeholder="Escribe el nombre de la cuenta" 
                value="<?php echo isset($editar_nombre_cuenta) ? htmlspecialchars($editar_nombre_cuenta) : ''; ?>" required>

            <!-- Token CSRF para seguridad -->
            <input type="hidden" name="token" value="<?php echo $token; ?>">

            <button type="submit">Guardar</button>
        </form>


        <div class="container-gestion">
            <h3>Mis Cuentas Bancarias</h3>
            <table>
                <tr>
                    <th>Banco</th>
                    <th>Tipo</th>
                    <th>Nombre de la Cuenta</th>
                    <th>Acciones</th>
                </tr>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['banco']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipo']); ?></td>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td>
                            <a href="bancos.php?editar=<?php echo $row['ID_banco']; ?>">Editar</a> |
                            <a href="bancos.php?eliminar=<?php echo $row['ID_banco']; ?>" onclick="return confirm('¿Estás seguro de eliminar esta cuenta bancaria?');">Eliminar</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </table>

            <!-- Paginación -->
            <div class="pagination">
                <?php
                $query_params = $_GET;
                unset($query_params['pagina']);
                $base_url = '?' . http_build_query($query_params);

                if ($pagina_actual > 1):
                ?>
                    <a href="<?php echo $base_url . '&pagina=' . ($pagina_actual - 1); ?>">&laquo; Anterior</a>
                <?php endif; ?>

                <span>Página <?php echo $pagina_actual; ?> de <?php echo $total_paginas; ?></span>

                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="<?php echo $base_url . '&pagina=' . ($pagina_actual + 1); ?>">Siguiente &raquo;</a>
                <?php endif; ?>
            </div>
        </div>

    </main>

    <footer>
        <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
    </footer>

    <script src="JS/menu_lateral.js"></script>
</body>
</html>
