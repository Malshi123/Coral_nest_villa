<?php
// Access control: only logged-in ADMIN users can open admin pages
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header('Location: /coral_nest/login.php?need_login=1');
    exit;
}
