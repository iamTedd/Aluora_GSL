<?php
/**
 * Aluora GSL - Logout Handler
 */

session_start();
require_once 'config.php';

if (isLoggedIn()) {
    logActivity($_SESSION['user_id'], 'logout', 'User logged out');
}

session_destroy();
session_unset();

header('Location: index.php');
exit;
