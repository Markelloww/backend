<?php

global $db;
require_once './scripts/db.php';

function admin_get($request, $db)
{
	if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
		if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
			header('WWW-Authenticate: Basic realm="Restricted Area"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'Требуется авторизация';
			exit();
		}

		if (!admin_check($_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'])) {
			header('WWW-Authenticate: Basic realm="Restricted Area"');
			header('HTTP/1.0 401 Unauthorized');
			echo 'Неверные учетные данные.';
			exit();
		}

		$_SESSION['admin_auth'] = true;
		$_SESSION['admin_login'] = $_SERVER['PHP_AUTH_USER'];
	}

	$editData = isset($_SESSION['edit_data']) ? $_SESSION['edit_data'] : null;
	if (isset($_SESSION['edit_data'])) {
		unset($_SESSION['edit_data']);
	}
	$csrfToken = generateCSRFToken();
	$applications = get_all_applications();

	$data = [
		'applications' => $applications,
		'csrf_token' => $csrfToken,
		'editData' => $editData
	];

	return theme('admin', $data);
}

function admin_post($request, $db)
{
	if (!isset($_SESSION['admin_auth']) || !validateCSRFToken()) {
		return access_denied();
	}

	if (isset($_POST['update'])) {
		$id = $_POST['id'];
		$data = [
			'name' => $_POST['name'],
			'phone' => $_POST['phone'],
			'email' => $_POST['email'],
			'comment' => $_POST['biography']
		];
		if (update_application($id, $data)) {
			return redirect('?q=admin');
		}
	} elseif (isset($_POST['delete'])) {
		$id = $_POST['delete'];
		if (delete_application($id)) {
			return redirect('?q=admin');
		}
	} elseif (isset($_POST['edit'])) {
		$id = $_POST['edit'];
		$_SESSION['edit_data'] = get_application_by_id($id);
	}

	return redirect('?q=admin');
}