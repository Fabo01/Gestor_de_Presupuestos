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

$ID_usuario = filter_var($_SESSION['user_id'], FILTER_VALIDATE_INT);
if (!$ID_usuario) {
    session_destroy();
    header('Location: index.php');
    exit();
}

$id_articulo = filter_input(INPUT_GET, 'id_articulo', FILTER_VALIDATE_INT);
if (!$id_articulo) {
    echo "Artículo no válido.";
    exit();
}

$stmt = $db->prepare("SELECT ID_articulo FROM Articulos WHERE ID_articulo = ? AND ID_usuario = ?");
if (!$stmt) {
    error_log("Error al preparar la consulta: " . $db->error);
    echo "Ocurrió un error al eliminar el artículo.";
    exit();
}

$stmt->bind_param('ii', $id_articulo, $ID_usuario);
if (!$stmt->execute()) {
    error_log("Error al ejecutar la consulta: " . $stmt->error);
    echo "Ocurrió un error al eliminar el artículo.";
    exit();
}

$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "No tienes permiso para eliminar este artículo.";
    exit();
}
$stmt->close();

$stmt = $db->prepare("DELETE FROM Articulos WHERE ID_articulo = ? AND ID_usuario = ?");
if (!$stmt) {
    error_log("Error al preparar la consulta: " . $db->error);
    echo "Ocurrió un error al eliminar el artículo.";
    exit();
}

$stmt->bind_param('ii', $id_articulo, $ID_usuario);
if ($stmt->execute()) {
    $stmt->close();
    header('Location: mis_articulos.php?mensaje=eliminado');
    exit();
} else {
    error_log("Error al ejecutar la consulta: " . $stmt->error);
    echo "Ocurrió un error al eliminar el artículo.";
    exit();
}
?>
