<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

require 'connection.php';

$method = $_SERVER['REQUEST_METHOD'];
$id = $_GET['id'] ?? null;
$action = $_GET['action'] ?? null; 

$input = json_decode(file_get_contents('php://input'), true);

if ($action === 'login' && $method === 'POST') {
    $stmt = $conn->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$input['email']]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($input['pass'], $user['pass'])) {
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
            $stmt = $conn->query("SELECT * FROM users ORDER BY id DESC");
            echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
        }
        break;

    case 'POST':
        $sql = "INSERT INTO users (fullName, email, pass, cpf, birth_date, role) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $pass = password_hash($input['pass'], PASSWORD_DEFAULT);
        try {
            $stmt->execute([
                $input['fullName'], $input['email'], $pass, 
                $input['cpf'] ?? '', $input['birth_date'] ?? null, $input['role'] ?? 'user'
            ]);
            echo json_encode(["message" => "User created"]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(["message" => "Error creating user: " . $e->getMessage()]);
        }
        break;

    case 'PUT':
        if (!$id) { http_response_code(400); echo json_encode(["message" => "ID missing"]); exit; }
        
        $fields = []; $params = [];
        if (isset($input['fullName'])) { $fields[] = "fullName=?"; $params[] = $input['fullName']; }
        if (isset($input['cpf'])) { $fields[] = "cpf=?"; $params[] = $input['cpf']; }
        if (isset($input['birth_date'])) { $fields[] = "birth_date=?"; $params[] = $input['birth_date']; }
        if (isset($input['role'])) { $fields[] = "role=?"; $params[] = $input['role']; }
        
        if (count($fields) > 0) {
            $sql = "UPDATE users SET " . implode(", ", $fields) . " WHERE id=?";
            $params[] = $id;
            $stmt = $conn->prepare($sql);
            $stmt->execute($params);
        }
        echo json_encode(["message" => "user updated successfully"]);
        break;

    case 'DELETE':
        if (!$id) { http_response_code(400); echo json_encode(["message" => "ID missing"]); exit; }
        $stmt = $conn->prepare("DELETE FROM users WHERE id=?");
        $stmt->execute([$id]);
        echo json_encode(["message" => "user deleted successfully"]);
        break;
}
?>