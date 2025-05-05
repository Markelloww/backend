<?php
session_start();
header('Content-Type: text/html; charset=UTF-8');

$db_user = 'u68594';
$db_pass = '2729694';
$db_name = 'u68594';

if (!isset($_SESSION['admin_auth']) || $_SESSION['admin_auth'] !== true) {
    if (!isset($_SERVER['PHP_AUTH_USER']) || !isset($_SERVER['PHP_AUTH_PW'])) {
        header('WWW-Authenticate: Basic realm="Restricted Area"');
        header('HTTP/1.0 401 Unauthorized');
        echo '<h1>Требуется аутентификация.</h1>';
        exit();
    }

    try {
        $db = new PDO("mysql:host=localhost;dbname=$db_user", $db_name, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);

        $stmt = $db->prepare("SELECT * FROM admin_users WHERE login = :login");
        $stmt->execute([':login' => $_SERVER['PHP_AUTH_USER']]);
        $admin = $stmt->fetch();

        if (!$admin || !password_verify($_SERVER['PHP_AUTH_PW'], $admin['pass_hash'])) {
            header('WWW-Authenticate: Basic realm="Restricted Area"');
            header('HTTP/1.0 401 Unauthorized');
            echo '<h1>Неверные учетные данные.</h1>';
            exit();
        }

        $_SESSION['admin_auth'] = true;
        $_SESSION['admin_login'] = $_SERVER['PHP_AUTH_USER'];
        
    } catch (PDOException $e) {
        die("Ошибка базы данных: " . $e->getMessage());
    }
}

