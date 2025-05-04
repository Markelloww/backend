<?php
header('Content-Type: text/html; charset=UTF-8');
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; script-src 'self'");

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$db_user = 'u68594';
$db_pass = '2729694';
$db_name = 'u68594';

$messages = [];

if (isset($_SESSION['login'])) {
	header('Location: ' . htmlspecialchars('index.php', ENT_QUOTES, 'UTF-8'));
    exit();
}

function displayLoginForm($messages) {
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Вход</title>
        <link href="./css/login.css" rel="stylesheet">
    </head>
    <body>
        <div class="content">
            <h1>Вход</h1>
            <form method="POST" action="" id="form">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                <label for="login">Логин:</label>
                <input type="text" name="login" id="login" required>
                <br>
                <label for="pass">Пароль:</label>
                <input type="password" name="pass" id="pass" required>
                <br>
                <button type="submit">Войти</button>
            </form>
			<?php if (!empty($messages)): ?>
                <div class="errors">
                    <?php foreach ($messages as $message): ?>
                        <div class="error"><?= htmlspecialchars($message, ENT_QUOTES, 'UTF-8') ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $login = $_POST['login'] ?? '';
    $pass = $_POST['pass'] ?? '';

    if (empty($login) || empty($pass)) {
        $messages[] = 'Заполните логин и пароль';
        displayLoginForm($messages);
        exit();
    }

	if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
		die('Ошибка безопасности: недействительный CSRF-токен');
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
            displayLoginForm($messages);
            exit();
        }
    } catch (PDOException $e) {
        $messages[] = 'Ошибка подключения к базе данных. Попробуйте позже.';
        displayLoginForm($messages);
        exit();
    }
}

displayLoginForm($messages);
?>