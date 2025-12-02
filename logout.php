<?php
// logout page, wipes session and sends user back to homepage
require_once __DIR__ . '/header.php';

// nuke everything stored in $_SESSION for this user
$_SESSION = [];

// if theres an active session, kill it completely (server side)
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

redirect('index.php');