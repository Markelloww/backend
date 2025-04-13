<?php
header('Content-Type: text/html; charset=UTF-8');

$db_user = 'u68594';
$db_pass = '2729694';
$db_name = 'u68594';

$fields = [
    'name' => '',
    'phone' => '',
    'email' => '',
    'birthday' => '',
    'gender' => 'Мужской',
    'language' => [],
    'biography' => '',
    'contract' => false
];

$errors = [];

if (isset($_COOKIE['form_errors'])) {
    $errors = json_decode($_COOKIE['form_errors'], true);
    setcookie('form_errors', '', time() - 3600, '/');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (empty($_POST['name'])) {
		$errors['name'] = 'Поле ФИО обязательно для заполнения';
	} elseif (!preg_match('/^[а-яА-ЯёЁa-zA-Z\s]+$/u', $_POST['name'])) {
		$errors['name'] = 'ФИО может содержать только буквы и пробелы';
	}
	
	if (empty($_POST['phone'])) {
		$errors['phone'] = 'Поле Телефон обязательно для заполнения';
	} elseif (!preg_match('/^(\+7|8)\d{10}$/', $_POST['phone'])) {
		$errors['phone'] = 'Телефон должен быть в формате +7XXXXXXXXXX или 8XXXXXXXXXX';
	}
	
	if (empty($_POST['email'])) {
		$errors['email'] = 'Поле Email обязательно для заполнения';
	} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$errors['email'] = 'Введите корректный email адрес';
	}
	
	if (empty($_POST['birthday'])) {
		$errors['birthday'] = 'Поле Дата рождения обязательно для заполнения';
	}
	
	if (empty($_POST['gender']) || !in_array($_POST['gender'], ['Мужской', 'Женский'])) {
		$errors['gender'] = 'Укажите пол';
	}
	
	if (empty($_POST['language'])) {
		$errors['language'] = 'Выберите хотя бы один язык программирования';
	} else {
		$validLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
		foreach ($_POST['language'] as $lang) {
			if (!in_array($lang, $validLanguages)) {
				$errors['language'] = 'Указан недопустимый язык программирования';
				break;
			}
		}
	}
	
	if (empty($_POST['contract'])) {
		$errors['contract'] = 'Необходимо подтвердить ознакомление с контрактом';
	}

    if (!empty($errors)) {
        setcookie('form_errors', json_encode($errors), 0, '/');
        setcookie('form_data', json_encode($_POST), 0, '/');
        header('Location: index.php');
        exit();
    }

    $form_data = [
        'name' => $_POST['name'],
        'phone' => $_POST['phone'],
        'email' => $_POST['email'],
        'birthday' => $_POST['birthday'],
        'gender' => $_POST['gender'],
        'language' => $_POST['language'],
        'biography' => $_POST['biography'] ?? '',
        'contract' => true
    ];
    setcookie('form_data', json_encode($form_data), time() + 60 * 60 * 24 * 365, '/');

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
        die("Ошибка базы данных: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit();
}