try {
    $db = new PDO("mysql:host=localhost;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
    ]);

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete'])) {
			$db->beginTransaction();
			$stmt = $db->prepare("DELETE FROM users WHERE application_id = :id");
			$stmt->execute([':id' => $_POST['delete']]);
				
			$stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = :id");
			$stmt->execute([':id' => $_POST['delete']]);
				
			$stmt = $db->prepare("DELETE FROM applications WHERE id = :id");
			$stmt->execute([':id' => $_POST['delete']]);
				
			$db->commit();
			header("Location: admin.php");
			exit();
		}
        elseif (isset($_POST['edit'])) {
            $stmt = $db->prepare("SELECT a.*, GROUP_CONCAT(pl.name) as languages 
                                 FROM applications a 
                                 JOIN application_languages al ON a.id = al.application_id 
                                 JOIN programming_languages pl ON al.language_id = pl.id 
                                 WHERE a.id = :id 
                                 GROUP BY a.id");
            $stmt->execute([':id' => $_POST['edit']]);
            $userData = $stmt->fetch();
            
            if ($userData) {
                $editData = [
                    'id' => $userData['id'],
                    'name' => $userData['name'],
                    'phone' => $userData['phone'],
                    'email' => $userData['email'],
                    'birthday' => $userData['birthday'],
                    'gender' => $userData['gender'],
                    'biography' => $userData['biography'],
                    'languages' => explode(',', $userData['languages'])
                ];
            }
        } elseif (isset($_POST['update'])) {
            $db->beginTransaction();
            
            $stmt = $db->prepare("UPDATE applications SET 
                                 name = :name, phone = :phone, email = :email, 
                                 birthday = :birthday, gender = :gender, biography = :biography 
                                 WHERE id = :id");
            $stmt->execute([
                ':name' => $_POST['name'],
                ':phone' => $_POST['phone'],
                ':email' => $_POST['email'],
                ':birthday' => $_POST['birthday'],
                ':gender' => $_POST['gender'],
                ':biography' => $_POST['biography'] ?? null,
                ':id' => $_POST['id']
            ]);
            
            $stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = :app_id");
            $stmt->execute([':app_id' => $_POST['id']]);
            
            $stmt = $db->prepare("INSERT INTO application_languages (application_id, language_id)
                                  SELECT :app_id, id FROM programming_languages WHERE name = :lang_name");
            foreach ($_POST['language'] as $lang) {
                $stmt->execute([':app_id' => $_POST['id'], ':lang_name' => $lang]);
            }
            
            $db->commit();
            header("Location: admin.php");
            exit();
        }
    }

    $stmt = $db->prepare("SELECT a.*, GROUP_CONCAT(pl.name) as languages 
                         FROM applications a 
                         JOIN application_languages al ON a.id = al.application_id 
                         JOIN programming_languages pl ON al.language_id = pl.id 
                         GROUP BY a.id");
    $stmt->execute();
    $applications = $stmt->fetchAll();

    $stmt = $db->prepare("SELECT pl.name, COUNT(al.application_id) as count 
                         FROM programming_languages pl 
                         LEFT JOIN application_languages al ON pl.id = al.language_id 
                         GROUP BY pl.name 
                         ORDER BY count DESC");
    $stmt->execute();
    $languageStats = $stmt->fetchAll();

} catch (PDOException $e) {
    die("Ошибка базы данных: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Админ-панель</title>
	<link href="./css/admin.css" rel="stylesheet">
</head>
<body>
    <h1>Админ-панель</h1>
    
    <?php if (isset($editData)): ?>
        <div class="edit-form">
            <h2>Редактирование пользователя</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($editData['id']) ?>">
                
                <label>ФИО: <input type="text" name="name" value="<?= htmlspecialchars($editData['name']) ?>" required></label><br>
                <label>Телефон: <input type="text" name="phone" value="<?= htmlspecialchars($editData['phone']) ?>" required></label><br>
                <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($editData['email']) ?>" required></label><br>
                <label>Дата рождения: <input type="date" name="birthday" value="<?= htmlspecialchars($editData['birthday']) ?>" required></label><br>
                
                <label>Пол: 
                    <select name="gender" required>
                        <option value="Мужской" <?= $editData['gender'] == 'Мужской' ? 'selected' : '' ?>>Мужской</option>
                        <option value="Женский" <?= $editData['gender'] == 'Женский' ? 'selected' : '' ?>>Женский</option>
                    </select>
                </label><br>
                
                <label>Биография: 
                <br>
                <textarea name="biography"><?= htmlspecialchars($editData['biography']) ?></textarea></label><br>
                
                <label>Языки программирования: 
                    <br>
                    <select name="language[]" multiple required>
                        <?php 
                        $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
                        foreach ($languages as $lang): ?>
                            <option value="<?= htmlspecialchars($lang) ?>" 
                                <?= in_array($lang, $editData['languages']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lang) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </label><br>
                
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
                <th>Дата рождения</th>
                <th>Пол</th>
                <th>Биография</th>
                <th>Языки</th>
                <th>Действия</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($applications as $app): ?>
                <tr>
                    <td><?= htmlspecialchars($app['id']) ?></td>
                    <td><?= htmlspecialchars($app['name']) ?></td>
                    <td><?= htmlspecialchars($app['phone']) ?></td>
                    <td><?= htmlspecialchars($app['email']) ?></td>
                    <td><?= htmlspecialchars($app['birthday']) ?></td>
                    <td><?= htmlspecialchars($app['gender']) ?></td>
                    <td><?= htmlspecialchars($app['biography']) ?></td>
                    <td><?= htmlspecialchars($app['languages']) ?></td>
                    <td>
                        <form method="POST" style="display: inline;">
                            <input type="hidden" name="edit" value="<?= $app['id'] ?>">
                            <button type="submit">Редактировать</button>
                        </form>
                        <form method="POST" style="display: inline;" onsubmit="return confirm('Удалить эту запись?');">
                            <input type="hidden" name="delete" value="<?= $app['id'] ?>">
                            <button type="submit">Удалить</button>
                        </form>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="stats">
        <h2>Статистика по языкам программирования</h2>
        <table>
            <thead>
                <tr>
                    <th>Язык программирования</th>
                    <th>Количество пользователей</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($languageStats as $stat): ?>
                    <tr>
                        <td><?= htmlspecialchars($stat['name']) ?></td>
                        <td><?= htmlspecialchars($stat['count']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>