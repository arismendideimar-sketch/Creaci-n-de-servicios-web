<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once 'conexion.php';

if (!isset($conexion)) {
    http_response_code(500);
    echo json_encode(["estado" => "error", "mensaje" => "Error critico: No se pudo establecer la conexion a la base de datos."]);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['usuario']) && isset($data['password'])) {
    $usuario = trim($data['usuario']);
    $password = trim($data['password']);

    if (empty($usuario) || empty($password)) {
        http_response_code(400);
        echo json_encode(["estado" => "error", "mensaje" => "Debe ingresar el usuario y la contrasena."]);
        exit();
    }

    try {
        $sql = "SELECT id, usuario, password FROM usuarios WHERE usuario = :usuario";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->execute();

        if ($stmt->rowCount() == 1) {
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (password_verify($password, $user['password'])) {
                http_response_code(200);
                echo json_encode([
                    "estado" => "exito",
                    "mensaje" => "Autenticacion satisfactoria! Bienvenido " . $user['usuario']
                ]);
            } else {
                http_response_code(401);
                echo json_encode([
                    "estado" => "error",
                    "mensaje" => "Error en la autenticacion: Contrasena incorrecta."
                ]);
            }
        } else {
            http_response_code(401);
            echo json_encode([
                "estado" => "error",
                "mensaje" => "Error en la autenticacion: El usuario no existe."
            ]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["estado" => "error", "mensaje" => "Error en el servidor: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["estado" => "error", "mensaje" => "Faltan parametros obligatorios."]);
}
