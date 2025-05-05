<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

try {
    $db = new PDO("mysql:host=localhost;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
    ]);

    $checkAdmin = $db->prepare("SELECT * FROM admin_users WHERE login = 'admin'");
    $checkAdmin->execute();
    $existingAdmin = $checkAdmin->fetch();

    if (!$existingAdmin) {
        $hashedPassword = password_hash('admin', PASSWORD_DEFAULT);
        $createAdmin = $db->prepare("INSERT INTO admin_users (login, pass_hash) VALUES ('admin', :pass_hash)");
        $createAdmin->execute([':pass_hash' => $hashedPassword]);
        echo '<p>Пользователь admin создан с паролем admin</p>';
    } else {
        echo '<p>Пользователь admin уже существует</p>';
    }
} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}
?>