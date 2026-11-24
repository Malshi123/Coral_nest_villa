<?php
// Small admin menu shown at the top of every admin page
// Set $admin_page = 'dashboard' | 'rooms' | 'bookings' before including
$admin_page = $admin_page ?? '';
?>
<div class="bg-white border-bottom">
  <div class="container py-2 d-flex flex-wrap gap-2">
    <a href="<?= BASE_URL ?>admin/dashboard.php" class="btn btn-sm <?= $admin_page === 'dashboard' ? 'btn-coral' : 'btn-outline-secondary' ?>"><i class="bi bi-speedometer2"></i> Dashboard</a>
    <a href="<?= BASE_URL ?>admin/rooms.php" class="btn btn-sm <?= $admin_page === 'rooms' ? 'btn-coral' : 'btn-outline-secondary' ?>"><i class="bi bi-door-open"></i> Rooms</a>
    <a href="<?= BASE_URL ?>admin/bookings.php" class="btn btn-sm <?= $admin_page === 'bookings' ? 'btn-coral' : 'btn-outline-secondary' ?>"><i class="bi bi-calendar-check"></i> Bookings</a>
  </div>
</div>
