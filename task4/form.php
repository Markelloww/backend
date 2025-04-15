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
            <input type="text" name="name" placeholder="Введите ваши ФИО" required type="text" value="<?= htmlspecialchars($data['name'] ?? '') ?>" class="<?= isset($errors['name']) ? 'error-field' : '' ?>">
            <br>

            <label for="phone">Телефон:</label>
            <input type="text" name="phone" placeholder="88005553535" required type="tel" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" class="<?= isset($errors['phone']) ? 'error-field' : '' ?>">
            <br>

            <label for="email">E-mail:</label>
            <input type="text" name="email" placeholder="you@example.com" required type="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
            <br>

            <label for="birthday">Дата рождения:</label>
            <input type="date" name="birthday" required type="date" value="<?= htmlspecialchars($data['birthday'] ?? '') ?>" class="<?= isset($errors['birthday']) ? 'error-field' : '' ?>">
            <br>

            <label>Пол:</label>
            <div>
                <div class="<?= isset($errors['gender']) ? 'error-field' : '' ?>">
                    <input type="radio" name="gender" value="Мужской" id="gender_male" <?= ($data['gender'] ?? '') == 'Мужской' ? 'checked' : '' ?>>
                    <label for="gender_male">Мужской</label>
                    <input type="radio" name="gender" value="Женский" id="gender_female" <?= ($data['gender'] ?? '') == 'Женский' ? 'checked' : '' ?>>
                    <label for="gender_female">Женский</label>
                </div>
            </div>
            <br>

            <label for="language">Любимые языки программирования:</label>
            <select id="language" name="language[]" multiple class="<?= isset($errors['language']) ? 'error-field' : '' ?>">
                <?php
                $languages = [
                    'Pascal', 'C', 'C++', 'JavaScript', 'PHP',
                    'Python', 'Java', 'Haskel', 'Clojure',
                    'Prolog', 'Scala', 'Go'
                ];
                $selectedLanguages = $data['language'] ?? [];
                foreach ($languages as $lang): 
                    $isSelected = in_array($lang, $selectedLanguages);
                ?>
                <option value="<?= htmlspecialchars($lang) ?>" <?= $isSelected ? 'selected' : '' ?>><?= htmlspecialchars($lang) ?></option>
                <?php endforeach; ?>
            </select>
            <br>

            <label for="biography">Биография:</label>
            <textarea name="biography" id="biography" placeholder="Расскажите о себе" class="<?= isset($errors['biography']) ? 'error-field' : '' ?>"><?=
                htmlspecialchars($data['biography'] ?? '') ?></textarea>
            <br>

            <label>
            <input type="checkbox" name="contract" value="on" <?= isset($data['contract']) ? 'checked' : '' ?>>
            С контрактом ознакомлен
            </label>
            <br>

            <button type="submit">Сохранить</button>
			
        </form>
    </div>
</body>
</html>