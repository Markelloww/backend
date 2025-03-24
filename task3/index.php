<?php
header('Content-Type: text/html; charset=UTF-8');

$db_user = 'u68594';
$db_pass = '2729694';
$db_name = 'u68594';

if ($_SERVER['REQUEST_METHOD'] == 'GET') {
    if (!empty($_GET['save'])) {
        echo '<div class="success">Данные успешно сохранены!</div>';
    }
    include('form.html');
    exit();
}
$errors = [];
if (empty($_POST['name']) || !preg_match('/^[а-яА-ЯёЁa-zA-Z\s]+$/u', $_POST['name'])) {
    $errors[] = 'Укажите корректное ФИО';
}
if (empty($_POST['phone']) || !preg_match('/^(\+7|8)\d{10}$/', $_POST['phone'])) {
    $errors[] = 'Укажите корректно телефон';
}
if (empty($_POST['email']) || !filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'Укажите корректный email';
}
if (empty($_POST['birthday'])) {
    $errors[] = 'Укажите дату рождения';
}
if (empty($_POST['gender']) || !in_array($_POST['gender'], ['Мужской', 'Женский'])) {
    $errors[] = 'Укажите пол';
}
if (empty($_POST['language'])) {
    $errors[] = 'Выберите хотя бы один язык программирования';
}
if (empty($_POST['contract'])) {
    $errors[] = 'Необходимо подтвердить ознакомление с контрактом';
}
if (!empty($errors)) {
    echo '<div class="errors">';
    foreach ($errors as $error) {
        echo htmlspecialchars($error) . '<br>';
    }
    echo '</div>';
    include('form.html');
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
    header('Location: ?save=1');
    exit();
} catch (PDOException $e) {
    $db->rollBack();
    die("Ошибка базы данных: " . $e->getMessage());
}
?>