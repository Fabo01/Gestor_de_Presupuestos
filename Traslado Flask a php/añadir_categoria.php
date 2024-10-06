<?php
require 'Conex.inc';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_categoria'])) {
    $ID_cuentabanco = $_POST['cuenta'];
    $categoria = trim($_POST['categoria']);

    if (empty($categoria)) {
        header('Location: dashboard.php?error=categoria_vacia');
        exit();
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM Categoria WHERE ID_cuentabanco = ? AND nombre = ?");
    $stmt->bind_param('is', $ID_cuentabanco, $categoria);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        header('Location: dashboard.php?error=categoria_existente');
        exit();
    } else {
        $stmt = $db->prepare("INSERT INTO Categoria (ID_cuentabanco, nombre) VALUES (?, ?)");
        $stmt->bind_param('is', $ID_cuentabanco, $categoria);

        if ($stmt->execute()) {
            header('Location: dashboard.php?success=categoria');
            exit();
        } else {
            header('Location: dashboard.php?error=categoria_insert');
            exit();
        }
        $stmt->close();
    }
} else {
    header('Location: dashboard.php');
    exit();
}
?>
