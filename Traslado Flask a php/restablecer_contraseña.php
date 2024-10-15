<?php
require 'Conex.inc';
session_start();

$error = '';
$mensaje = '';
$password = '';
$confirm_password = '';
$token_valido = false;

if (isset($_GET['token'], $_GET['email'])) {
    $token = $_GET['token'];
    $email = $_GET['email'];

    $token = trim($token);
    $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);

    if (!empty($token) && !empty($email) && filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $stmt = $db->prepare("SELECT ID_usuario, token_recuperacion, token_expira FROM Usuario WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($ID_usuario, $token_hash, $token_expira);
        $stmt->fetch();
        $stmt->close();

        if ($ID_usuario && $token_hash && $token_expira) {
            if (strtotime($token_expira) >= time()) {
                if (password_verify($token, $token_hash)) {
                    $token_valido = true;
                } else {
                    $error = "El enlace de restablecimiento es inválido o ha expirado.";
                }
            } else {
                $error = "El enlace de restablecimiento ha expirado.";
            }
        } else {
            $error = "El enlace de restablecimiento es inválido.";
        }
    } else {
        $error = "El enlace de restablecimiento es inválido.";
    }
} else {
    $error = "Parámetros inválidos.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['token'], $_POST['email'])) {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['token'], $_POST['csrf_token'])) {
        $error = "Token CSRF inválido.";
    } else {
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);
        $token = trim($_POST['token']);

        if (empty($password) || empty($confirm_password)) {
            $error = "Por favor, completa todos los campos.";
        } elseif ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden.";
        } elseif (strlen($password) < 8) {
            $error = "La contraseña debe tener al menos 8 caracteres.";
        } else {
            $stmt = $db->prepare("SELECT ID_usuario, token_recuperacion, token_expira FROM Usuario WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->bind_result($ID_usuario, $token_hash, $token_expira);
            $stmt->fetch();
            $stmt->close();

            if ($ID_usuario && $token_hash && $token_expira) {
                if (strtotime($token_expira) >= time() && password_verify($token, $token_hash)) {
                    $hashed_password = password_hash($password, PASSWORD_BCRYPT);

                    $stmt = $db->prepare("UPDATE Usuario SET password = ?, token_recuperacion = NULL, token_expira = NULL WHERE ID_usuario = ?");
                    $stmt->bind_param('si', $hashed_password, $ID_usuario);

                    if ($stmt->execute()) {
                        $mensaje = "Tu contraseña ha sido restablecida exitosamente. Ahora puedes iniciar sesión.";
                        $token_valido = false;
                    } else {
                        $error = "Error al actualizar la contraseña. Por favor, inténtalo de nuevo.";
                    }
                    $stmt->close();
                } else {
                    $error = "El enlace de restablecimiento es inválido o ha expirado.";
                }
            } else {
                $error = "El enlace de restablecimiento es inválido.";
            }
        }
    }
}
?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Restablecer Contraseña</title>
    <link rel="stylesheet" href="CSS/login.css">
</head>
<body class="login-page">
    <div class="aspect-ratio-container">
        <div class="content-wrapper">
            <header>
                <h1 class="main-header">Restablecer Contraseña</h1>
            </header>
            <main>
    
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                <?php if (!empty($mensaje)): ?>
                    <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
                    <div class="extra-links">
                        <a href="index.php">Iniciar Sesión</a>
                    </div>
                <?php elseif ($token_valido): ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                    <?php if (!empty($error)): ?>
                        <div class="error"><?php echo htmlspecialchars($error); ?></div>
                    <?php endif; ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

                    <form action="restablecer_contraseña.php" method="POST" class="form-style">
                        <input type="hidden" name="csrf_token" value="<?php echo $_SESSION['token']; ?>">
                        <input type="hidden" name="token" value="<?php echo htmlspecialchars($token); ?>">
                        <input type="hidden" name="email" value="<?php echo htmlspecialchars($email); ?>">
                        <div class="form-group">
                            <label for="password">Nueva Contraseña:</label>
                            <input type="password" name="password" placeholder="Nueva Contraseña" required><br>
                        </div>
                        <div class="form-group">
                            <label for="confirm_password">Confirmar Contraseña:</label>
                            <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required><br>
                        </div>
                        <div class="button-group">
                            <button type="submit" class="btn-primary">Restablecer Contraseña</button>
                        </div>
                    </form>
                    
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                <?php else: ?>
                    <?php if (!empty($error)): ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

                        <div class="error"><?php echo htmlspecialchars($error); ?></div>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                    <?php endif; ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

                    <div class="extra-links">
                        <a href="recuperar_contraseña.php">Solicitar un nuevo enlace de restablecimiento</a>
                    </div>
    
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                <?php endif; ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

            </main>
            <footer>
                <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
            </footer>
        </div>
    </div>
</body>
</html>
