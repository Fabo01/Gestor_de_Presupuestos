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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_FILES['foto_perfil']) && $_FILES['foto_perfil']['error'] === UPLOAD_ERR_OK) {
        $file_tmp_path = $_FILES['foto_perfil']['tmp_name'];
        $file_name = $_FILES['foto_perfil']['name'];
        $file_size = $_FILES['foto_perfil']['size'];
        $file_type = $_FILES['foto_perfil']['type'];

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif'];
        if (in_array($file_type, $allowed_types)) {
            if ($file_size <= 2 * 1024 * 1024) {
                $file_extension = pathinfo($file_name, PATHINFO_EXTENSION);
                $new_file_name = uniqid('perfil_', true) . '.' . $file_extension;

                $upload_dir = 'uploads/';
                if (!is_dir($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }

                $dest_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp_path, $dest_path)) {
                    $stmt = $db->prepare("UPDATE Usuario SET foto_perfil = ? WHERE ID_usuario = ?");
                    if ($stmt) {
                        $stmt->bind_param('si', $new_file_name, $user_id);
                        if ($stmt->execute()) {
                            $stmt->close();
                            header('Location: ver_perfil.php');
                            exit();
                        } else {
                            error_log("Error al actualizar la foto de perfil: " . $stmt->error);
                            $error_message = "Error al actualizar la foto de perfil. Por favor, inténtalo de nuevo.";
                        }
                    } else {
                        error_log("Error al preparar la consulta: " . $db->error);
                        $error_message = "Error al actualizar la foto de perfil. Por favor, inténtalo de nuevo.";
                    }
                } else {
                    $error_message = "Error al mover el archivo al directorio de destino.";
                }
            } else {
                $error_message = "El archivo es demasiado grande. El tamaño máximo permitido es 2MB.";
            }
        } else {
            $error_message = "Tipo de archivo no permitido. Solo se permiten imágenes JPG, PNG y GIF.";
        }
    } else {
        $error_message = "No se ha seleccionado ningún archivo o ha ocurrido un error al subir el archivo.";
    }

    if (isset($error_message)) {
        echo "<p>$error_message</p>";
    }
} else {
    header('Location: ver_perfil.php');
    exit();
}
?>