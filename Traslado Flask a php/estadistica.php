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

$stmt = $db->prepare("
    SELECT SUM(presupuesto_mensual) AS presupuesto_total
    FROM Categorias C
    INNER JOIN Cuentas_de_banco CB ON C.ID_cuentabanco = CB.ID_cuentabanco
    WHERE CB.ID_usuario = ?
");
if (!$stmt) {
    error_log("Error al preparar la consulta: " . $db->error);
    $error_message = "Ocurrió un error al cargar los datos. Por favor, inténtalo de nuevo más tarde.";
} else {
    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar la consulta: " . $stmt->error);
        $error_message = "Ocurrió un error al cargar los datos. Por favor, inténtalo de nuevo más tarde.";
    } else {
        $stmt->bind_result($presupuesto_total);
        $stmt->fetch();
        $stmt->close();
    }
}

$stmt = $db->prepare("
    SELECT SUM(C.gasto_acumulado) AS gasto_total, SUM(C.saldo_restante) AS saldo_total
    FROM Categorias C
    INNER JOIN Cuentas_de_banco CB ON C.ID_cuentabanco = CB.ID_cuentabanco
    WHERE CB.ID_usuario = ?
");
if (!$stmt) {
    error_log("Error al preparar la consulta: " . $db->error);
} else {
    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar la consulta: " . $stmt->error);
    } else {
        $stmt->bind_result($gasto_total, $saldo_total);
        $stmt->fetch();
        $stmt->close();
    }
}

$stmt = $db->prepare("
    SELECT C.nombre, C.gasto_acumulado
    FROM Categorias C
    INNER JOIN Cuentas_de_banco CB ON C.ID_cuentabanco = CB.ID_cuentabanco
    WHERE CB.ID_usuario = ?
");
if (!$stmt) {
    error_log("Error al preparar la consulta: " . $db->error);
} else {
    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar la consulta: " . $stmt->error);
    } else {
        $result = $stmt->get_result();
        $categorias = [];
        while ($row = $result->fetch_assoc()) {
            $categorias[] = $row;
        }
        $stmt->close();
    }
}

$stmt = $db->prepare("
    SELECT DATE_FORMAT(T.fecha, '%Y-%m') AS mes, SUM(T.Monto) AS monto_total
    FROM Transacciones T
    INNER JOIN Cuentas_de_banco CB ON T.ID_Cuentabanco = CB.ID_cuentabanco
    WHERE CB.ID_usuario = ? AND T.fecha >= DATE_SUB(CURDATE(), INTERVAL 6 MONTH)
    GROUP BY mes
    ORDER BY mes ASC
");
if (!$stmt) {
    error_log("Error al preparar la consulta: " . $db->error);
} else {
    $stmt->bind_param('i', $user_id);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar la consulta: " . $stmt->error);
    } else {
        $result = $stmt->get_result();
        $gastos_mensuales = [];
        while ($row = $result->fetch_assoc()) {
            $gastos_mensuales[] = $row;
        }
        $stmt->close();
    }
}
?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Estadísticas Financieras</title>
    <link rel="stylesheet" href="css/style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
        <li><a href="articulos.php">Ver Artículos</a></li>
        <li><a href="logros.php">Logros</a></li>
    </ul>
</aside>

<main>
    <h1>Estadísticas Financieras</h1>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
    <?php if (isset($error_message)): ?>
        <p class="mensaje"><?php echo htmlspecialchars($error_message); ?></p>
    <?php else: ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

        <section>
            <h2>Presupuesto Total de Todas las Cuentas Bancarias</h2>
            <p><strong>Presupuesto Total:</strong> $<?php echo number_format($presupuesto_total, 2); ?></p>
        </section>

        <section>
            <h2>Gastos Totales vs. Saldos Restantes</h2>
            <canvas id="gastosSaldosChart"></canvas>
        </section>

        <section>
            <h2>Gastos por Categoría</h2>
            <canvas id="gastosCategoriaChart"></canvas>
        </section>

        <section>
            <h2>Gastos en los Últimos 6 Meses</h2>
            <canvas id="gastosMensualesChart"></canvas>
        </section>
    <?php endif; ?>
</main>

<footer>
    <p>&copy; 2024 Foro de Artículos Informativos. Todos los derechos reservados.</p>
</footer>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctxGastosSaldos = document.getElementById('gastosSaldosChart').getContext('2d');
    const gastosSaldosChart = new Chart(ctxGastosSaldos, {
        type: 'doughnut',
        data: {
            labels: ['Gasto Total', 'Saldo Restante'],
            datasets: [{
                data: [<?php echo $gasto_total; ?>, <?php echo $saldo_total; ?>],
                backgroundColor: ['#ff6384', '#36a2eb']
            }]
        },
        options: {
            responsive: true
        }
    });

    const ctxGastosCategoria = document.getElementById('gastosCategoriaChart').getContext('2d');
    const categorias = <?php echo json_encode(array_column($categorias, 'nombre')); ?>;
    const gastosPorCategoria = <?php echo json_encode(array_map('floatval', array_column($categorias, 'gasto_acumulado'))); ?>;

    const gastosCategoriaChart = new Chart(ctxGastosCategoria, {
        type: 'bar',
        data: {
            labels: categorias,
            datasets: [{
                label: 'Gasto por Categoría',
                data: gastosPorCategoria,
                backgroundColor: '#4caf50'
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });

    const ctxGastosMensuales = document.getElementById('gastosMensualesChart').getContext('2d');
    const meses = <?php echo json_encode(array_column($gastos_mensuales, 'mes')); ?>;
    const gastosMensuales = <?php echo json_encode(array_map('floatval', array_column($gastos_mensuales, 'monto_total'))); ?>;

    const gastosMensualesChart = new Chart(ctxGastosMensuales, {
        type: 'line',
        data: {
            labels: meses,
            datasets: [{
                label: 'Gasto Mensual',
                data: gastosMensuales,
                fill: false,
                borderColor: '#ff9800',
                tension: 0.1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: { beginAtZero: true }
            }
        }
    });
});
</script>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
<script src="js/menu_lateral.js"></script>
</body>
</html>
