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

$ID_cuentabanco = filter_input(INPUT_GET, 'id_banco', FILTER_VALIDATE_INT);

if (!$ID_cuentabanco) {
    header('Location: dashboard.php');
    exit();
}

// Generar un token CSRF para el formulario
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Inicializar variable para mensajes
$message = '';

// Procesar la actualización del presupuesto si se envía el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] === 'actualizar_presupuesto') {
    // Verificar el token CSRF
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $message = "Token CSRF inválido.";
    } else {
        // Validar y sanitizar los datos
        $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);
        $nuevo_presupuesto = filter_input(INPUT_POST, 'nuevo_presupuesto', FILTER_VALIDATE_FLOAT);

        if (!$id_categoria || $nuevo_presupuesto === false || $nuevo_presupuesto < 0) {
            $message = "Datos inválidos.";
        } else {
            // Verificar que la categoría pertenece al usuario
            $stmt = $db->prepare("
                SELECT C.ID_categoria, C.presupuesto_mensual, C.gasto_acumulado
                FROM Categorias C
                INNER JOIN Cuentas_de_banco CB ON C.ID_cuentabanco = CB.ID_cuentabanco
                WHERE C.ID_categoria = ? AND CB.ID_usuario = ?
            ");
            if (!$stmt) {
                error_log("Error al preparar la consulta: " . $db->error);
                $message = "Ocurrió un error al procesar la solicitud.";
            } else {
                $stmt->bind_param("ii", $id_categoria, $user_id);
                if (!$stmt->execute()) {
                    error_log("Error al ejecutar la consulta: " . $stmt->error);
                    $message = "Ocurrió un error al procesar la solicitud.";
                } else {
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $stmt->bind_result($id_categoria_obtenido, $presupuesto_actual, $gasto_acumulado);
                        $stmt->fetch();
                        $stmt->close();

                        // Validar que el nuevo presupuesto no es menor que el gasto acumulado
                        if ($nuevo_presupuesto < $gasto_acumulado) {
                            $message = "El nuevo presupuesto no puede ser menor que el gasto acumulado actual.";
                        } else {
                            // Calcular el nuevo saldo restante
                            $nuevo_saldo_restante = $nuevo_presupuesto - $gasto_acumulado;

                            // Actualizar la categoría con el nuevo presupuesto y saldo restante
                            $stmt = $db->prepare("UPDATE Categoria SET presupuesto_mensual = ?, saldo_restante = ? WHERE ID_categoria = ?");
                            if (!$stmt) {
                                error_log("Error al preparar la consulta: " . $db->error);
                                $message = "Ocurrió un error al actualizar la categoría.";
                            } else {
                                $stmt->bind_param("ddi", $nuevo_presupuesto, $nuevo_saldo_restante, $id_categoria);
                                if ($stmt->execute()) {
                                    $stmt->close();
                                    $message = "Presupuesto actualizado exitosamente.";
                                } else {
                                    error_log("Error al ejecutar la actualización: " . $stmt->error);
                                    $message = "Ocurrió un error al actualizar la categoría.";
                                }
                            }
                        }
                    } else {
                        $message = "No tienes permiso para modificar esta categoría.";
                    }
                }
            }
        }
    }
}

// Obtener el nombre del banco
$stmt = $db->prepare("SELECT nombre, banco, tipo FROM Cuentas_de_banco WHERE ID_banco = ? AND ID_usuario = ?");
if (!$stmt) {
    error_log("Error al preparar la consulta: " . $db->error);
    echo "Ocurrió un error al cargar los datos. Por favor, inténtalo de nuevo más tarde.";
    exit();
}

$stmt->bind_param('ii', $ID_cuentabanco, $user_id);
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
                <a href="ver_perfil.php">
                    <button class="btn btn-perfil">Perfil</button>
                </a>
            </li>
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

    <?php if ($message): ?>
        <p class="mensaje"><?php echo htmlspecialchars($message); ?></p>
    <?php endif; ?>

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
                            </div>
                            <!-- Botón para mostrar/ocultar el formulario de modificación -->
                            <button class='btn btn-modificar' onclick='mostrarFormulario(" . htmlspecialchars($row['ID_categoria'], ENT_QUOTES, 'UTF-8') . ")'>Modificar Presupuesto</button>
                            <!-- Formulario oculto para modificar el presupuesto -->
                            <div id='form-modificar-" . htmlspecialchars($row['ID_categoria'], ENT_QUOTES, 'UTF-8') . "' class='form-modificar-presupuesto' style='display: none;'>
                                <form method='post' action=''>
                                    <input type='hidden' name='csrf_token' value='" . $csrf_token . "'>
                                    <input type='hidden' name='id_categoria' value='" . htmlspecialchars($row['ID_categoria'], ENT_QUOTES, 'UTF-8') . "'>
                                    <input type='hidden' name='accion' value='actualizar_presupuesto'>
                                    <input type='number' name='nuevo_presupuesto' step='0.01' min='0' placeholder='Nuevo Presupuesto' required>
                                    <button type='submit'>Actualizar</button>
                                </form>
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
<script>
    function mostrarFormulario(idCategoria) {
        var formulario = document.getElementById('form-modificar-' + idCategoria);
        if (formulario.style.display === 'none') {
            formulario.style.display = 'block';
        } else {
            formulario.style.display = 'none';
        }
    }
</script>
</body>
</html>
