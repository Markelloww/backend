<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<link rel="stylesheet" href="./theme/css/login.css">
	<title>Авторизация</title>
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>

<body>
	<div class="login-container">
		<h1>Вход в систему</h1>

		<form method="POST" action="">
			<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
			<div class="input-group">
				<label for="login">Логин</label>
				<input type="text" id="login" name="login" placeholder="Введите ваш логин"
					value="<?php echo isset($_COOKIE['login']) ? htmlspecialchars($_COOKIE['login']) : ''; ?>" required>
			</div>

			<div class="input-group">
				<label for="password">Пароль</label>
				<input type="password" id="password" name="password" placeholder="Введите ваш пароль"
					value="<?php echo isset($_COOKIE['login']) ? htmlspecialchars($_COOKIE['pass']) : ''; ?>" required>
			</div>

			<button type="submit">Войти</button>
		</form>
		<br>
		<?php if (!empty($errors)): ?>
			<div class="form-messages">
				<?php foreach ($errors as $error): ?>
					<div class="message"><?= $error ?></div>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>
	</div>
</body>

</html>