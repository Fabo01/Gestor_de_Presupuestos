<?php
require 'Conex.inc';

// Configurar las cookies de sesión de manera segura antes de iniciar la sesión
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'domain' => '',
    'secure' => isset($_SERVER['HTTPS']),
    'httponly' => true,
    'samesite' => 'Strict',
]);
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];

$mensaje = '';
$error = '';
$usuario = '';
$email = '';
$nombre = '';
$apellido = '';
$nacionalidad = '';
$nacimiento = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $error = "Token CSRF inválido.";
    } else {
        // Recopilar y sanitizar los datos de entrada
        $usuario = trim($_POST['username']);
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $nombre = trim($_POST['name']);
        $apellido = trim($_POST['lastname']);
        $nacionalidad = $_POST['nacionalidad'];
        $nacimiento = $_POST['fechanac'];

        // Validación de entradas
        if (empty($usuario) || empty($email) || empty($password) || empty($confirm_password) || empty($nombre) || empty($apellido) || empty($nacionalidad) || empty($nacimiento)) {
            $error = "Por favor, completa todos los campos.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "El correo electrónico no es válido.";
        } elseif ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $usuario)) {
            $error = "El nombre de usuario debe tener entre 3 y 20 caracteres y solo puede contener letras, números y guiones bajos.";
        } elseif (strlen($password) < 8) {
            $error = "La contraseña debe tener al menos 8 caracteres.";
        } else {
            // Verificar si el usuario es mayor de 18 años
            $fecha_nacimiento = new DateTime($nacimiento);
            $hoy = new DateTime();
            $edad = $hoy->diff($fecha_nacimiento)->y;

            if ($edad < 18) {
                $error = "Debes tener al menos 18 años para registrarte.";
            } else {
                // Verificar si el correo electrónico ya existe
                $stmt = $db->prepare("SELECT 1 FROM Usuarios WHERE email = ? LIMIT 1");
                if (!$stmt) {
                    $error = "Error en la base de datos. Por favor, inténtalo más tarde.";
                } else {
                    $stmt->bind_param('s', $email);
                    $stmt->execute();
                    $stmt->store_result();
                    if ($stmt->num_rows > 0) {
                        $error = "El correo electrónico ya está en uso.";
                    }
                    $stmt->close();
                }

                // Verificar si el nombre de usuario ya existe
                if (empty($error)) {
                    $stmt = $db->prepare("SELECT 1 FROM Usuarios WHERE usuario = ? LIMIT 1");
                    if (!$stmt) {
                        $error = "Error en la base de datos. Por favor, inténtalo más tarde.";
                    } else {
                        $stmt->bind_param('s', $usuario);
                        $stmt->execute();
                        $stmt->store_result();
                        if ($stmt->num_rows > 0) {
                            $error = "El nombre de usuario ya está en uso.";
                        }
                        $stmt->close();
                    }
                }

                if (empty($error)) {
                    // Encriptar la contraseña de forma segura
                    $options = ['cost' => 12];
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);

                    // Iniciar una transacción
                    $db->begin_transaction();

                    try {
                        // Insertar el nuevo usuario en la base de datos
                        $stmt = $db->prepare("INSERT INTO Usuarios (usuario, email, password, nombre, apellido, nacionalidad, nacimiento) VALUES (?, ?, ?, ?, ?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception("Error en la base de datos. Por favor, inténtalo más tarde.");
                        }

                        $stmt->bind_param('sssssss', $usuario, $email, $hashed_password, $nombre, $apellido, $nacionalidad, $nacimiento);

                        if (!$stmt->execute()) {
                            throw new Exception("Error al registrar el usuario. Por favor, inténtalo más tarde.");
                        }

                        // Obtener el ID del nuevo usuario
                        $ID_usuario = $stmt->insert_id;
                        $stmt->close();

                        // Categorías predeterminadas
                        $categorias_predeterminadas = [
                            ['nombre' => 'Salario', 'tipo' => 'ingreso'],
                            ['nombre' => 'Venta', 'tipo' => 'ingreso'],
                            ['nombre' => 'Regalo', 'tipo' => 'ingreso'],
                            ['nombre' => 'Alquiler', 'tipo' => 'gasto'],
                            ['nombre' => 'Comida', 'tipo' => 'gasto'],
                            ['nombre' => 'Transporte', 'tipo' => 'gasto'],
                            ['nombre' => 'Entretenimiento', 'tipo' => 'gasto'],
                            ['nombre' => 'Salud', 'tipo' => 'gasto'],
                            ['nombre' => 'Educación', 'tipo' => 'gasto'],
                            ['nombre' => 'Servicios', 'tipo' => 'gasto'],
                            ['nombre' => 'Otros', 'tipo' => 'gasto'],
                            ['nombre' => 'Acciones', 'tipo' => 'ingreso'],
                        ];

                        // Insertar las categorías predeterminadas
                        $stmt = $db->prepare("INSERT INTO Categorias (ID_usuario, nombre, tipo) VALUES (?, ?, ?)");
                        if (!$stmt) {
                            throw new Exception("Error al crear categorías predeterminadas. Por favor, inténtalo más tarde.");
                        }

                        foreach ($categorias_predeterminadas as $categoria) {
                            $stmt->bind_param('iss', $ID_usuario, $categoria['nombre'], $categoria['tipo']);
                            if (!$stmt->execute()) {
                                throw new Exception("Error al crear categorías predeterminadas. Por favor, inténtalo más tarde.");
                            }
                        }

                        $stmt->close();

                        // Confirmar la transacción
                        $db->commit();

                        header('Location: index.php?registro=exitoso');
                        exit();

                    } catch (Exception $e) {
                        // Revertir la transacción en caso de error
                        $db->rollback();
                        $error = $e->getMessage();
                    }
                }
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="CSS/login.css">
</head>
<body class="login-page">
    <div class="aspect-ratio-container">
        <div class="content-wrapper">
            <header>
                <h1 class="main-header">Crear Cuenta</h1>
            </header>

            <?php if (!empty($mensaje)): ?>
                <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
            <?php endif; ?>

            <?php if (!empty($error)): ?>
                <div class="error"><?php echo htmlspecialchars($error); ?></div>
            <?php endif; ?>

            <main>
                <form action="register.php" method="POST" class="form-style">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label for="username">Nombre de Usuario:</label>
                        <input type="text" name="username" placeholder="Nombre de Usuario" value="<?php echo htmlspecialchars($usuario); ?>" required><br>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" name="email" placeholder="Correo electrónico" value="<?php echo htmlspecialchars($email); ?>" required><br>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" name="password" placeholder="Contraseña" required><br>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña:</label>
                        <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required><br>
                    </div>
                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="text" name="name" placeholder="Nombre" value="<?php echo htmlspecialchars($nombre); ?>" required><br>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Apellido:</label>
                        <input type="text" name="lastname" placeholder="Apellido" value="<?php echo htmlspecialchars($apellido); ?>" required><br>
                    </div>
                    <div class="form-group">
                        <label for="nacionalidad">Nacionalidad:</label>
                        <select name="nacionalidad" required>
                            <option value="">Seleccione su país</option>
                            <option value="Argentina" <?php if ($nacionalidad == 'Argentina') echo 'selected'; ?>>Argentina</option>
                            <option value="Bolivia" <?php if ($nacionalidad == 'Bolivia') echo 'selected'; ?>>Bolivia</option>
                            <option value="Chile" <?php if ($nacionalidad == 'Chile') echo 'selected'; ?>>Chile</option>
                            <option value="Colombia" <?php if ($nacionalidad == 'Colombia') echo 'selected'; ?>>Colombia</option>
                            <option value="Costa Rica" <?php if ($nacionalidad == 'Costa Rica') echo 'selected'; ?>>Costa Rica</option>
                            <option value="Cuba" <?php if ($nacionalidad == 'Cuba') echo 'selected'; ?>>Cuba</option>
                            <option value="Ecuador" <?php if ($nacionalidad == 'Ecuador') echo 'selected'; ?>>Ecuador</option>
                            <option value="El Salvador" <?php if ($nacionalidad == 'El Salvador') echo 'selected'; ?>>El Salvador</option>
                            <option value="España" <?php if ($nacionalidad == 'España') echo 'selected'; ?>>España</option>
                            <option value="Guatemala" <?php if ($nacionalidad == 'Guatemala') echo 'selected'; ?>>Guatemala</option>
                            <option value="Honduras" <?php if ($nacionalidad == 'Honduras') echo 'selected'; ?>>Honduras</option>
                            <option value="México" <?php if ($nacionalidad == 'México') echo 'selected'; ?>>México</option>
                            <option value="Nicaragua" <?php if ($nacionalidad == 'Nicaragua') echo 'selected'; ?>>Nicaragua</option>
                            <option value="Panamá" <?php if ($nacionalidad == 'Panamá') echo 'selected'; ?>>Panamá</option>
                            <option value="Paraguay" <?php if ($nacionalidad == 'Paraguay') echo 'selected'; ?>>Paraguay</option>
                            <option value="Perú" <?php if ($nacionalidad == 'Perú') echo 'selected'; ?>>Perú</option>
                            <option value="Puerto Rico" <?php if ($nacionalidad == 'Puerto Rico') echo 'selected'; ?>>Puerto Rico</option>
                            <option value="República Dominicana" <?php if ($nacionalidad == 'República Dominicana') echo 'selected'; ?>>República Dominicana</option>
                            <option value="Uruguay" <?php if ($nacionalidad == 'Uruguay') echo 'selected'; ?>>Uruguay</option>
                            <option value="Venezuela" <?php if ($nacionalidad == 'Venezuela') echo 'selected'; ?>>Venezuela</option>
                        </select><br>
                    </div>
                    <div class="form-group">
                        <label for="fechanac">Fecha de Nacimiento:</label>
                        <input type="date" name="fechanac" value="<?php echo htmlspecialchars($nacimiento); ?>" required><br>
                    </div>
                    <div class="button-group">
                        <button type="submit">Registrarse</button>
                    </div>
                </form>
                <div class="extra-links">
                    <a href="index.php" class="btn-primary">¿Ya tienes una cuenta? Inicia sesión aquí</a>
                </div>
            </main>
            <footer>
                <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
            </footer>
        </div>
    </div>
</body>
</html>
