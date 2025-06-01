<?php

define('NAME_PATTERN', '/^[а-яА-ЯёЁa-zA-Z\s]+$/u');
define('PHONE_PATTERN', '/^(\+7|8)\d{10}$/');
define('DATE_PATTERN', '/^\d{4}-\d{2}-\d{2}$/');
define('BIO_PATTERN', '/^[а-яА-ЯёЁa-zA-Z0-9\s.,!?-]+$/u');

global $db;
require_once './scripts/db.php';

function front_get($request, $db)
{
	error_log('Cookies: ' . print_r($_COOKIE, true));

	$csrfToken = generateCSRFToken();
	$messages = array();
	$values = array();

	if (!empty($_COOKIE['save'])) {
		if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
			$messages[] = 'Спасибо, результаты сохранены.';
			$messages[] = sprintf('Вы можете <a href="%s">войти</a> с логином <strong>%s</strong>
                и паролем <strong>%s</strong> для изменения данных в любой момент.',
				htmlspecialchars(url('?q=login'), ENT_QUOTES, 'UTF-8'),
				htmlspecialchars(strip_tags(string: $_COOKIE['login']), ENT_QUOTES, 'UTF-8'),
				htmlspecialchars(strip_tags($_COOKIE['pass']), ENT_QUOTES, 'UTF-8')
			);
		}
	}

	if (!empty($_COOKIE['savedData'])) {
		$savedData = json_decode($_COOKIE['savedData'], true);
		if (is_array($savedData)) {
			$values = [
				'name' => htmlspecialchars($savedData['name'] ?? ''),
				'phone' => htmlspecialchars($savedData['phone'] ?? ''),
				'email' => htmlspecialchars($savedData['email'] ?? ''),
				'message' => htmlspecialchars($savedData['message'] ?? '')
			];
		}
	}

	$errors = json_decode($_COOKIE['errors'] ?? '', true) ?: [];

	$data = [
		'messages' => $messages,
		'values' => $values,
		'errors' => $errors,
		'csrf_token' => $csrfToken
	];

	return theme('home', $data);
}

function front_post($request, $db)
{
	if (!validateCSRFToken()) {
		return access_denied();
	}

	$errors = [];
	$fields = [
		'name' => trim($_POST['name'] ?? ''),
		'phone' => trim($_POST['phone'] ?? ''),
		'email' => trim($_POST['email'] ?? ''),
		'message' => trim($_POST['message'] ?? ''),
	];

	if (empty($_POST['name'])) {
		$errors['name'] = 'Укажите ФИО!';
	} elseif (!preg_match(NAME_PATTERN, $_POST['name'])) {
		$errors['name'] = 'ФИО может содержать только буквы и пробелы';
	}

	if (empty($_POST['phone'])) {
		$errors['phone'] = 'Укажите номер телефона!';
	} elseif (!preg_match(PHONE_PATTERN, $_POST['phone'])) {
		$errors['phone'] = 'Телефон должен быть в формате +7XXXXXXXXXX или 8XXXXXXXXXX (11 цифр)';
	}

	if (empty($_POST['email'])) {
		$errors['email'] = 'Укажите e-mail!';
	} elseif (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
		$errors['email'] = 'E-mail должен быть в формате you@example.com';
	}

	if (empty($_POST['terms'])) {
		$errors['terms'] = 'Необходимо согласие';
	}

	if (!empty($errors)) {
		setcookie('errors', json_encode($errors), time() + 3600, '/');
		return redirect();
	}

	try {
		if (!empty($_SESSION['login'])) {
			$user = db_query(
				"SELECT * FROM final_users WHERE username = ?",
				$_SESSION['login']
			);
			if ($user) {
				$user = reset($user);
				db_command(
					"UPDATE final_applications SET name = ?, phone = ?, email = ?, comment = ? WHERE id = ?",
					$fields['name'],
					$fields['phone'],
					$fields['email'],
					$fields['message'],
					$user['application_id']
				);
			}
		} else {
			db_command(
				"INSERT INTO final_applications (name, phone, email, comment) VALUES (?, ?, ?, ?)",
				$fields['name'],
				$fields['phone'],
				$fields['email'],
				$fields['message']
			);
			$application_id = db_insert_id();

			$login = uniqid('user_');
			$password = substr(md5(uniqid()), 0, 8);
			$password_hash = password_hash($password, PASSWORD_DEFAULT);

			db_command(
				"INSERT INTO final_users (application_id, username, password_hash) VALUES (?, ?, ?)",
				$application_id,
				$login,
				$password_hash
			);

			setcookie('save', '1', time() + 3600, '/');
			setcookie('login', $login, time() + 60 * 60 * 24 * 30, '/');
			setcookie('pass', $password, time() + 60 * 60 * 24 * 30, '/');
		}

		$savedData = [
			'name' => $fields['name'],
			'phone' => $fields['phone'],
			'email' => $fields['email'],
			'message' => $fields['message']
		];
		setcookie('savedData', json_encode($savedData), time() + 60 * 60 * 24 * 30, '/');

		return redirect();
	} catch (Exception $e) {
		$errors['db'] = 'Ошибка при сохранении данных';
		setcookie('errors', json_encode($errors), time() + 3600, '/');
		return redirect();
	}
}