<?php
require 'Conex.inc';
session_start();

$error = '';
$mensaje = '';
$email = '';

if (empty($_SESSION['token'])) {
    $_SESSION['token'] = bin2hex(random_bytes(32));
}
$token = $_SESSION['token'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $error = "Token CSRF inválido.";
    } else {
        $email = filter_var(trim($_POST['email']), FILTER_SANITIZE_EMAIL);

        if (empty($email)) {
            $error = "Por favor, ingresa tu correo electrónico.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "El correo electrónico no es válido.";
        } else {
            $stmt = $db->prepare("SELECT ID_usuario FROM Usuario WHERE email = ?");
            $stmt->bind_param('s', $email);
            $stmt->execute();
            $stmt->store_result();

            if ($stmt->num_rows > 0) {
                $reset_token = bin2hex(random_bytes(32));
                $token_hash = password_hash($reset_token, PASSWORD_DEFAULT);
                $token_expiry = date('Y-m-d H:i:s', time() + 3600);

                $stmt->bind_result($ID_usuario);
                $stmt->fetch();
                $stmt->close();

                $stmt = $db->prepare("UPDATE Usuario SET token_recuperacion = ?, token_expira = ? WHERE ID_usuario = ?");
                $stmt->bind_param('ssi', $token_hash, $token_expiry, $ID_usuario);
                $stmt->execute();
                $stmt->close();

                $reset_link = "https://pillan.inf.uct.cl/~evejar/TallerDeIntegracion/restablecer_contraseña.php?token=" . urlencode($reset_token) . "&email=" . urlencode($email);

                $to = $email;
                $subject = "Restablecimiento de Contraseña";
                $message = "Hola,\n\nHemos recibido una solicitud para restablecer tu contraseña. Por favor, haz clic en el siguiente enlace para restablecer tu contraseña:\n\n$reset_link\n\nSi no solicitaste este cambio, puedes ignorar este correo electrónico.\n\nGracias.";
                $headers = "From: no-reply@pillan.inf.uct.cl\r\n";
                $headers .= "Reply-To: itan.daniel.fr@gmail.com\r\n";
                $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

                if (mail($to, $subject, $message, $headers)) {
                    $mensaje = "Se ha enviado un enlace de restablecimiento de contraseña a tu correo electrónico.";
                } else {
                    $error = "Error al enviar el correo electrónico. Por favor, inténtalo de nuevo más tarde.";
                }
            } else {
                $error = "Si el correo electrónico está registrado, recibirás un enlace para restablecer tu contraseña.";
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
    <title>Recuperar Contraseña</title>
    <link rel="stylesheet" href="CSS/login.css">
</head>
<body class="login-page">
    <div class="aspect-ratio-container">
        <div class="content-wrapper">
            <header>
                <h1 class="main-header">Recuperar Contraseña</h1>
            </header>
            <main>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                <?php if (!empty($mensaje)): ?>
                    <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
                <?php endif; ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>
                <?php if (!empty($error)): ?>
                    <div class="error"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
<!------------------------------------------------------------------------------------------------------------------------------------------------------------------>

                <form action="recuperar_contraseña.php" method="POST" class="form-style">
                    <input type="hidden" name="token" value="<?php echo $token; ?>">
                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" name="email" placeholder="Correo electrónico" value="<?php echo htmlspecialchars($email); ?>" required><br>
                    </div>
                    <div class="button-group">
                        <button type="submit" class="btn-primary">Enviar Enlace de Restablecimiento</button>
                    </div>
                </form>
                <div class="extra-links">
                    <a href="index.php">Iniciar Sesión</a>
                </div>
            </main>
            <footer>
                <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
            </footer>
        </div>
    </div>
</body>
</html>
