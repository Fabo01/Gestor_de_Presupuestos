<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'Conex.inc';
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
$username = '';
$email = '';
$name = '';
$lastname = '';
$nacionalidad = '';
$fechanac = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['token']) || !hash_equals($_SESSION['token'], $_POST['token'])) {
        $error = "Token CSRF inválido.";
    } else {
        $username = htmlspecialchars(trim($_POST['username']));
        $email = htmlspecialchars(trim($_POST['email']));
        $password = $_POST['password'];
        $confirm_password = $_POST['confirm_password'];
        $name = htmlspecialchars(trim($_POST['name']));
        $lastname = htmlspecialchars(trim($_POST['lastname']));
        $nacionalidad = htmlspecialchars(trim($_POST['nacionalidad']));
        $fechanac = htmlspecialchars(trim($_POST['fechanac']));

        if (empty($username) || empty($email) || empty($password) || empty($confirm_password) || empty($name) || empty($lastname) || empty($nacionalidad) || empty($fechanac)) {
            $error = "Por favor, completa todos los campos.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error = "El correo electrónico no es válido.";
        } elseif ($password !== $confirm_password) {
            $error = "Las contraseñas no coinciden.";
        } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
            $error = "El nombre de usuario debe tener entre 3 y 20 caracteres y solo puede contener letras, números y guiones bajos.";
        } elseif (strlen($password) < 8) {
            $error = "La contraseña debe tener al menos 8 caracteres.";
        } else {
            $stmt = $db->prepare("SELECT COUNT(*) FROM Usuarios WHERE email = ? OR usuario = ?");
            $stmt->bind_param('ss', $email, $username);
            $stmt->execute();
            $stmt->bind_result($count);
            $stmt->fetch();
            $stmt->close();

            if ($count > 0) {
                $error = "El correo electrónico o nombre de usuario ya están en uso.";
            } else {
                $options = ['cost' => 12];
                $hashed_password = password_hash($password, PASSWORD_BCRYPT, $options);

                $stmt = $db->prepare("INSERT INTO Usuarios (usuario, email, password, nombre, apellido, nacionalidad, nacimiento) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->bind_param('sssssss', $username, $email, $hashed_password, $name, $lastname, $nacionalidad, $fechanac);

                if ($stmt->execute()) {
                    header('Location: index.php?registro=exitoso');
                    exit();
                } else {
                    $error = "Error al registrar el usuario.";
                }
                $stmt->close();
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
    <title>Registro de Usuario</title>
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-page">
    <div class="aspect-ratio-container">
        <div class="content-wrapper">
            <header>
                <h1 class="main-header">Crear Cuenta</h1>
            </header>

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

            <main>
                <form action="register.php" method="POST" class="form-style">
                    <input type="hidden" name="token" value="<?php echo $token; ?>">
                    <div class="form-group">
                        <label for="username">Nombre de Usuario:</label>
                        <input type="text" name="username" placeholder="Nombre de Usuario" value="<?php echo htmlspecialchars($username); ?>" required><br>
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
                        <input type="text" name="name" placeholder="Nombre" value="<?php echo htmlspecialchars($name); ?>" required><br>
                    </div>
                    <div class="form-group">
                        <label for="lastname">Apellido:</label>
                        <input type="text" name="lastname" placeholder="Apellido" value="<?php echo htmlspecialchars($lastname); ?>" required><br>
                    </div>
                    <div class="form-group">
                        <label for="nacionalidad">Nacionalidad:</label>
                        <input type="text" name="nacionalidad" placeholder="Nacionalidad" value="<?php echo htmlspecialchars($nacionalidad); ?>" required><br>
                    </div>
                    <div class="form-group">
                        <label for="fechanac">Fecha de Nacimiento:</label>
                        <input type="date" name="fechanac" placeholder="Fecha de Nacimiento" value="<?php echo htmlspecialchars($fechanac); ?>" required><br>
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
