<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'Conex.inc';

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST' ) {
    if (isset($_POST['action'])) {
        $action = $_POST['action'];

        switch ($action) {
            case 'add_banco':
                // Lógica para agregar un banco
                if (isset($_POST['banco'], $_POST['tipo'], $_POST['nombre'])) {
                    $banco = trim($_POST['banco']);
                    $tipo = trim($_POST['tipo']);
                    $nombre_cnta = trim($_POST['nombre']);
                    $ID_usuario = $_SESSION['user_id'];

                    if (empty($banco)) {
                        header('Location: dashboard.php?error=banco_vacio');
                        exit();
                    }

                    // Verificamos si el banco ya existe para este usuario
                    $stmt = $db->prepare("SELECT COUNT(*) FROM Cuentas_de_banco WHERE ID_usuario = ? AND banco = ? AND tipo = ? AND nombre = ?");
                    $stmt->bind_param('isss', $ID_usuario, $banco, $tipo, $nombre_cnta);
                    $stmt->execute();
                    $stmt->bind_result($count);
                    $stmt->fetch();
                    $stmt->close();

                    if ($count > 0) {
                        header('Location: dashboard.php?error=banco_existente');
                        exit();
                    } else {
                        // Insertar el nuevo banco
                        $stmt = $db->prepare("INSERT INTO Cuentas_de_banco (ID_usuario, banco, tipo, nombre) VALUES (?, ?, ?, ?)");
                        $stmt->bind_param('isss', $ID_usuario, $banco, $tipo, $nombre_cnta);

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
                    header('Location: dashboard.php?error=banco_datos_faltantes');
                    exit();
                }
                break;

            case 'borrar_banco':
                // Lógica para borrar un banco
                if (isset($_POST['ID_banco'])) {
                    $ID_usuario = $_SESSION['user_id'];
                    $banco_id = intval($_POST['ID_banco']);

                    if ($banco_id > 0) {
                        // Verificamos si el banco pertenece al usuario
                        $stmt = $db->prepare("DELETE FROM Cuentas_de_banco WHERE ID_banco = ? AND ID_usuario = ?");
                        $stmt->bind_param("ii", $banco_id, $ID_usuario);

                        if ($stmt->execute()) {
                            header('Location: dashboard.php?success=banco_eliminado');
                            exit();
                        } else {
                            header('Location: dashboard.php?error=eliminar_banco');
                            exit();
                        }
                        $stmt->close();
                    } else {
                        header('Location: dashboard.php?error=banco_id_invalido');
                        exit();
                    }
                } else {
                    header('Location: dashboard.php?error=banco_no_seleccionado');
                    exit();
                }
                break;
        }
    }
}
?>
