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

$login_input = '';
$error = '';

// Habilitar la visualización de errores (solo en desarrollo)
ini_set('display_errors', 1);
error_reporting(E_ALL);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $error = "Token CSRF inválido.";
    } else {
        $login_input = trim($_POST['login_input']);
        $password = $_POST['password'];

        if (empty($login_input) || empty($password)) {
            $error = "Por favor, completa todos los campos.";
        } else {
            $max_attempts = 5;
            $lockout_time = 15 * 60;

            if (!isset($_SESSION['login_attempts'])) {
                $_SESSION['login_attempts'] = 0;
                $_SESSION['last_attempt_time'] = time();
            }

            if ($_SESSION['login_attempts'] >= $max_attempts) {
                $time_since_last_attempt = time() - $_SESSION['last_attempt_time'];
                if ($time_since_last_attempt < $lockout_time) {
                    $remaining_time = ceil(($lockout_time - $time_since_last_attempt) / 60);
                    $error = "Demasiados intentos fallidos. Por favor, inténtalo de nuevo en $remaining_time minutos.";
                } else {
                    $_SESSION['login_attempts'] = 0;
                    $_SESSION['last_attempt_time'] = time();
                }
            }

            if (empty($error)) {
                // Buscar al usuario por correo electrónico o nombre de usuario
                $stmt = $db->prepare("SELECT ID_usuario, usuario, nombre, password FROM Usuarios WHERE email = ? OR usuario = ?");
                if (!$stmt) {
                    $error = "Error en la base de datos. Por favor, inténtalo más tarde.";
                } else {
                    $stmt->bind_param('ss', $login_input, $login_input);
                    $stmt->execute();
                    $stmt->store_result();

                    if ($stmt->num_rows > 0) {
                        $stmt->bind_result($ID_usuario, $usuario, $nombre, $hashed_password);
                        $stmt->fetch();

                        if (password_verify($password, $hashed_password)) {
                            session_regenerate_id(true);
                            $_SESSION['user_id'] = $ID_usuario;
                            $_SESSION['usuario'] = $usuario;
                            $_SESSION['nombre'] = $nombre;
                            $_SESSION['login_attempts'] = 0;
                            header('Location: dashboard.php');
                            exit();
                        } else {
                            $error = "Nombre de usuario/correo electrónico o contraseña incorrectos.";
                            $_SESSION['login_attempts'] += 1;
                            $_SESSION['last_attempt_time'] = time();
                        }
                    } else {
                        $error = "Nombre de usuario/correo electrónico o contraseña incorrectos.";
                        $_SESSION['login_attempts'] += 1;
                        $_SESSION['last_attempt_time'] = time();
                    }
                    $stmt->close();
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
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="CSS/login.css">
</head>

<body class="login-page">
    <div class="aspect-ratio-container">
        <div class="content-wrapper">
            <header>
                <h1 class="main-header">Iniciar Sesión</h1>
            </header>
            <main>

                <?php if (!empty($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>

                <form action="index.php" method="POST" class="form-style">
                    <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                    <div class="form-group">
                        <label for="login_input">Correo o Usuario:</label>
                        <input type="text" name="login_input" placeholder="Correo electrónico o nombre de usuario" value="<?php echo htmlspecialchars($login_input); ?>" required><br>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" name="password" placeholder="Contraseña" required><br>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn-primary">Iniciar Sesión</button>
                    </div>
                </form>
                <div class="extra-links">
                    <a href="register.php">¿No tienes una cuenta? Regístrate aquí</a>
                    <p><a href="recuperar_contraseña.php">Olvidé mi Contraseña</a></p>
                </div>
            </main>
            <footer>
                <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
            </footer>
        </div>
    </div>
</body>
</html>