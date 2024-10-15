<?php
function verificar_logros($user_id, $db) {
    $stmt = $db->prepare("SELECT ID_logro, nombre, tipo, condicion FROM Logros");
    $stmt->execute();
    $result = $stmt->get_result();

    while ($logro = $result->fetch_assoc()) {
        $logro_id = $logro['ID_logro'];
        $tipo = $logro['tipo'];
        $condicion = $logro['condicion'];

        if (usuario_tiene_logro($user_id, $logro_id, $db)) {
            continue;
        }

        switch ($tipo) {
            case 'ahorro_total':
                verificar_logro_ahorro_total($user_id, $db, $logro_id, $condicion);
                break;
            case 'primera_transaccion':
                verificar_logro_primera_transaccion($user_id, $db, $logro_id);
                break;
        }
    }
    $stmt->close();
}

function usuario_tiene_logro($user_id, $logro_id, $db) {
    $stmt = $db->prepare("SELECT 1 FROM Usuario_Logros WHERE ID_usuario = ? AND ID_logro = ?");
    $stmt->bind_param('ii', $user_id, $logro_id);
    $stmt->execute();
    $stmt->store_result();
    $tiene_logro = $stmt->num_rows > 0;
    $stmt->close();
    return $tiene_logro;
}

function otorgar_logro($user_id, $logro_id, $db) {
    $stmt = $db->prepare("INSERT INTO Usuario_Logros (ID_usuario, ID_logro) VALUES (?, ?)");
    $stmt->bind_param('ii', $user_id, $logro_id);
    $stmt->execute();
    $stmt->close();
    $_SESSION['mensaje_logro'] = obtener_nombre_logro($logro_id, $db);
}

function obtener_nombre_logro($logro_id, $db) {
    $stmt = $db->prepare("SELECT nombre FROM Logros WHERE ID_logro = ?");
    $stmt->bind_param('i', $logro_id);
    $stmt->execute();
    $stmt->bind_result($nombre_logro);
    $stmt->fetch();
    $stmt->close();
    return $nombre_logro;
}

function verificar_logro_ahorro_total($user_id, $db, $logro_id, $condicion) {
    $stmt = $db->prepare("
        SELECT SUM(T.Monto)
        FROM Transacciones T
        INNER JOIN Categoria C ON T.ID_Categoria = C.ID_categoria
        INNER JOIN Cuentas_de_banco CB ON C.ID_cuentabanco = CB.ID_cuentabanco
        WHERE CB.ID_usuario = ? AND T.Monto > 0
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($total_ahorrado);
    $stmt->fetch();
    $stmt->close();

    if ($total_ahorrado >= $condicion) {
        otorgar_logro($user_id, $logro_id, $db);
    }
}

function verificar_logro_primera_transaccion($user_id, $db, $logro_id) {
    $stmt = $db->prepare("
        SELECT COUNT(*)
        FROM Transacciones T
        INNER JOIN Cuentas_de_banco CB ON T.ID_Cuentabanco = CB.ID_cuentabanco
        WHERE CB.ID_usuario = ?
    ");
    $stmt->bind_param('i', $user_id);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count >= 1) {
        otorgar_logro($user_id, $logro_id, $db);
    }
}
?>

<?php
require 'Conex.inc';

session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];

$stmt = $db->prepare("
    SELECT L.ID_logro, L.nombre, L.descripcion, UL.fecha_desbloqueo
    FROM Logros L
    LEFT JOIN Usuario_Logros UL ON L.ID_logro = UL.ID_logro AND UL.ID_usuario = ?
    ORDER BY L.ID_logro ASC
");
$stmt->bind_param('i', $user_id);
$stmt->execute();
$result = $stmt->get_result();

$logros = [];
while ($row = $result->fetch_assoc()) {
    $logros[] = $row;
}
$stmt->close();
?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Logros</title>
    <link rel="stylesheet" href="CSS/style.css">
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
                        <a href="articulos.php">
                            <button class="btn btn-boletines">Boletines</button>
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

    <main>
        <h1>Mis Logros</h1>

        <div class="logros-container">
            <?php foreach ($logros as $logro): ?>
                <div class="logro <?php echo $logro['fecha_desbloqueo'] ? 'desbloqueado' : 'no-desbloqueado'; ?>">
                    <h3><?php echo htmlspecialchars($logro['nombre']); ?></h3>
                    <p><?php echo htmlspecialchars($logro['descripcion']); ?></p>
                    <?php if ($logro['fecha_desbloqueo']): ?>
                        <p>Desbloqueado el: <?php echo date('d/m/Y', strtotime($logro['fecha_desbloqueo'])); ?></p>
                    <?php else: ?>
                        <p>Aún no desbloqueado</p>
                    <?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </main>

    <footer>
        <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
    </footer>

<script src="JS/menu_lateral.js"></script>
</body>
</html>
