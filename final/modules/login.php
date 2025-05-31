<?php

global $db;
require_once './scripts/db.php';

function login_get($request, $db)
{

  // вот здесь будет csrf токен генерироваться, затем передаваться в шаблон

  return theme('login');
}

function login_post($request, $db)
{
  // валидируем csrf защиту

  $login = $request['post']['login'];
  $password = $request['post']['password'];

  if (auth_check($login, $password)) {
    $_SESSION['login'] = $login;
    return redirect('');
  } else {
    $errors[] = 'Введены неверные данные';
    return theme('login', ['errors' => $errors]);
  }
}
