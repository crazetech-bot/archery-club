<?php
try {
    $pdo = new PDO(
        "mysql:host=217.216.35.201;port=3306;dbname=fmsport_archery",
        "fmsport_archer12",
        'z&Z5!\qj9E>[.gx&'
    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "DB connected OK" . PHP_EOL;

    $stmt = $pdo->query("SELECT id, email, password, is_super_admin FROM users WHERE email='admin@fmsport.biz'");
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if ($user) {
        echo "USER FOUND: id=" . $user['id'] . " super_admin=" . $user['is_super_admin'] . PHP_EOL;
        echo "PASSWORD_OK=" . (password_verify('Admin@2026!', $user['password']) ? 'YES' : 'NO') . PHP_EOL;
        echo "Hash in DB: " . $user['password'] . PHP_EOL;
    } else {
        echo "USER NOT FOUND in users table" . PHP_EOL;
        $count = $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
        echo "Total users in table: $count" . PHP_EOL;
    }
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
}
