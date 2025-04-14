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
            <input type="text" name="name" value="<?= htmlspecialchars($data['name'] ?? '') ?>" class="<?= isset($errors['name']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['name'])): ?>
                <span class="error"><?= htmlspecialchars($errors['name']) ?></span>
            <?php endif; ?>
            <br>
            <label for="phone">Телефон:</label>
            <input type="text" name="phone" value="<?= htmlspecialchars($data['phone'] ?? '') ?>" class="<?= isset($errors['phone']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['phone'])): ?>
                <span class="error"><?= htmlspecialchars($errors['phone']) ?></span>
            <?php endif; ?>
            <br>
            <label for="email">E-mail:</label>
            <input type="text" name="email" value="<?= htmlspecialchars($data['email'] ?? '') ?>" class="<?= isset($errors['email']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['email'])): ?>
                <span class="error"><?= htmlspecialchars($errors['email']) ?></span>
            <?php endif; ?>
            <br>
            <label for="birthday">Дата рождения:</label>
            <input type="date" name="birthday" value="<?= htmlspecialchars($data['birthday'] ?? '') ?>" class="<?= isset($errors['birthday']) ? 'error-field' : '' ?>">
            <?php if (isset($errors['birthday'])): ?>
                <span class="error"><?= htmlspecialchars($errors['birthday']) ?></span>
            <?php endif; ?>
            <br>
            <label>Пол:</label>
            <div>
                <div class="<?= isset($errors['gender']) ? 'error-field' : '' ?>">
                    <input type="radio" name="gender" value="Мужской" id="gender_male" <?= ($data['gender'] ?? '') == 'Мужской' ? 'checked' : '' ?>>
                    <label for="gender_male">Мужской</label>
                    <input type="radio" name="gender" value="Женский" id="gender_female" <?= ($data['gender'] ?? '') == 'Женский' ? 'checked' : '' ?>>
                    <label for="gender_female">Женский</label>
                </div>
                <?php if (isset($errors['gender'])): ?>
                    <span class="error"><?= htmlspecialchars($errors['gender']) ?></span>
                <?php endif; ?>
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
            <?php if (isset($errors['language'])): ?>
                <span class="error"><?= htmlspecialchars($errors['language']) ?></span>
            <?php endif; ?>
            <br>
            <label for="biography">Биография:</label>
            <textarea name="biography" id="biography" class="<?= isset($errors['biography']) ? 'error-field' : '' ?>"><?=
                htmlspecialchars($data['biography'] ?? '') ?></textarea>
            <?php if (isset($errors['biography'])): ?>
            <span class="error"><?= htmlspecialchars($errors['biography']) ?></span>
            <?php endif; ?>
            <br>
            <label>
            <input type="checkbox" name="contract" value="on"
                   <?= isset($data['contract']) ? 'checked' : '' ?>>
            С контрактом ознакомлен
            <?php if (isset($errors['contract'])): ?>
                <span class="error"><?= htmlspecialchars($errors['contract']) ?></span>
            <?php endif; ?>
            </label>
            <br>
            <button type="submit">Сохранить</button>
        </form>
    </div>
</body>
</html>