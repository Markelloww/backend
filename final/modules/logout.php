<?php

global $db;
require_once './scripts/db.php';

function logout_post($request, $db)
{
    session_unset();
    session_destroy();
    return redirect('');
}