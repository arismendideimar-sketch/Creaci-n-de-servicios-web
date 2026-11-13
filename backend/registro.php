<?php

header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: POST");

require_once 'conexion.php';

$data = json_decode(file_get_contents("php://input"), true);

if (isset($data['usuario']) && isset($data['password'])) {
    $usuario = trim($data['usuario']);
    $password = trim($data['password']);

    if (empty($usuario) || empty($password)) {
        http_response_code(400);
        echo json_encode(["estado" => "error", "mensaje" => "El usuario y la contrasena no pueden estar vacios."]);
        exit();
    }

    try {
        $sqlCheck = "SELECT id FROM usuarios WHERE usuario = :usuario";
        $stmtCheck = $conexion->prepare($sqlCheck);
        $stmtCheck->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmtCheck->execute();

        if ($stmtCheck->rowCount() > 0) {
            http_response_code(409);
            echo json_encode(["estado" => "error", "mensaje" => "El nombre de usuario ya se encuentra registrado."]);
            exit();
        }

        $passwordHash = password_hash($password, PASSWORD_BCRYPT);

        $sql = "INSERT INTO usuarios (usuario, password) VALUES (:usuario, :password)";
        $stmt = $conexion->prepare($sql);
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->bindParam(':password', $passwordHash, PDO::PARAM_STR);

        if ($stmt->execute()) {
            http_response_code(201);
            echo json_encode([
                "estado" => "exito",
                "mensaje" => "Registro de usuario satisfactorio!"
            ]);
        } else {
            http_response_code(500);
            echo json_encode(["estado" => "error", "mensaje" => "No se pudo registrar el usuario."]);
        }
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["estado" => "error", "mensaje" => "Error en el servidor: " . $e->getMessage()]);
    }
} else {
    http_response_code(400);
    echo json_encode(["estado" => "error", "mensaje" => "Datos incompletos. Se requiere 'usuario' y 'password'."]);
}
