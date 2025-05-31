<?php

// CREATE TABLE final_applications (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     name VARCHAR(100) NOT NULL,
//     phone VARCHAR(20) NOT NULL,
//     email VARCHAR(100) NOT NULL,
//     comment TEXT
// );

// CREATE TABLE final_users (
//     id INT AUTO_INCREMENT PRIMARY KEY,
//     application_id INT,
//     username VARCHAR(50) NOT NULL UNIQUE,
//     password_hash VARCHAR(255) NOT NULL,
//     FOREIGN KEY (application_id) REFERENCES final_applications(id) ON DELETE SET NULL
// );

global $db;

try {
    $db = new PDO(
        'mysql:host=' . conf('db_host') . ';dbname=' . conf('db_name'),
        conf('db_user'),
        conf('db_psw'),
        array(PDO::MYSQL_ATTR_FOUND_ROWS => true, PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES utf8'));
} catch (PDOException $e) {
	die("Ошибка БД!". $e->getMessage());
}

function db_row($stmt)
{
    return $stmt->fetch(PDO::FETCH_ASSOC);
}

function db_error()
{
    global $db;
    return $db->errorInfo();
}

function db_query($query)
{
    global $db;
    $args = func_get_args();
    array_shift($args);
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($args);
        
        $r = array();
        while ($row = db_row($stmt)) {
            if (isset($row['id']) && !isset($r[$row['id']])) {
                $r[$row['id']] = $row;
            } else {
                $r[] = $row;
            }
        }
        return $r;
    } catch (PDOException $e) {
        return false;
    }
}

function db_result($query)
{
    global $db;
    $args = func_get_args();
    array_shift($args);
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($args);
        
        $row = $stmt->fetch(PDO::FETCH_NUM);
        return $row ? $row[0] : false;
    } catch (PDOException $e) {
        return false;
    }
}

function db_command($query)
{
    global $db;
    $args = func_get_args();
    array_shift($args);
    
    try {
        $stmt = $db->prepare($query);
        $stmt->execute($args);
        return $stmt->rowCount();
    } catch (PDOException $e) {
        return false;
    }
}

function db_insert_id()
{
    global $db;
    return $db->lastInsertId();
}

function db_get($name, $default = FALSE)
{
	if (strlen($name) == 0) {
		return $default;
	}
	$value = db_result("SELECT value FROM variable WHERE name = ?", $name);
	if ($value === FALSE) {
		return $default;
	} else {
		return $value;
	}
}

function db_set($name, $value)
{
	if (strlen($name) == 0) {
		return;
	}

	$v = db_get($name);
	if ($v === FALSE) {
		$q = "INSERT INTO variable VALUES (?, ?)";
		return db_command($q, $name, $value) > 0;
	} else {
		$q = "UPDATE variable SET value = ? WHERE name = ?";
		return db_command($q, $value, $name) > 0;
	}
}

function db_sort_sql()
{
}

function db_pager_query()
{
}

function db_array()
{
    global $db;
	$args = func_get_args();
	$key = array_shift($args);
	$query = array_shift($args);
	$q = $db->prepare($query);
	$res = $q->execute($args);
	$r = array();
	if ($res) {
		while ($row = db_row($res)) {
			if (!empty($key) && isset($row[$key]) && !isset($r[$row[$key]])) {
				$r[$row[$key]] = $row;
			} else {
				$r[] = $row;
			}
		}
	}
	return $r;
}

function auth_check($login, $password)
{
    global $db;
    try {
        $stmt = $db->prepare("SELECT password_hash FROM final_users WHERE username = :login");
        $stmt->bindParam(':login', $login, PDO::PARAM_STR);
        $stmt->execute();
        
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$user) {
            return false;
        }
        
        return password_verify($password, $user['password_hash']);
    }
    catch (Exception $e) {
        return false;
    }
}