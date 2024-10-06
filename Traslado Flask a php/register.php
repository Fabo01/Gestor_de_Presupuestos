<!-------------------------------------------- Codigo php -------------------------------------->
<?php
require 'Conex.inc';
session_start();

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit();
}

$mensaje = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if (empty($name) || empty($email) || empty($password) || empty($confirm_password)) {
        $error = "Por favor, completa todos los campos.";
    } elseif ($password !== $confirm_password) {
        $error = "Las contraseñas no coinciden.";
    } else {
        $stmt = $db->prepare("SELECT COUNT(*) FROM Usuarios WHERE email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count > 0) {
            $error = "Error: Ya existe una cuenta con este correo electrónico.";
        } else {
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);

            $stmt = $db->prepare("INSERT INTO Usuarios (name, email, password) VALUES (?, ?, ?)");
            $stmt->bind_param('sss', $name, $email, $hashed_password);

            if ($stmt->execute()) {
                $mensaje = "Registro exitoso. Puedes iniciar sesión ahora.";
            } else {
                $error = "Error al registrar el usuario.";
            }
            $stmt->close();
        }
    }
}
?>
<!---------------------------------------------------------------------------------------------------------------------------->

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-page">
    <div class="aspect-ratio-container">
        <div class="content-wrapper">
            <header>
                <h1 class="main-header">Crear Cuenta</h1>
            </header>
<!-------------------------------------------- Codigo php -------------------------------------->
        <?php if (!empty($mensaje)): ?>
            <div class="mensaje"><?php echo htmlspecialchars($mensaje); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>
<!---------------------------------------------------------------------------------------------------------------------------->

            <main>
                <form action="register.php" method="POST" class="form-style">
                    <div class="form-group">
                        <label for="name">Nombre:</label>
                        <input type="text" name="name" placeholder="Nombre" required><br>
                    </div>
                    <div class="form-group">
                        <label for="email">Correo Electrónico:</label>
                        <input type="email" name="email" placeholder="Correo electrónico" required><br>
                    </div>
                    <div class="form-group">
                        <label for="password">Contraseña:</label>
                        <input type="password" name="password" placeholder="Contraseña" required><br>
                    </div>
                    <div class="form-group">
                        <label for="confirm_password">Confirmar Contraseña:</label>
                        <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required><br>
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
