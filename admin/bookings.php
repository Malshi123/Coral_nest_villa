<?php
require_once '../includes/admin_check.php';
require_once '../includes/db.php';

// Confirm / cancel a booking (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $booking_id = (int)($_POST['booking_id'] ?? 0);
    $action     = $_POST['action'] ?? '';
    $msg        = 'invalid';

    $stmt = $pdo->prepare('SELECT * FROM bookings WHERE booking_id = ?');
    $stmt->execute([$booking_id]);
    $b = $stmt->fetch();

    if ($b && $action === 'confirm' && $b['booking_status'] === 'Pending') {
        // Transaction + row lock: two admins cannot confirm overlapping bookings at the same time
        $pdo->beginTransaction();
        $pdo->prepare('SELECT room_id FROM rooms WHERE room_id = ? FOR UPDATE')->execute([$b['room_id']]);

        // Prevent overlapping CONFIRMED bookings for the same room
        $chk = $pdo->prepare(
            "SELECT COUNT(*) FROM bookings
             WHERE room_id = ? AND booking_status = 'Confirmed' AND booking_id <> ?
               AND check_in < ? AND check_out > ?"
        );
        $chk->execute([$b['room_id'], $booking_id, $b['check_out'], $b['check_in']]);

        if ($chk->fetchColumn() > 0) {
            $pdo->rollBack();
            $msg = 'overlap';
        } else {
            $pdo->prepare("UPDATE bookings SET booking_status = 'Confirmed' WHERE booking_id = ?")->execute([$booking_id]);
            $pdo->commit();
            $msg = 'confirmed';
        }
    } elseif ($b && $action === 'cancel' && in_array($b['booking_status'], ['Pending', 'Confirmed'], true)) {
        $pdo->prepare("UPDATE bookings SET booking_status = 'Cancelled' WHERE booking_id = ?")->execute([$booking_id]);
        $msg = 'cancelled';
    }
    header('Location: bookings.php?msg=' . $msg);
    exit;
}

// Optional filter by status
$filter = $_GET['status'] ?? '';
$sql = "SELECT b.*, u.name AS guest_name, u.email, u.contact_number, r.room_name
        FROM bookings b
        JOIN users u ON b.user_id = u.user_id
        JOIN rooms r ON b.room_id = r.room_id";
$params = [];
if (in_array($filter, ['Pending', 'Confirmed', 'Cancelled'], true)) {
    $sql .= ' WHERE b.booking_status = ?';
    $params[] = $filter;
}
$sql .= ' ORDER BY b.booking_date DESC';
$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$bookings = $stmt->fetchAll();

$badge = ['Pending' => 'bg-warning text-dark', 'Confirmed' => 'bg-success', 'Cancelled' => 'bg-danger'];
$messages = [
    'confirmed' => ['success', 'Booking confirmed.'],
    'cancelled' => ['info',    'Booking cancelled.'],
    'overlap'   => ['danger',  'Cannot confirm: this room already has a confirmed booking for overlapping dates.'],
    'invalid'   => ['danger',  'Invalid request.'],
];

$page_title = 'Manage Bookings | Coral Nest Villa';
$admin_page = 'bookings';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="container my-4">
  <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 gap-2">
    <h2 class="section-title mb-0">Manage Bookings</h2>
    <div class="btn-group">
      <a href="bookings.php" class="btn btn-sm <?= $filter === '' ? 'btn-coral' : 'btn-outline-secondary' ?>">All</a>
      <a href="bookings.php?status=Pending" class="btn btn-sm <?= $filter === 'Pending' ? 'btn-coral' : 'btn-outline-secondary' ?>">Pending</a>
      <a href="bookings.php?status=Confirmed" class="btn btn-sm <?= $filter === 'Confirmed' ? 'btn-coral' : 'btn-outline-secondary' ?>">Confirmed</a>
      <a href="bookings.php?status=Cancelled" class="btn btn-sm <?= $filter === 'Cancelled' ? 'btn-coral' : 'btn-outline-secondary' ?>">Cancelled</a>
    </div>
  </div>

  <?php if (isset($_GET['msg'], $messages[$_GET['msg']])): [$type, $text] = $messages[$_GET['msg']]; ?>
    <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($text) ?></div>
  <?php endif; ?>

  <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
    <table class="table align-middle mb-0">
      <thead><tr><th>#</th><th>Guest</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Nights</th><th>Total</th><th>Status</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($bookings as $b): ?>
          <tr>
            <td><?= (int)$b['booking_id'] ?></td>
            <td>
              <?= htmlspecialchars($b['guest_name']) ?><br>
              <small class="text-muted"><?= htmlspecialchars($b['email']) ?> | <?= htmlspecialchars($b['contact_number']) ?></small>
            </td>
            <td><?= htmlspecialchars($b['room_name']) ?></td>
            <td><?= htmlspecialchars($b['check_in']) ?></td>
            <td><?= htmlspecialchars($b['check_out']) ?></td>
            <td><?= (int)$b['nights'] ?></td>
            <td>Rs. <?= number_format($b['total_amount']) ?></td>
            <td><span class="badge <?= $badge[$b['booking_status']] ?>"><?= htmlspecialchars($b['booking_status']) ?></span></td>
            <td class="text-nowrap">
              <?php if ($b['booking_status'] === 'Pending'): ?>
                <form method="post" class="d-inline">
                  <input type="hidden" name="booking_id" value="<?= (int)$b['booking_id'] ?>">
                  <input type="hidden" name="action" value="confirm">
                  <button class="btn btn-sm btn-success">Confirm</button>
                </form>
              <?php endif; ?>
              <?php if (in_array($b['booking_status'], ['Pending', 'Confirmed'], true)): ?>
                <form method="post" class="d-inline" onsubmit="return confirm('Cancel this booking?');">
                  <input type="hidden" name="booking_id" value="<?= (int)$b['booking_id'] ?>">
                  <input type="hidden" name="action" value="cancel">
                  <button class="btn btn-sm btn-outline-danger">Cancel</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
        <?php if (!$bookings): ?><tr><td colspan="9" class="text-center text-muted">No bookings found.</td></tr><?php endif; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
