<?php
header('Content-Type: text/html; charset=UTF-8');

define('NAME_PATTERN', '/^[а-яА-ЯёЁa-zA-Z\s]+$/u');
define('PHONE_PATTERN', '/^(\+7|8)\d{10}$/');
define('DATE_PATTERN', '/^\d{4}-\d{2}-\d{2}$/');
define('BIO_PATTERN', '/^[а-яА-ЯёЁa-zA-Z0-9\s.,!?-]+$/u');

$languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP','Python', 'Java', 'Haskel', 'Clojure','Prolog', 'Scala', 'Go'];

$db_user = 'u68594';
$db_pass = '2729694';
$db_name = 'u68594';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    $errors = json_decode($_COOKIE['errors'] ?? '', true) ?: [];
    $formData = json_decode($_COOKIE['formData'] ?? '', true) ?: [];
    if (!empty($_GET['save'])) {
        echo '<div class="success">Данные успешно сохранены!</div>';
        setcookie('savedData', json_encode($formData), time() + 60*60*24*365, path: '/');
    }
    setcookie('errors', '', time() - 3600, '/');
    include('form.php');
    exit();
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
    setcookie('errors', json_encode($errors), 0, '/');
    setcookie('formData', json_encode($formData), 0, '/');
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
    $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id)
                          SELECT :app_id, id FROM programming_languages WHERE name = :lang_name");
    foreach ($_POST['language'] as $lang) {
        $stmt->execute([':app_id' => $appId, ':lang_name' => $lang]);
    }
    $db->commit();
    
	setcookie('savedData', json_encode($formData), time() + 60*60*24*365, '/');
    setcookie('formData', '', time() - 3600, '/');
    header('Location: ?save=1');
    exit();
} catch (PDOException $e) {
    $db->rollBack();
    die("Ошибка базы данных: " . $e->getMessage());
}
?>