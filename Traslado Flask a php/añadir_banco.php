<?php
require 'Conex.inc';
session_start();

function redirigir($url, $param) {
    header("Location: {$url}?{$param}");
    exit();
}

if (!isset($_SESSION['user_id'])) {
    redirigir('index.php', 'error=acceso_denegado');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_banco'])) {
    $banco = trim($_POST['banco']);
    $ID_usuario = $_SESSION['user_id'];

    if (empty($banco)) {
        redirigir('dashboard.php', 'error=banco_vacio');
    }

    $stmt = $db->prepare("SELECT COUNT(*) FROM Cuentas_de_banco WHERE ID_usuario = ? AND banco = ?");
    $stmt->bind_param('is', $ID_usuario, $banco);
    $stmt->execute();
    $stmt->bind_result($count);
    $stmt->fetch();
    $stmt->close();

    if ($count > 0) {
        redirigir('dashboard.php', 'error=banco_existente');
    } else {
        $stmt = $db->prepare("INSERT INTO Cuentas_de_banco (ID_usuario, banco) VALUES (?, ?)");
        $stmt->bind_param('is', $ID_usuario, $banco);

        if ($stmt->execute()) {
            redirigir('dashboard.php', 'success=banco');
        } else {
            redirigir('dashboard.php', 'error=fallo_bd');
        }
    }
} else {
    redirigir('dashboard.php', 'error=solicitud_invalida');
}
?>
