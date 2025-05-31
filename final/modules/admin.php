<?php

global $db;
require_once './scripts/db.php';

function admin_get($request, $db) {
  return theme('admin');
}

function admin_post($request, $db) {
  return redirect('admin');
}
