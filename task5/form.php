<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форма регистрации</title>
    <link href="./css/style.css" rel="stylesheet">
    <meta name="viewport" content="width=device-width, initial-scale=1">
</head>
<body>
    <?php
    $savedData = json_decode($_COOKIE['savedData'] ?? '', true) ?: [];
    $formData = json_decode($_COOKIE['formData'] ?? '', true) ?: [];
    $errors = json_decode($_COOKIE['errors'] ?? '', true) ?: [];
    $data = array_merge($savedData, $formData);
    $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
    $selectedLanguage = $data['language'] ?? [];
    ?>

    <?php if (!empty($errors)): ?>
        <div class="errors">
            <p>Пожалуйста, исправьте следующие ошибки:</p>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li class="error"><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <div class="content">
        <form id="form" method="POST" action="index.php">
            <label for="name">ФИО:</label>
            <input type="text" name="name" placeholder="Введите ваши ФИО" value="<?= htmlspecialchars($data['name'] ?? '') ?>" class="<?= isset($errors['name']) ? 'error-field' : '' ?>">
            <br>

            <label for="phone">Телефон:</label>
            <input type="text" name="phone" placeholder="88005553535" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" class="<?= isset($errors['phone']) ? 'error-field' : '' ?>">
            <br>

            <label for="email">E-mail:</label>
            <input type="text" name="email" placeholder="you@example.com" value="<?= htmlspecialchars($data['email'] ?? '') ?>" class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
            <br>

            <label for="birthday">Дата рождения:</label>
            <input type="date" name="birthday" value="<?= htmlspecialchars($data['birthday'] ?? '') ?>" class="<?= isset($errors['birthday']) ? 'error-field' : '' ?>">
            <br>

            <label>Пол:</label>
            <div>
                <div class="<?= isset($errors['gender']) ? 'error-field' : '' ?>">
                    <label>
                        <input type="radio" name="gender" value="Мужской" <?= ($data['gender'] ?? '') == 'Мужской' ? 'checked' : '' ?>>
                        Мужской
                    </label>
                    <label>
                        <input type="radio" name="gender" value="Женский" <?= ($data['gender'] ?? '') == 'Женский' ? 'checked' : '' ?>>
                        Женский
                    </label>
                </div>
            </div>
            <br>

            <label for="language">Любимые языки программирования:</label>
            <select id="language" name="language[]" multiple class="<?= isset($errors['language']) ? 'error-field' : '' ?>">
                <?php foreach ($languages as $lang): ?>
                    <option value="<?= htmlspecialchars($lang) ?>"
                        <?= in_array($lang, $selectedLanguage) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($lang) ?>
                    </option>
                <?php endforeach; ?>
            </select>

            <label for="biography">Биография:</label>
            <textarea name="biography" id="biography" placeholder="Расскажите о себе" class="<?= isset($errors['biography']) ? 'error-field' : '' ?>"><?= htmlspecialchars($data['biography'] ?? '') ?></textarea>
            <br>

            <div class="<?= isset($errors['contract']) ? 'error-field' : '' ?>">
                <label>
                    <input type="checkbox" name="contract" value="on" <?= ($data['contract'] ?? null) === 'on' ? 'checked' : '' ?>>
                    С контрактом ознакомлен
                </label>
            </div>
            <br>
            <button type="submit">Сохранить</button>
        </form>
		<?php if (!empty($_SESSION['login'])): ?>
            <div class="logout">
                <form action="logout.php" method="post">
                    <button type="submit">Выйти (<?= htmlspecialchars($_SESSION['login']) ?>)</button>
                </form>
            </div>
        <?php endif; ?>
		<?php if (!empty($messages)): ?>
        	<div class="messages">
				<?php foreach ($messages as $message): ?>
					<div><?= $message ?></div>
				<?php endforeach; ?>
			</div>
    	<?php endif; ?>
    </div>
</body>
</html>