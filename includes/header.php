<?php
// Header: session start + navbar (included on every page)
if (session_status() === PHP_SESSION_NONE) { session_start(); }
define('BASE_URL', '/coral_nest/');   // folder name inside htdocs
$logged_in = isset($_SESSION['user_id']);
$is_admin  = $logged_in && ($_SESSION['role'] ?? '') === 'admin';
$page_title = $page_title ?? 'Coral Nest Villa, Hikkaduwa';
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= htmlspecialchars($page_title) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
  <link href="<?= BASE_URL ?>css/style.css" rel="stylesheet">
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark sticky-top">
  <div class="container">
    <a class="navbar-brand fw-semibold" href="<?= BASE_URL ?>index.php">
      <i class="bi bi-water"></i> Coral Nest Villa
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="nav">
      <ul class="navbar-nav ms-auto align-items-lg-center gap-lg-2">
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>index.php">Home</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>rooms.php">Rooms</a></li>
        <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>gallery.php">Gallery</a></li>
        
        <?php if ($logged_in): ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>my_bookings.php">My Bookings</a></li>
          <?php if ($is_admin): ?>
            <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>admin/dashboard.php">Admin</a></li>
          <?php endif; ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>logout.php">Logout (<?= htmlspecialchars($_SESSION['name']) ?>)</a></li>
        <?php else: ?>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>login.php">Login</a></li>
          <li class="nav-item"><a class="nav-link" href="<?= BASE_URL ?>register.php">Register</a></li>
        <?php endif; ?>
        <li class="nav-item"><a class="btn btn-coral btn-sm ms-lg-2" href="<?= BASE_URL ?>rooms.php">Book Now</a></li>
      </ul>
    </div>
  </div>
</nav>
