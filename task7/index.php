<?php
header('Content-Type: text/html; charset=UTF-8');
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; script-src 'self'");

session_start();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

define('NAME_PATTERN', '/^[а-яА-ЯёЁa-zA-Z\s]+$/u');
define('PHONE_PATTERN', '/^(\+7|8)\d{10}$/');
define('DATE_PATTERN', '/^\d{4}-\d{2}-\d{2}$/');
define('BIO_PATTERN', '/^[а-яА-ЯёЁa-zA-Z0-9\s.,!?-]+$/u');

$languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP','Python', 'Java', 'Haskel', 'Clojure','Prolog', 'Scala', 'Go'];

$db_user = 'u68594';
$db_pass = '2729694';
$db_name = 'u68594';

$messages = array();

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_COOKIE['save'])) {
        if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
			$messages[] = 'Спасибо, результаты сохранены.';
            $messages[] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
                и паролем <strong>%s</strong> для изменения данных.',
                htmlspecialchars(strip_tags($_COOKIE['login']), ENT_QUOTES, 'UTF-8'),
                htmlspecialchars(strip_tags($_COOKIE['pass']), ENT_QUOTES, 'UTF-8'));
        }
        setcookie('save', '', time() - 3600, '/');
		setcookie('login', '', time() - 3600, '/');
		setcookie('pass', '', time() - 3600, '/');
    }
    
    $errors = json_decode($_COOKIE['errors'] ?? '', true) ?: [];
    $forma = [];
    
    if (!empty($_SESSION['login'])) {
        try {
            $db = new PDO("mysql:host=localhost;dbname=$db_name", $db_user, $db_pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
            ]);
            
            $stmt = $db->prepare("SELECT a.*, GROUP_CONCAT(pl.name) as languages 
                                 FROM applications a 
                                 JOIN application_languages al ON a.id = al.application_id 
                                 JOIN programming_languages pl ON al.language_id = pl.id 
                                 WHERE a.id = :id 
                                 GROUP BY a.id");
            $stmt->execute([':id' => $_SESSION['uid']]);
            $userData = $stmt->fetch();
            
            if ($userData) {
                $forma = [
                    'name' => htmlspecialchars($userData['name'], ENT_QUOTES, 'UTF-8'),
                    'phone' => htmlspecialchars($userData['phone'], ENT_QUOTES, 'UTF-8'),
                    'email' => htmlspecialchars($userData['email'], ENT_QUOTES, 'UTF-8'),
                    'birthday' => htmlspecialchars($userData['birthday'], ENT_QUOTES, 'UTF-8'),
                    'gender' => htmlspecialchars($userData['gender'], ENT_QUOTES, 'UTF-8'),
                    'biography' => htmlspecialchars($userData['biography'], ENT_QUOTES, 'UTF-8'),
                    'language' => explode(',', $userData['languages']),
                    'contract' => 'on'
                ];
            }
        } catch (PDOException $e) {
            $messages[] = 'Ошибка загрузки данных: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8');
        }
    } elseif (isset($_COOKIE['savedData'])) {
        $forma = json_decode($_COOKIE['savedData'], true);
    } elseif (isset($_COOKIE['formData'])) {
        $forma = json_decode($_COOKIE['formData'], true);
    }

    include('form.php');
    exit();
}

if (empty($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die('Ошибка безопасности: недействительный CSRF-токен');
}

$fields = ['name', 'phone', 'email', 'birthday', 'gender', 'biography', 'language', 'contract'];
$formData = array_intersect_key($_POST, array_flip($fields));

$errors = [];
if (empty($_POST['name'])) {
    $errors['name'] = 'Укажите ФИО';
} elseif (!preg_match(NAME_PATTERN, $_POST['name'])) {
    $errors['name'] = 'ФИО может содержать только буквы и пробелы';
}

if (empty($_POST['phone'])) {
    $errors['phone'] = 'Укажите номер телефона';
} elseif (!preg_match(PHONE_PATTERN, $_POST['phone'])) {
    $errors['phone'] = 'Телефон должен быть в формате +7XXXXXXXXXX или 8XXXXXXXXXX (11 цифр)';
}

if (empty($_POST['email'])) {
    $errors['email'] = 'Укажите E-mail';
} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors['email'] = 'E-mail должен быть в формате you@example.com';
}

