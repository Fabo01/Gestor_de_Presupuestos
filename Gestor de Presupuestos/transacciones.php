<?php
require 'Conex.inc';
session_start();

// Verificar si el usuario ha iniciado sesi�n
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$usuario = $_SESSION['usuario'];

$error = '';
$mensaje = '';

// Generar token CSRF
if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Procesar el formulario cuando se env�a
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($csrf_token, $_POST['csrf_token'])) {
        $error = "Token CSRF inv�lido.";
    } else {
        // Recopilar y validar los datos del formulario
        $id_banco = isset($_POST['id_banco']) ? intval($_POST['id_banco']) : 0;
        $monto = isset($_POST['monto']) ? floatval($_POST['monto']) : 0;
        $id_categoria = isset($_POST['id_categoria']) ? intval($_POST['id_categoria']) : 0;
        $fecha = isset($_POST['fecha']) ? $_POST['fecha'] : '';
        $descripcion = isset($_POST['descripcion']) ? trim($_POST['descripcion']) : '';

        // Validar campos obligatorios
        if ($id_banco <= 0 || $monto == 0 || $id_categoria <= 0 || empty($fecha)) {
            $error = "Por favor, completa todos los campos obligatorios.";
        } else {
            // Verificar que el banco pertenece al usuario
            $stmt_banco = $db->prepare("SELECT COUNT(*) FROM Cuentas_de_banco WHERE ID_banco = ? AND ID_usuario = ?");
            $stmt_banco->bind_param('ii', $id_banco, $user_id);
            $stmt_banco->execute();
            $stmt_banco->bind_result($banco_count);
            $stmt_banco->fetch();
            $stmt_banco->close();

            if ($banco_count == 0) {
                $error = "El banco seleccionado no es v�lido.";
            } else {
                // Verificar que la categor�a pertenece al usuario
                $stmt_categoria = $db->prepare("SELECT COUNT(*) FROM Categorias WHERE ID_categoria = ? AND ID_usuario = ?");
                $stmt_categoria->bind_param('ii', $id_categoria, $user_id);
                $stmt_categoria->execute();
                $stmt_categoria->bind_result($categoria_count);
                $stmt_categoria->fetch();
                $stmt_categoria->close();

                if ($categoria_count == 0) {
                    $error = "La categor�a seleccionada no es v�lida.";
                } else {
                    // Insertar la transacci�n en la base de datos
                    $stmt = $db->prepare("INSERT INTO Transacciones (ID_usuario, ID_banco, monto, ID_categoria, fecha, descripcion) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->bind_param('iidiss', $user_id, $id_banco, $monto, $id_categoria, $fecha, $descripcion);

                    if ($stmt->execute()) {
                        $mensaje = "Transacci�n registrada exitosamente.";
                        // Redirigir o limpiar los campos del formulario si es necesario
                        header('Location: dashboard.php?mensaje=transaccion_creada');
                        exit();
                    } else {
                        $error = "Error al registrar la transacci�n.";
                    }
                    $stmt->close();
                }
            }
        }
    }
}

// Obtener las cuentas bancarias del usuario
$stmt_bancos = $db->prepare("SELECT ID_banco, banco, nombre FROM Cuentas_de_banco WHERE ID_usuario = ?");
$stmt_bancos->bind_param('i', $user_id);
$stmt_bancos->execute();
$result_bancos = $stmt_bancos->get_result();
$bancos = $result_bancos->fetch_all(MYSQLI_ASSOC);
$stmt_bancos->close();

// Obtener las categor�as del usuario
$stmt_categorias = $db->prepare("SELECT ID_categoria, nombre FROM Categorias WHERE ID_usuario = ?");
$stmt_categorias->bind_param('i', $user_id);
$stmt_categorias->execute();
$result_categorias = $stmt_categorias->get_result();
$categorias = $result_categorias->fetch_all(MYSQLI_ASSOC);
$stmt_categorias->close();
?>
close();

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crear Transacci�n</title>

    <!-- Incluir el CSS del header y aside -->
    <link rel="stylesheet" href="header_aside.css">

    <!-- Incluir el CSS espec�fico de la p�gina -->
    <link rel="stylesheet" href="CSS/style.css">

    <!-- Incluir el CSS del formulario -->
    <link rel="stylesheet" href="CSS/transacciones.css">
</head>
<body>

    <!-- Incluir el header y aside -->
    <?php include 'header_aside.php'; ?>

    <main>
        <div class="form-container">
            <h2>Crear Transacci�n</h2>

            <!-- Mostrar mensajes de �xito o error -->
            <?php if (!empty($mensaje)): ?>
                <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <form action="transacciones.php" method="POST" class="form-transaccion">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="form-group">
                    <label for="id_banco">Banco:</label>
                    <select name="id_banco" id="id_banco" required>
                        <option value="">Seleccionar Banco</option>
                        <?php foreach ($bancos as $banco): ?>
                            <option value="<?php echo $banco['ID_banco']; ?>">
                                <?php echo htmlspecialchars($banco['banco'] . ' - ' . $banco['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="monto">Monto:</label>
                    <input type="number" step="0.01" name="monto" id="monto" placeholder="Monto de la transacci�n" required>
                </div>

                <div class="form-group">
                    <label for="id_categoria">Categor�a:</label>
                    <select name="id_categoria" id="id_categoria" required>
                        <option value="">Seleccionar Categor�a</option>
                        <?php foreach ($categorias as $categoria): ?>
                            <option value="<?php echo $categoria['ID_categoria']; ?>">
                                <?php echo htmlspecialchars($categoria['nombre']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha:</label>
                    <input type="date" name="fecha" id="fecha" value="<?php echo date('Y-m-d'); ?>" required>
                </div>

                <div class="button-group">
                    <button type="submit">Agregar Transacci�n</button>
                    <a href="dashboard.php">Regresar</a>
                </div>
            </form>
        </div>
    </main>

    <footer>
        <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
    </footer>

    <!-- Incluir el JS del header y aside -->
    <script src="header_aside.js"></script>

    <!-- Incluir el JS espec�fico de la p�gina -->
    <script src="menu_lateral.js"></script>

</body>
</html>
