<!DOCTYPE html>
<html lang="ru">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Панель администратора</title>
	<link rel="stylesheet" href="./theme/css/admin.css">
</head>

<body>
	<h1>Админ-панель</h1>

	<?php if (isset($editData)): ?>
		<div class="edit-form">
			<h2>Редактирование пользователя</h2>
			<form method="POST">
				<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
				<input type="hidden" name="id" value="<?= htmlspecialchars($editData['id'], ENT_QUOTES, 'UTF-8') ?>">

				<label>ФИО: <input type="text" name="name"
						value="<?= htmlspecialchars($editData['name'], ENT_QUOTES, 'UTF-8') ?>" required></label><br>
				<label>Телефон: <input type="text" name="phone"
						value="<?= htmlspecialchars($editData['phone'], ENT_QUOTES, 'UTF-8') ?>" required></label><br>
				<label>Email: <input type="email" name="email"
						value="<?= htmlspecialchars($editData['email'], ENT_QUOTES, 'UTF-8') ?>" required></label><br>
				<label>Комментарий:<br><textarea
						name="biography"><?= htmlspecialchars($editData['comment'], ENT_QUOTES, 'UTF-8') ?></textarea></label><br>

				<button type="submit" name="update">Сохранить</button>
				<a href="admin.php">Отмена</a>
			</form>
		</div>
	<?php endif; ?>

	<h2>Все заявки</h2>
	<table>
		<thead>
			<tr>
				<th>ID</th>
				<th>ФИО</th>
				<th>Телефон</th>
				<th>Email</th>
				<th>Комментарий</th>
				<th>Действия</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($applications as $app): ?>
				<tr>
					<td><?= htmlspecialchars($app['id'], ENT_QUOTES, 'UTF-8') ?></td>
					<td><?= htmlspecialchars($app['name'], ENT_QUOTES, 'UTF-8') ?></td>
					<td><?= htmlspecialchars($app['phone'], ENT_QUOTES, 'UTF-8') ?></td>
					<td><?= htmlspecialchars($app['email'], ENT_QUOTES, 'UTF-8') ?></td>
					<td><?= htmlspecialchars($app['comment'], ENT_QUOTES, 'UTF-8') ?></td>
					<td>
						<form method="POST" style="display: inline;">
							<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
							<input type="hidden" name="edit"
								value="<?= htmlspecialchars($app['id'], ENT_QUOTES, 'UTF-8') ?>">
							<button type="submit">Редактировать</button>
						</form>
						<form method="POST" style="display: inline;" onsubmit="return confirm('Удалить эту запись?');">
							<input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token, ENT_QUOTES, 'UTF-8') ?>">
							<input type="hidden" name="delete"
								value="<?= htmlspecialchars($app['id'], ENT_QUOTES, 'UTF-8') ?>">
							<button type="submit">Удалить</button>
						</form>
					</td>
				</tr>
			<?php endforeach; ?>
		</tbody>
	</table>
</body>

</html>