<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') { http_response_code(200); exit(); }

$host = 'localhost';
$db_name = 'people_db';
$username = 'root';
$password = '';

try {
    $conn = new PDO("mysql:host=$host", $username, $password);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $conn->exec("CREATE DATABASE IF NOT EXISTS $db_name");
    $conn->exec("USE $db_name");
    
    $conn->exec("CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullName VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        pass VARCHAR(255) NOT NULL,
        cpf VARCHAR(14),
        birth_date DATE, 
        role ENUM('admin', 'user') DEFAULT 'user'
    )");

    $check = $conn->query("SELECT count(*) FROM users");
    if ($check->fetchColumn() == 0) {
        $pass = password_hash('123456', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullName, email, pass, role, birth_date) VALUES ('Admin', 'admin@test.com', '$pass', 'admin', '1990-01-01')";
        $conn->exec($sql);
    }
} catch(PDOException $e) {
    echo json_encode(["message" => "Connection error: " . $e->getMessage()]);
    exit;
}
?>