<?php
require __DIR__ . '/../vendor/autoload.php'; 

use Medoo\Medoo;

function db() {
    static $database = null;
    if ($database === null) {
        $database = new Medoo([
            'type' => 'mysql',
            'host' => '127.0.0.1',
            'database' => 'test_project',
            'username' => 'root',
            'password' => '',
            'charset' => 'utf8mb4'
        ]);
    }
    return $database;
}
