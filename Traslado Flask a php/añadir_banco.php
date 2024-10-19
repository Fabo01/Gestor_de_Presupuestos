<?php
require 'Conex.inc';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_banco'])) {
    $banco = trim($_POST['banco-name']);
    $tipo = trim($_POST['tipo-cnta']);
    $cuenta = trim($_POST['cuenta-name']);
    $ID_usuario = $_SESSION['user_id'];

    if (empty($banco)) {
        header('Location: dashboard.php?error=banco_vacio');
        exit();
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM Cuentas_de_banco WHERE ID_usuario = ? AND nombre_banco = ? AND tipo_cuenta = ? AND nombre_cuenta = ? ");
    $stmt->bind_param('isss', $ID_usuario, $banco, $tipo, $cuenta);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        header('Location: dashboard.php?error=banco_existente');
        exit();
    } else {
        $stmt = $db->prepare("INSERT INTO Cuentas_de_banco (ID_usuario, nombre_banco, tipo_cuenta, nombre_cuenta) VALUES (?, ?, ?, ?)");
        $stmt->bind_param('isss', $ID_usuario, $banco, $tipo, $cuenta);

        if ($stmt->execute()) {
            header('Location: dashboard.php?success=banco');
            exit();
        } else {
            header('Location: dashboard.php?error=banco_insert');
            exit();
        }
        $stmt->close();
    }
} else {
    header('Location: dashboard.php');
    exit();
}
?>
