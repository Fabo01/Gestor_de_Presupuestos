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

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'No has iniciado sesión.']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!isset($_POST['csrf_token']) || !hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'])) {
        echo json_encode(['success' => false, 'message' => 'Token CSRF inválido.']);
        exit();
    }

    $descripcion = filter_input(INPUT_POST, 'descripcion', FILTER_SANITIZE_STRING);
    $monto = filter_input(INPUT_POST, 'monto', FILTER_VALIDATE_FLOAT);
    $id_categoria = filter_input(INPUT_POST, 'id_categoria', FILTER_VALIDATE_INT);

    if (!$descripcion || $monto === false || $monto === null || !$id_categoria) {
        echo json_encode(['success' => false, 'message' => 'Datos inválidos.']);
        exit();
    }

    $stmt = $db->prepare("
        SELECT C.ID_categoria, CB.ID_cuentabanco
        FROM Categoria C 
        INNER JOIN Cuentas_de_banco CB ON C.ID_cuentabanco = CB.ID_cuentabanco 
        WHERE C.ID_categoria = ? AND CB.ID_usuario = ?
    ");
    if (!$stmt) {
        error_log("Error al preparar la consulta: " . $db->error);
        echo json_encode(['success' => false, 'message' => 'Ocurrió un error al procesar la transacción.']);
        exit();
    }

    $stmt->bind_param("ii", $id_categoria, $_SESSION['user_id']);
    if (!$stmt->execute()) {
        error_log("Error al ejecutar la consulta: " . $stmt->error);
        echo json_encode(['success' => false, 'message' => 'Ocurrió un error al procesar la transacción.']);
        exit();
    }

    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($id_categoria_obtenido, $id_cuentabanco);
        $stmt->fetch();
        $stmt->close();

        $stmt = $db->prepare("INSERT INTO Transacciones (`desc`, Monto, fecha, ID_Categoria, ID_Cuentabanco) VALUES (?, ?, NOW(), ?, ?)");
        if (!$stmt) {
            error_log("Error al preparar la consulta: " . $db->error);
            echo json_encode(['success' => false, 'message' => 'Ocurrió un error al procesar la transacción.']);
            exit();
        }

        $stmt->bind_param("sdii", $descripcion, $monto, $id_categoria, $id_cuentabanco);

        if ($stmt->execute()) {
            $stmt->close();

            $fecha_actual = date('Y-m-d H:i:s');

            $stmt = $db->prepare("UPDATE Categoria SET gasto_acumulado = gasto_acumulado + ?, saldo_restante = saldo_restante - ? WHERE ID_categoria = ?");
            if (!$stmt) {
                error_log("Error al preparar la consulta: " . $db->error);
                echo json_encode(['success' => false, 'message' => 'Ocurrió un error al actualizar la categoría.']);
                exit();
            }

            $stmt->bind_param("ddi", $monto, $monto, $id_categoria);
            if (!$stmt->execute()) {
                error_log("Error al ejecutar la actualización: " . $stmt->error);
                echo json_encode(['success' => false, 'message' => 'Ocurrió un error al actualizar la categoría.']);
                exit();
            }

            $stmt->close();

            $stmt = $db->prepare("SELECT gasto_acumulado, saldo_restante FROM Categoria WHERE ID_categoria = ?");
            if (!$stmt) {
                error_log("Error al preparar la consulta: " . $db->error);
                echo json_encode(['success' => false, 'message' => 'Ocurrió un error al obtener los datos actualizados.']);
                exit();
            }

            $stmt->bind_param("i", $id_categoria);
            if (!$stmt->execute()) {
                error_log("Error al ejecutar la consulta: " . $stmt->error);
                echo json_encode(['success' => false, 'message' => 'Ocurrió un error al obtener los datos actualizados.']);
                exit();
            }

            $stmt->bind_result($gasto_acumulado_actualizado, $saldo_restante_actualizado);
            $stmt->fetch();
            $stmt->close();

            $gasto_acumulado_formateado = number_format($gasto_acumulado_actualizado, 2);
            $saldo_restante_formateado = number_format($saldo_restante_actualizado, 2);

            echo json_encode([
                'success' => true,
                'message' => 'Transacción agregada exitosamente.',
                'transaccion' => [
                    'desc' => htmlspecialchars($descripcion, ENT_QUOTES, 'UTF-8'),
                    'Monto' => number_format($monto, 2),
                    'fecha' => $fecha_actual
                ],
                'gasto_acumulado' => $gasto_acumulado_formateado,
                'saldo_restante' => $saldo_restante_formateado
                
            ]);
            exit();
        } else {
            error_log("Error al insertar la transacción: " . $stmt->error);
            echo json_encode(['success' => false, 'message' => 'Error al insertar la transacción.']);
            exit();
        }
    } else {
        echo json_encode(['success' => false, 'message' => 'No tienes permiso para agregar transacciones a esta categoría.']);
        exit();
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Método de solicitud inválido.']);
    exit();
}
?>
