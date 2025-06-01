<?php

global $db;
require_once './scripts/db.php';

function login_get($request, $db)
{

	$csrfToken = generateCSRFToken();

	return theme('login', ['csrf_token' => $csrfToken]);
}

function login_post($request, $db)
{
	if (!validateCSRFToken()) {
		return access_denied();
	}

	$errors = [];
	$fields = [
		'login' => trim($_POST['login'] ?? ''),
		'password' => trim($_POST['password'] ?? ''),
	];

	if (empty($fields['login'])) {
		$errors['login'] = 'Поле "Логин" обязательно';
	}
	if (empty($fields['password'])) {
		$errors['password'] = 'Поле "Пароль" обязательно';
	}

	if (!empty($errors)) {
		return theme('login', ['errors' => $errors, 'values' => $fields]);
	}

	if (auth_check($fields['login'], $fields['password'])) {
		$_SESSION['login'] = $fields['login'];
		return redirect('');
	} else {
		$errors[] = 'Введены неверные данные';
		return theme('login', ['errors' => $errors]);
	}
}
