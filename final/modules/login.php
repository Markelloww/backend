<?php

global $db;
require_once './scripts/db.php';

function login_get($request) {
  return theme('login');
}

function login_post($request) {
  redirect();
}
