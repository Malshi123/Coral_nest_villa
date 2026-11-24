<?php
require_once '../includes/admin_check.php';
require_once '../includes/db.php';

// Booking statistics (COUNT / SUM queries)
$stats = $pdo->query(
    "SELECT COUNT(*) AS total,
            COALESCE(SUM(booking_status = 'Pending'), 0)   AS pending,
            COALESCE(SUM(booking_status = 'Confirmed'), 0) AS confirmed,
            COALESCE(SUM(booking_status = 'Cancelled'), 0) AS cancelled,
            COALESCE(SUM(CASE WHEN booking_status = 'Confirmed' THEN total_amount END), 0) AS revenue
     FROM bookings"
)->fetch();

$rooms_count  = $pdo->query("SELECT COUNT(*) FROM rooms WHERE room_status = 'active'")->fetchColumn();
$guests_count = $pdo->query("SELECT COUNT(*) FROM users WHERE role = 'guest'")->fetchColumn();

$recent = $pdo->query(
    "SELECT b.*, u.name AS guest_name, r.room_name
     FROM bookings b
     JOIN users u ON b.user_id = u.user_id
     JOIN rooms r ON b.room_id = r.room_id
     ORDER BY b.booking_date DESC LIMIT 5"
)->fetchAll();

$badge = ['Pending' => 'bg-warning text-dark', 'Confirmed' => 'bg-success', 'Cancelled' => 'bg-danger'];

$page_title = 'Admin Dashboard | Coral Nest Villa';
$admin_page = 'dashboard';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="container my-4">
  <h2 class="section-title">Admin Dashboard</h2>

  <div class="row g-3 mb-4">
    <div class="col-6 col-md-3"><div class="feature-box"><i class="bi bi-journal-text"></i><h3 class="mb-0"><?= (int)$stats['total'] ?></h3><small>Total Bookings</small></div></div>
    <div class="col-6 col-md-3"><div class="feature-box"><i class="bi bi-hourglass-split"></i><h3 class="mb-0"><?= (int)$stats['pending'] ?></h3><small>Pending</small></div></div>
    <div class="col-6 col-md-3"><div class="feature-box"><i class="bi bi-check-circle"></i><h3 class="mb-0"><?= (int)$stats['confirmed'] ?></h3><small>Confirmed</small></div></div>
    <div class="col-6 col-md-3"><div class="feature-box"><i class="bi bi-x-circle"></i><h3 class="mb-0"><?= (int)$stats['cancelled'] ?></h3><small>Cancelled</small></div></div>
    <div class="col-12 col-md-4"><div class="feature-box"><i class="bi bi-cash-coin"></i><h3 class="mb-0">Rs. <?= number_format($stats['revenue']) ?></h3><small>Revenue (confirmed bookings)</small></div></div>
    <div class="col-6 col-md-4"><div class="feature-box"><i class="bi bi-door-open"></i><h3 class="mb-0"><?= (int)$rooms_count ?></h3><small>Active Rooms</small></div></div>
    <div class="col-6 col-md-4"><div class="feature-box"><i class="bi bi-people"></i><h3 class="mb-0"><?= (int)$guests_count ?></h3><small>Registered Guests</small></div></div>
  </div>

  <h5 class="section-title">Recent Bookings</h5>
  <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
    <table class="table align-middle mb-0">
      <thead><tr><th>#</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Total</th><th>Status</th></tr></thead>
      <tbody>
        <?php foreach ($recent as $b): ?>
          <tr>
            <td><?= (int)$b['booking_id'] ?></td>
            <td><?= htmlspecialchars($b['guest_name']) ?></td>
            <td><?= htmlspecialchars($b['room_name']) ?></td>
            <td><?= htmlspecialchars($b['check_in']) ?></td>
            <td><?= htmlspecialchars($b['check_out']) ?></td>
            <td>Rs. <?= number_format($b['total_amount']) ?></td>
            <td><span class="badge <?= $badge[$b['booking_status']] ?>"><?= htmlspecialchars($b['booking_status']) ?></span></td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$recent): ?><tr><td colspan="7" class="text-center text-muted">No bookings yet.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
  <a href="bookings.php" class="btn btn-coral mt-3">Manage All Bookings</a>
</div>
<?php include '../includes/footer.php'; ?>
