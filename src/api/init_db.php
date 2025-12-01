<?php
require_once 'config.php';

$conn = connectServer();

try {
    $conn->exec("CREATE DATABASE IF NOT EXISTS " . DB_NAME);
    $conn = connectDB();  

    $sql = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        fullName VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        pass VARCHAR(255) NOT NULL,
        cpf VARCHAR(14) UNIQUE,
        birth_date DATE,
        role ENUM('admin', 'user') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    $check = $conn->query("SELECT count(*) FROM users");
    if ($check->fetchColumn() == 0) {
        $pass = password_hash('123456', PASSWORD_DEFAULT);
        $sql = "INSERT INTO users (fullName, email, pass, role, birth_date) 
                VALUES ('Admin Padrão', 'admin@test.com', '$pass', 'admin', '1990-01-01')";
        $conn->exec($sql);
    }
    
    return $conn;

} catch(PDOException $e) {
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode(["message" => "error init db: " . $e->getMessage()]);
    exit;
}
?>