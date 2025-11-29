<?php
require 'config.php';

$method = $_SERVER['REQUEST_METHOD'];
$data = json_decode(file_get_contents("php://input"), true);
$id = isset($_GET['id']) ? $_GET['id'] : null;

if ($method == 'POST' && isset($_GET['action']) && $_GET['action'] == 'login') {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$data['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($data['pass'], $user['pass'])) {
        unset($user['pass']);
        echo json_encode(["user" => $user]);
    } else {
        http_response_code(401);
        echo json_encode(["message" => "invalid user e/or password"]);
    }
    exit;
}

switch ($method) {
    case 'GET':
        if ($id) {
            $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode($stmt->fetch(PDO::FETCH_ASSOC));
        } else {
            $stmt = $conn->query("SELECT * FROM users");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $sql = "INSERT INTO users (fullName, email, pass, cpf, birth_date, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $pass = password_hash($data['pass'], PASSWORD_DEFAULT);
        
        try {
            $stmt->execute([
                $data['fullName'], 
                $data['email'], 
                $pass, 
                $data['cpf'] ?? '', 
                $data['birth_date'] ?? null, 
                $data['role']
            ]);
            echo json_encode(["message" => "User created successfully"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Error creating user: " . $e->getMessage()]);
        }
        break;

    case 'PUT':
        if (!$id) die(json_encode(["message" => "ID required"]));
        
        $fields = [];
        $params = [];
        
        if (isset($data['fullName'])) { $fields[] = "fullName=?"; $params[] = $data['fullName']; }
        if (isset($data['cpf'])) { $fields[] = "cpf=?"; $params[] = $data['cpf']; }
        if (isset($data['birth_date'])) { $fields[] = "birth_date=?"; $params[] = $data['birth_date']; }
        if (isset($data['role'])) { $fields[] = "role=?"; $params[] = $data['role']; }
        
        if (count($fields) > 0) {
            $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id=?";
            $params[] = $id;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
        echo json_encode(["message" => "user updated successfully"]);
        break;

    case 'DELETE':
        if (!$id) die(json_encode(["message" => "id required to delete a user"]));
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "user deeleted successfully"]);
        break;
}
?>