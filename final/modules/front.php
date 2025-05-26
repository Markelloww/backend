<?php

global $db;
require_once './scripts/db.php';

function front_get($request)
{
	error_log('Cookies: ' . print_r($_COOKIE, true));

	$messages = array();
	$values = array();

	// Когда успешно сохранили форму (потом сделать, чтобы 1 раз выводилось и пропадал пароль)
	if (!empty($_COOKIE['save'])) {
		if (!empty($_COOKIE['login']) && !empty($_COOKIE['pass'])) {
			$messages[] = 'Спасибо, результаты сохранены.';
			$messages[] = sprintf('Вы можете <a href="login.php">войти</a> с логином <strong>%s</strong>
                и паролем <strong>%s</strong> для изменения данных.',
				htmlspecialchars(strip_tags($_COOKIE['login']), ENT_QUOTES, 'UTF-8'),
				htmlspecialchars(strip_tags($_COOKIE['pass']), ENT_QUOTES, 'UTF-8')
			);
		}
		// setcookie('save', '', time() - 3600, '/');
		// setcookie('login', '', time() - 3600, '/');
		// setcookie('pass', '', time() - 3600, '/');
	}

	if (!empty($_COOKIE['login'])) {
		$stmt = db_query(
			"SELECT a.* FROM final_applications a 
             JOIN final_users u ON a.id = u.application_id 
             WHERE u.username = ?",
			$_COOKIE['login']
		);

		if ($stmt && !empty($stmt)) {
			$application = reset($stmt); 
			$values = [
				'name' => htmlspecialchars($application['name']),
				'phone' => htmlspecialchars($application['phone']),
				'email' => htmlspecialchars($application['email']),
				'message' => htmlspecialchars($application['comment']),
			];
		}
	}

	$errors = json_decode($_COOKIE['errors'] ?? '', true) ?: [];

	$data = [
		'messages' => $messages,
		'values' => $values,
		'errors' => $errors
	];

	return theme('home', $data);
}

function front_post($request)
{
	$errors = [];
	$fields = [
		'name' => trim($_POST['name'] ?? ''),
		'phone' => trim($_POST['phone'] ?? ''),
		'email' => trim($_POST['email'] ?? ''),
		'message' => trim($_POST['message'] ?? ''),
	];

	if (empty($fields['name']))
		$errors['name'] = 'Поле "Имя" обязательно';
	if (empty($fields['phone']))
		$errors['phone'] = 'Поле "Телефон" обязательно';
	if (empty($fields['email']) || !filter_var($fields['email'], FILTER_VALIDATE_EMAIL)) {
		$errors['email'] = 'Укажите корректный email';
	}
	if (empty($_POST['terms']))
		$errors['terms'] = 'Необходимо согласие';

	if (!empty($errors)) {
		setcookie('errors', json_encode($errors), time() + 3600, '/');
		return redirect();
	}

	try {
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

		return redirect();
	} catch (Exception $e) {
		$errors['db'] = 'Ошибка при сохранении данных';
		setcookie('errors', json_encode($errors), time() + 3600, '/');
		return redirect();
	}
}