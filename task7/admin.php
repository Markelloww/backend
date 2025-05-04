<?php
header('Content-Type: text/html; charset=UTF-8');
header("X-XSS-Protection: 1; mode=block");
header("Content-Security-Policy: default-src 'self'; script-src 'self'");

$db_user = 'u68594';
$db_pass = '2729694';
$db_name = 'u68594';

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

if (empty($_SERVER['PHP_AUTH_USER']) || empty($_SERVER['PHP_AUTH_PW'])) {
    header('HTTP/1.1 401 Unauthorized');
    header('WWW-Authenticate: Basic realm="Admin Panel"');
    echo '<h1>401 Требуется авторизация</h1>';
    exit();
}

try {
    $db = new PDO("mysql:host=localhost;dbname=$db_name", $db_user, $db_pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8mb4'
    ]);

    $stmt = $db->prepare("SELECT * FROM admin_users WHERE login = :login");
    $stmt->execute([':login' => $_SERVER['PHP_AUTH_USER']]);
    $admin = $stmt->fetch();

    if ($_SERVER['PHP_AUTH_USER'] !== 'admin' || $_SERVER['PHP_AUTH_PW'] !== 'admin') {
		header('HTTP/1.1 401 Unauthorized');
		header('WWW-Authenticate: Basic realm="Admin Panel"');
		echo '<h1>401 Неверные учетные данные</h1>';
		exit();
	}

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        if (isset($_POST['delete'])) {
			$db->beginTransaction();
			try {
				$stmt = $db->prepare("DELETE FROM users WHERE application_id = :id");
				$stmt->execute([':id' => $_POST['delete']]);
				
				$stmt = $db->prepare("DELETE FROM application_languages WHERE application_id = :id");
				$stmt->execute([':id' => $_POST['delete']]);
				
				$stmt = $db->prepare("DELETE FROM applications WHERE id = :id");
				$stmt->execute([':id' => $_POST['delete']]);
				
				$db->commit();
				header("Location: admin.php");
				exit();
			} catch (PDOException $e) {
				$db->rollBack();
				die("Ошибка при удалении: " . $e->getMessage());
			}
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
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        table { border-collapse: collapse; width: 100%; margin-bottom: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f2f2f2; }
        tr:nth-child(even) { background-color: #f9f9f9; }
        .edit-form { background: #f5f5f5; padding: 15px; margin: 15px 0; border: 1px solid #ddd; }
        .stats { margin-top: 30px; }
    </style>
</head>
<body>
    <h1>Админ-панель</h1>
    
    <?php if (isset($editData)): ?>
        <div class="edit-form">
            <h2>Редактирование пользователя</h2>
            <form method="POST">
                <input type="hidden" name="id" value="<?= htmlspecialchars($editData['id']) ?>">
                
                <label>ФИО: <input type="text" name="name" value="<?= htmlspecialchars($editData['name'], ENT_QUOTES, 'UTF-8') ?>" required></label><br>
                <label>Телефон: <input type="text" name="phone" value="<?= htmlspecialchars($editData['phone'], ENT_QUOTES, 'UTF-8') ?>" required></label><br>
                <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($editData['email'], ENT_QUOTES, 'UTF-8') ?>" required></label><br>
                <label>Дата рождения: <input type="date" name="birthday" value="<?= htmlspecialchars($editData['birthday'], ENT_QUOTES, 'UTF-8') ?>" required></label><br>
                
                <label>Пол: 
                    <select name="gender" required>
                        <option value="Мужской" <?= $editData['gender'] == 'Мужской' ? 'selected' : '' ?>>Мужской</option>
                        <option value="Женский" <?= $editData['gender'] == 'Женский' ? 'selected' : '' ?>>Женский</option>
                    </select>
                </label><br>
                
                <label>Биография: 
                <br>
                <textarea name="biography"><?= htmlspecialchars($editData['biography'], ENT_QUOTES, 'UTF-8') ?></textarea></label><br>
                
                <label>Языки программирования: 
                    <br>
                    <select name="language[]" multiple required>
                        <?php 
                        $languages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 'Java', 'Haskel', 'Clojure', 'Prolog', 'Scala', 'Go'];
                        foreach ($languages as $lang): ?>
                            <option value="<?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>" 
                                <?= in_array($lang, $editData['languages']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($lang, ENT_QUOTES, 'UTF-8') ?>
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
                    <td><?= htmlspecialchars($app['id'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($app['name'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($app['phone'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($app['email'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($app['birthday'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($app['gender'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($app['biography'], ENT_QUOTES, 'UTF-8') ?></td>
                    <td><?= htmlspecialchars($app['languages'], ENT_QUOTES, 'UTF-8') ?></td>
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
                        <td><?= htmlspecialchars($stat['name'], ENT_QUOTES, 'UTF-8') ?></td>
                        <td><?= htmlspecialchars($stat['count'], ENT_QUOTES, 'UTF-8') ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</body>
</html>