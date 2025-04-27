<?php
header('Content-Type: text/html; charset=UTF-8');
session_start();

$db_user = 'u68594';
$db_pass = '2729694';
$db_name = 'u68594';

$messages = array();

if (!empty($_SESSION['login'])) {
    header('Location: index.php');
    exit();
}

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Вход</title>
        <link href="./css/style.css" rel="stylesheet">
    </head>
    <body>
        <div class="content">
            <h1>Вход</h1>
            <?php if (!empty($messages)): ?>
                <div class="errors">
                    <?php foreach ($messages as $message): ?>
                        <div class="error"><?= htmlspecialchars($message) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="login.php">
                <label for="login">Логин:</label>
                <input type="text" name="login" id="login" required>
                
                <label for="pass">Пароль:</label>
                <input type="password" name="pass" id="pass" required>
                
                <button type="submit">Войти</button>
            </form>
        </div>
    </body>
    </html>
    <?php
} else {
    $login = $_POST['login'] ?? '';
    $pass = $_POST['pass'] ?? '';
    
    if (empty($login) || empty($pass)) {
        $messages[] = 'Заполните логин и пароль';
        include('login.php');
        exit();
    }
    
    try {
        $db = new PDO("mysql:host=localhost;dbname=$db_name", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
        ]);
        
        $stmt = $db->prepare("SELECT u.*, a.id as app_id FROM users u 
                             JOIN applications a ON u.application_id = a.id 
                             WHERE u.login = :login");
        $stmt->execute([':login' => $login]);
        $user = $stmt->fetch();
        
        if ($user && password_verify($pass, $user['pass_hash'])) {
            $_SESSION['login'] = $user['login'];
            $_SESSION['uid'] = $user['app_id'];
            
            header('Location: index.php');
            exit();
        } else {
            $messages[] = 'Неверный логин или пароль';
            include('login.php');
            exit();
        }
    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
}
?>