if (empty($_POST['birthday'])) {
    $errors['birthday'] = 'Укажите дату рождения';
} elseif (!preg_match(DATE_PATTERN, $_POST['birthday'])) {
    $errors['birthday'] = 'Дата должна быть в формате ДД-ММ-ГГГГ';
}

if (empty($_POST['gender'])) {
    $errors['gender'] = 'Укажите пол';
} elseif (!in_array($_POST['gender'], ['Мужской', 'Женский'])) {
    $errors['gender'] = 'Пол должен быть "Мужской" или "Женский"';
}

if (empty($_POST['language'])) {
    $errors['language'] = 'Выберите хотя бы один язык программирования';
} else {
    foreach ($_POST['language'] as $lang) {
        if (!in_array($lang, $languages)) {
            $errors['language'] = 'Выбран некорректный язык';
            break;
        }
    }
}

if (!empty($_POST['biography']) && !preg_match(BIO_PATTERN, $_POST['biography'])) {
    $errors['biography'] = 'Биография содержит недопустимые символы';
}

if (empty($_POST['contract'])) {
    $errors['contract'] = 'Необходимо подтвердить ознакомление с контрактом';
}

if (!empty($errors)) {
    setcookie('savedData', '', time() - 3600, '/');
    setcookie('formData', json_encode($formData), 0, '/');
    setcookie('errors', json_encode($errors), 0, '/');
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

try {
    $db = new PDO("mysql:host=localhost;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
    ]);
    
    $db->beginTransaction();
    
    if (!empty($_SESSION['login'])) {
        $stmt = $db->prepare("UPDATE applications SET 
                             name = :name, phone = :phone, email = :email, 
                             birthday = :birthday, gender = :gender, biography = :biography 
                             WHERE id = :id");
        $stmt->execute([
            ':name' => $_POST['name'],
            ':phone' => $_POST['phone'],
            ':email' => $_POST['email'],
            ':birthday' => $_POST['birthday'],
            ':gender' => $_POST['gender'],
            ':biography' => $_POST['biography'] ?? null,
            ':id' => $_SESSION['uid']
        ]);
        
        $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = :app_id");
        $stmt->execute([':app_id' => $_SESSION['uid']]);
        
        $appId = $_SESSION['uid'];
    } else {
        $stmt = $db->prepare("INSERT INTO applications (name, phone, email, birthday, gender, biography) 
                             VALUES (:name, :phone, :email, :birthday, :gender, :biography)");
        $stmt->execute([
            ':name' => $_POST['name'],
            ':phone' => $_POST['phone'],
            ':email' => $_POST['email'],
            ':birthday' => $_POST['birthday'],
            ':gender' => $_POST['gender'],
            ':biography' => $_POST['biography'] ?? null
        ]);
        $appId = $db->lastInsertId();
        
        $login = uniqid('user_');
        $pass = substr(md5(uniqid()), 0, 8);
        $pass_hash = password_hash($pass, PASSWORD_DEFAULT);
        
        $stmt = $db->prepare("INSERT INTO users (application_id, login, pass_hash) 
                             VALUES (:app_id, :login, :pass_hash)");
        $stmt->execute([
            ':app_id' => $appId,
            ':login' => $login,
            ':pass_hash' => $pass_hash
        ]);
        
        setcookie('login', $login, time() + 60 * 60 * 24 * 30, '/');
        setcookie('pass', $pass, time() + 60 * 60 * 24 * 30, '/');
    }
    
    $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id)
                          SELECT :app_id, id FROM programming_languages WHERE name = :lang_name");
    foreach ($_POST['language'] as $lang) {
        $stmt->execute([':app_id' => $appId, ':lang_name' => $lang]);
    }
    
    $db->commit();
    
    setcookie('formData', '', time() - 3600, '/');
    setcookie('errors', '', time() - 3600, '/');
    setcookie('savedData', json_encode($formData), time() + 60 * 60 * 24 * 365, '/');
    setcookie('save', '1', time() + 60 * 60 * 24, '/');
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
} catch (PDOException $e) {
    $db->rollBack();
    die("Ошибка базы данных: " . $e->getMessage());
}
?>