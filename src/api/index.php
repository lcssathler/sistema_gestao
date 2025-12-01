<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

$conn = require_once 'init_db.php'; 

function handleLogin($conn) {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        http_response_code(405);
        echo json_encode(["message" => "Method not allowed for login"]);
        return;
    }

    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->email) || !isset($data->password)) {
        http_response_code(400);
        echo json_encode(["message" => "Email and password are required."]);
        return;
    }

    $email = $data->email;
    $password = $data->password;

    try {
        $stmt = $conn->prepare("SELECT id, fullName AS name, email, pass, role FROM users WHERE email = :email");
        $stmt->bindParam(':email', $email);
        $stmt->execute();
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            http_response_code(401);
            echo json_encode(["message" => "Email or password incorrect"]);
            return;
        }

        if (password_verify($password, $user['pass'])) {
            http_response_code(200);
            
            unset($user['pass']); 
            
            echo json_encode(["message" => "Login successfully", "user" => $user]);
        } else {
            http_response_code(401);
            echo json_encode(["message" => "Email or password incorrect"]);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "error on server side: " . $e->getMessage()]);
    }
}

function handlePostUser($conn) {
    $data = json_decode(file_get_contents("php://input"));

    if (!isset($data->name) || !isset($data->email) || !isset($data->password)) {
        http_response_code(400);
        echo json_encode(["message" => "fields required"]);
        return;
    }

    $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
    
    $cpf = $data->cpf ?? null;
    $birth_date = $data->birth_date ?? null;
    $role = $data->role ?? 'user'; 

    try {
        $sql = "INSERT INTO users (fullName, email, pass, cpf, birth_date, role) 
                VALUES (:name, :email, :pass, :cpf, :birth_date, :role)";
        
        $stmt = $conn->prepare($sql);
        
        $stmt->bindParam(':name', $data->name);
        $stmt->bindParam(':email', $data->email);
        $stmt->bindParam(':pass', $hashed_password);
        $stmt->bindParam(':cpf', $cpf);
        $stmt->bindParam(':birth_date', $birth_date);
        $stmt->bindParam(':role', $role);

        $stmt->execute();
        
        http_response_code(201);
        echo json_encode(["message" => "User created successfully", "id" => $conn->lastInsertId()]);

    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { 
            http_response_code(409); 
            echo json_encode(["message" => "email or CPF already in use"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "error creating user: " . $e->getMessage()]);
        }
    }
}

function handlePutUser($conn, $id) {
    $data = json_decode(file_get_contents("php://input"));

    if (!$id) {
        http_response_code(400);
        echo json_encode(["message" => "id required"]);
        return;
    }
    
    $set_clauses = "fullName = :name, email = :email, cpf = :cpf, birth_date = :birth_date, role = :role";
    $params = [
        ':id' => $id,
        ':name' => $data->name ?? null,
        ':email' => $data->email ?? null,
        ':cpf' => $data->cpf ?? null,
        ':birth_date' => $data->birth_date ?? null,
        ':role' => $data->role ?? 'user'
    ];

    if (isset($data->password) && !empty($data->password)) {
        $hashed_password = password_hash($data->password, PASSWORD_DEFAULT);
        $set_clauses .= ", pass = :pass";
        $params[':pass'] = $hashed_password;
    }

    if (!isset($data->name) || !isset($data->email)) {
        http_response_code(400);
        echo json_encode(["message" => "name and email are required to update"]);
        return;
    }

    try {
        $sql = "UPDATE users SET {$set_clauses} WHERE id = :id";
        $stmt = $conn->prepare($sql);
        
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["message" => "user created successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "incorrect datas"]);
        }

    } catch (PDOException $e) {
        if ($e->getCode() == '23000') { 
            http_response_code(409);
            echo json_encode(["message" => "email or CPF already in use"]);
        } else {
            http_response_code(500);
            echo json_encode(["message" => "error updating user: " . $e->getMessage()]);
        }
    }
}

function handleDeleteUser($conn, $id) {
    if (!$id) {
        http_response_code(400);
        echo json_encode(["message" => "id required to delete user"]);
        return;
    }

    try {
        $sql = "DELETE FROM users WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $id);
        $stmt->execute();

        if ($stmt->rowCount() > 0) {
            http_response_code(200);
            echo json_encode(["message" => "User deleted successfully"]);
        } else {
            http_response_code(404);
            echo json_encode(["message" => "User not fouynd"]);
        }

    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error deleting user: " . $e->getMessage()]);
    }
}


if (isset($_GET['action']) && $_GET['action'] === 'login') {
    handleLogin($conn);
    exit();
}

$request_uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$path_parts = array_filter(explode('/', $request_uri));

$api_index = array_search('api', $path_parts);
if ($api_index === false) {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found."]);
    exit();
}

$route = array_slice($path_parts, $api_index + 1);
$endpoint = isset($route[0]) ? $route[0] : '';
$id = isset($route[1]) ? $route[1] : null;


if ($endpoint === 'users') {
    switch ($_SERVER['REQUEST_METHOD']) {
        case 'GET':
            if (!$id) {
                try {
                    $stmt = $conn->query("SELECT id, fullName AS name, email, cpf, birth_date, role FROM users");
                    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    echo json_encode($users);
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(["message" => "Error getting all users" . $e->getMessage()]);
                }
            } 
            else {
                try {
                    $stmt = $conn->prepare("SELECT id, fullName AS name, email, cpf, birth_date, role FROM users WHERE id = :id");
                    $stmt->bindParam(':id', $id);
                    $stmt->execute();
                    $user = $stmt->fetch(PDO::FETCH_ASSOC);

                    if ($user) {
                        echo json_encode($user);
                    } else {
                        http_response_code(404);
                        echo json_encode(["message" => "User not found."]);
                    }
                } catch (Exception $e) {
                    http_response_code(500);
                    echo json_encode(["message" => "Error get user: " . $e->getMessage()]);
                }
            }
            break;

        case 'POST':
            handlePostUser($conn);
            break;

        case 'PUT':
            handlePutUser($conn, $id);
            break;

        case 'DELETE':
            handleDeleteUser($conn, $id);
            break;

        default:
            http_response_code(405);
            echo json_encode(["message" => "HTTP method not allowed"]);
            break;
    }

} 
else if (empty($endpoint)) {
    http_response_code(200);
    echo json_encode(["message" => "API OK", "method" => $_SERVER['REQUEST_METHOD']]);
} else {
    http_response_code(404);
    echo json_encode(["message" => "Endpoint not found."]);
}
?>