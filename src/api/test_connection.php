<?php
require_once 'config.php';
require_once 'init_db.php'; 

$pdo = connectDB();

if ($pdo) {
    try {
        $stmt = $pdo->query("SELECT name FROM users LIMIT 1");
        $user_name = $stmt->fetchColumn();
        
        echo "<h1>Conexão com o MySQL do XAMPP OK!</h1>";
        echo "<p>Status: Conectado ao banco de dados <strong>" . DB_NAME . "</strong> com sucesso.</p>";
        echo "<p>Teste de Leitura: Primeiro usuário na tabela é: <strong>" . htmlspecialchars($user_name) . "</strong></p>";
        echo "<p style='color: green; font-weight: bold;'>🎉 Seu backend PHP está se comunicando com o MySQL.</p>";

    } catch (Exception $e) {
        echo "<h1>Conexão OK, mas Query Falhou (Tabela/Dados)</h1>";
        echo "<p>Erro na Query: " . $e->getMessage() . "</p>";
    }
} else {
    echo "<h1>FALHA NA CONEXÃO COM O MySQL</h1>";
    echo "<p style='color: red; font-weight: bold;'>Verifique o Painel de Controle do XAMPP (Apache e MySQL devem estar ON).</p>";
}
?>