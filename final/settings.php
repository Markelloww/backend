<?php
define('DISPLAY_ERRORS', 1);
define(
  'INCLUDE_PATH',
  __DIR__ . '/static' . PATH_SEPARATOR .
  __DIR__ . '/scripts' . PATH_SEPARATOR .
  __DIR__ . '/modules'
);
set_include_path(INCLUDE_PATH);

$config = parse_ini_file('./config.ini');

$conf = array(
  'sitename' => 'Final project 8',
  'theme' => './theme',
  'charset' => 'UTF-8',
  'clean_urls' => TRUE,
  'display_errors' => 1,
  'date_format' => 'Y.m.d',
  'date_format_2' => 'Y.m.d H:i',
  'date_format_3' => 'd.m.Y',
  'basedir' => '/final/',
  'login' => 'admin',
  'password' => '123',
  'admin_mail' => 'sin@kubsu.ru',
  'db_host' => 'localhost',
  'db_name' => $config['db_user'],
  'db_user' => $config['db_user'],
  'db_psw' => $config['db_pass']
);

// $urlconf = array(
// 	'' => array('module' => 'front'), // гл. страница
//   '/^login$/' => array('module' => 'auth_basic'), // страница авторизации
// //   '/^logout$/' => array('module' => 'admin', 'auth' => 'auth_basic'),
// //   '/^admin\/(\d+)$/' => array('module' => 'admin', 'auth' => 'auth_basic'),
// );

$urlconf = [
  '' => [
    'module' => 'front',
    'GET' => 'front_get',
    'POST' => 'front_post'
  ],

  '/^login$/' => [
    'module' => 'login',
    'GET' => 'login_get',
    'POST' => 'login_post'
  ],

  // // Пример маршрута с параметром
  // '~^admin/(\d+)$~' => [
  //     'module' => 'admin',
  //     'auth' => 'auth_basic',
  //     'GET' => 'admin_item_get',
  //     'POST' => 'admin_item_post'
  // ],

  // // Выход
  // '~^logout$~' => [
  //     'module' => 'auth_basic',
  //     'GET' => 'logout_handler'
  // ]
];

header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: text/html; charset=' . $conf['charset']);