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

$id_categoria = filter_input(INPUT_GET, 'id_categoria', FILTER_VALIDATE_INT);
if (!$id_categoria) {
    echo "Categoría inválida.";
    exit();
}

$stmt = $db->prepare("
    SELECT C.nombre, C.presupuesto_mensual, C.gasto_acumulado, C.saldo_restante, CB.ID_cuentabanco
    FROM Categoria C
    INNER JOIN Cuentas_de_banco CB ON C.ID_cuentabanco = CB.ID_cuentabanco
    WHERE C.ID_categoria = ? AND CB.ID_usuario = ?
");
if (!$stmt) {
    error_log("Error al preparar la consulta: " . $db->error);
    echo "Ocurrió un error al cargar los datos. Por favor, inténtalo de nuevo más tarde.";
    exit();
}

$stmt->bind_param('ii', $id_categoria, $_SESSION['user_id']);
if (!$stmt->execute()) {
    error_log("Error al ejecutar la consulta: " . $stmt->error);
    echo "Ocurrió un error al cargar los datos. Por favor, inténtalo de nuevo más tarde.";
    exit();
}

$stmt->bind_result($nombre_categoria, $presupuesto_total, $gasto_acumulado, $saldo_restante, $id_cuentabanco);
if (!$stmt->fetch()) {
    echo "No tienes permiso para acceder a esta categoría.";
    exit();
}
$stmt->close();

function obtenerTransacciones($db, $id_categoria) {
    $stmt = $db->prepare("
        SELECT `desc`, Monto, fecha
        FROM Transacciones
        WHERE ID_Categoria = ?
        ORDER BY fecha DESC
    ");
    if (!$stmt) {
        error_log("Error al preparar la consulta: " . $db->error);
        return [];
    }

    $stmt->bind_param('i', $id_categoria);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar la consulta: " . $stmt->error);
        return [];
    }

    $result = $stmt->get_result();
    $transacciones = [];
    while ($row = $result->fetch_assoc()) {
        $transacciones[] = $row;
    }
    $stmt->close();
    return $transacciones;
}

$transacciones = obtenerTransacciones($db, $id_categoria);

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];
?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestionar Transacciones - <?php echo htmlspecialchars($nombre_categoria); ?></title>
    <link rel="stylesheet" href="CSS/style.css">
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
            <li><a href="estadistica.php">Estadísticas</a></li>
            <li><a href="logros.php">Logros</a></li>
        </ul>
    </aside>

    <main>
        <h2>Gestionar Transacciones - <?php echo htmlspecialchars($nombre_categoria); ?></h2>

        <div class="datos-categoria">
            <p><strong>Presupuesto Total:</strong> $<?php echo number_format($presupuesto_total, 2); ?></p>
            <p><strong>Monto Total de Transacciones:</strong> <span id="gastoAcumulado">$<?php echo number_format($gasto_acumulado, 2); ?></span></p>
            <p><strong>Saldo Restante:</strong> <span id="saldoRestante">$<?php echo number_format($saldo_restante, 2); ?></span></p>
        </div>

        <div id="mensaje" class="mensaje"></div>

        <section>
            <h3>Agregar Nueva Transacción</h3>
            <form id="transaccionForm" method="POST" action="procesar_transaccion.php">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                <input type="hidden" name="id_categoria" value="<?php echo htmlspecialchars($id_categoria, ENT_QUOTES, 'UTF-8'); ?>">
                <div class="form-group">
                    <label for="descripcion">Descripción:</label>
                    <input type="text" id="descripcion" name="descripcion" required>
                </div>
                <div class="form-group">
                    <label for="monto">Monto:</label>
                    <input type="number" id="monto" name="monto" step="0.01" required>
                </div>
                <button type="submit">Agregar Transacción</button>
            </form>
        </section>

        <section>
            <h3>Lista de Transacciones</h3>
            <ul id="transaccionesList">
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                <?php foreach ($transacciones as $transaccion): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($transaccion['desc']); ?></strong> - Monto: $<?php echo htmlspecialchars(number_format($transaccion['Monto'], 2)); ?> - Fecha: <?php echo htmlspecialchars($transaccion['fecha']); ?>
                    </li>
                <?php endforeach; ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
            </ul>
        </section>

        <section>
            <h3>Gráfico de Transacciones</h3>
            <canvas id="transaccionesChart"></canvas>
        </section>

        <div class="button-group">
            <a href="ver_categorias.php?id_banco=<?php echo htmlspecialchars($id_cuentabanco, ENT_QUOTES, 'UTF-8'); ?>" class="btn">Regresar a Categorías</a>
        </div>
    </main>

    <footer>
        <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
    </footer>

    <script src="JS/menu_lateral.js"></script>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const ctx = document.getElementById('transaccionesChart').getContext('2d');
        let transaccionesFecha = <?php echo json_encode(array_column($transacciones, 'fecha')); ?>;
        let transaccionesMonto = <?php echo json_encode(array_map('floatval', array_column($transacciones, 'Monto'))); ?>;

        const transaccionesChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: transaccionesFecha,
                datasets: [{
                    label: 'Monto de Transacciones',
                    data: transaccionesMonto,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: { beginAtZero: true }
                }
            }
        });

        const form = document.getElementById('transaccionForm');
        form.addEventListener('submit', function(event) {
            event.preventDefault();

            const formData = new FormData(form);

            fetch('procesar_transaccion.php', {
                method: 'POST',
                body: formData,
                credentials: 'same-origin'
            })
            .then(response => response.json())
            .then(data => {
                const mensajeDiv = document.getElementById('mensaje');
                if (data.success) {
                    mensajeDiv.textContent = data.message;
                    mensajeDiv.classList.add('mensaje-exito');
                    mensajeDiv.classList.remove('mensaje-error');

                    form.reset();

                    const transaccionesList = document.getElementById('transaccionesList');
                    const newTransaccion = document.createElement('li');
                    newTransaccion.innerHTML = `<strong>${data.transaccion.desc}</strong> - Monto: $${data.transaccion.Monto} - Fecha: ${data.transaccion.fecha}`;
                    transaccionesList.insertBefore(newTransaccion, transaccionesList.firstChild);

                    transaccionesChart.data.labels.unshift(data.transaccion.fecha);
                    transaccionesChart.data.datasets[0].data.unshift(parseFloat(data.transaccion.Monto));
                    transaccionesChart.update();

                    document.getElementById('gastoAcumulado').textContent = `$${data.gasto_acumulado}`;
                    document.getElementById('saldoRestante').textContent = `$${data.saldo_restante}`;
                } else {
                    mensajeDiv.textContent = data.message;
                    mensajeDiv.classList.add('mensaje-error');
                    mensajeDiv.classList.remove('mensaje-exito');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                const mensajeDiv = document.getElementById('mensaje');
                mensajeDiv.textContent = 'Ocurrió un error al procesar la transacción.';
                mensajeDiv.classList.add('mensaje-error');
                mensajeDiv.classList.remove('mensaje-exito');
            });
        });
    });
    </script>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
</body>
</html>
