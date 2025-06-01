<?php
define('DISPLAY_ERRORS', 1);
define('INCLUDE_PATH', './scripts' . PATH_SEPARATOR . './modules');

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
  'basedir' => '/backend/final/',
  'login' => 'admin',
  'password' => '123',
  'admin_mail' => 'sin@kubsu.ru',
  'db_host' => 'localhost',
  'db_name' => $config['db_user'],
  'db_user' => $config['db_user'],
  'db_psw' => $config['db_pass']
);

$urlconf = array(
  '' => array('module' => 'front'),
  '/^login$/' => array('module' => 'login'),
  '/^admin$/' => array('module' => 'admin'),
  '/^logout$/' => array('module' => 'logout'),
);


header('Cache-Control: no-cache, must-revalidate');
header('Expires: Sat, 26 Jul 1997 05:00:00 GMT');
header('Content-Type: text/html; charset=' . $conf['charset']);
header('Content-Type: application/json');