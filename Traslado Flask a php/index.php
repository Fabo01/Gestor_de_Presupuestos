<!-------------------------------------------- Codigo php -------------------------------------->
<?php
require 'Conex.inc';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    if (empty($email) || empty($password)) {
        $error = "Por favor, completa todos los campos.";
    } else {
        $stmt = $db->prepare("SELECT ID_usuario, name, password FROM Usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($ID_usuario, $nombre, $hashed_password);
        $stmt->fetch();
        $stmt->close();

        if ($ID_usuario && password_verify($password, $hashed_password)) {
            $_SESSION['user_id'] = $ID_usuario;
            $_SESSION['user'] = $nombre;
            header('Location: dashboard.php');
            exit();
        } else {
            $error = "Correo electrónico o contraseña incorrectos.";
        }
    }
}
?>
<!---------------------------------------------------------------------------------------------------------------------------->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <link rel="stylesheet" href="css/login.css">
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
                        <div class="form-group">
                            <label for="email">Correo Electrónico:</label>
                            <input type="email" name="email" placeholder="Correo electrónico" required><br>
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
                    <p>Olvidé mi Contraseña <a href="#">Restablecer Contraseña</a></p>
                    </div>
            </main>
            <footer>
                <p>&copy; Gestor de Presupuestos 2024. Todos los derechos reservados.</p>
            </footer>
        </div>
    </div>
</body>
</html>
