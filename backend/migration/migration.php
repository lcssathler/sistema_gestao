<?php
require_once __DIR__ . '/../database/database.php';

if (file_exists(__DIR__ . '/.migrated')) {
    exit("Migration already executed.\n");
}

$db = db();

$db->query("
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(120) UNIQUE,
    password VARCHAR(255),
    full_name VARCHAR(120) DEFAULT NULL,
    birth_date DATE DEFAULT NULL,
    role ENUM('admin','user') DEFAULT 'user',
    is_profile_complete TINYINT(1) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
");

$adminEmail = "admin@system.com";

$exists = $db->get("users", "id", ["email" => $adminEmail]);

if (!$exists) {
    $db->insert("users", [
        "email" => $adminEmail,
        "password" => password_hash("Admin123!", PASSWORD_DEFAULT),
        "role" => "admin",
        "is_profile_complete" => 1
    ]);
}

file_put_contents(__DIR__ . "/.migrated", "done");

echo "Migration complete.\n";
