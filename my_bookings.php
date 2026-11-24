<?php
session_start();
require_once 'includes/db.php';

// Only logged-in users
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?need_login=1');
    exit;
}
$user_id = $_SESSION['user_id'];

// Cancel a PENDING booking (only if it belongs to this user)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_id'])) {
    $stmt = $pdo->prepare(
        "UPDATE bookings SET booking_status = 'Cancelled'
         WHERE booking_id = ? AND user_id = ? AND booking_status = 'Pending'"
    );
    $stmt->execute([(int)$_POST['cancel_id'], $user_id]);
    header('Location: my_bookings.php?cancelled=' . ($stmt->rowCount() ? '1' : '0'));
    exit;
}

// Only THIS user's bookings (prevents seeing other guests' bookings)
$stmt = $pdo->prepare(
    "SELECT b.*, r.room_name FROM bookings b
     JOIN rooms r ON b.room_id = r.room_id
     WHERE b.user_id = ?
     ORDER BY b.booking_date DESC"
);
$stmt->execute([$user_id]);
$bookings = $stmt->fetchAll();

$badge = ['Pending' => 'bg-warning text-dark', 'Confirmed' => 'bg-success', 'Cancelled' => 'bg-danger'];

$page_title = 'My Bookings | Coral Nest Villa';
include 'includes/header.php';
?>
<div class="container my-5">
  <h2 class="section-title text-center">My Bookings</h2>

  <?php if (isset($_GET['booked'])): ?>
    <div class="alert alert-success">Booking request submitted. The villa will confirm it soon.</div>
  <?php endif; ?>
  <?php if (isset($_GET['cancelled'])): ?>
    <?php if ($_GET['cancelled'] === '1'): ?>
      <div class="alert alert-info">Booking cancelled.</div>
    <?php else: ?>
      <div class="alert alert-danger">Only your pending bookings can be cancelled.</div>
    <?php endif; ?>
  <?php endif; ?>

  <?php if (!$bookings): ?>
    <div class="alert alert-warning text-center">You have no bookings yet. <a href="rooms.php">Browse rooms</a></div>
  <?php else: ?>
    <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
      <table class="table align-middle mb-0">
        <thead>
          <tr><th>#</th><th>Room</th><th>Check-in</th><th>Check-out</th><th>Nights</th><th>Guests</th><th>Total</th><th>Status</th><th></th></tr>
        </thead>
        <tbody>
          <?php foreach ($bookings as $b): ?>
            <tr>
              <td><?= (int)$b['booking_id'] ?></td>
              <td><?= htmlspecialchars($b['room_name']) ?></td>
              <td><?= htmlspecialchars($b['check_in']) ?></td>
              <td><?= htmlspecialchars($b['check_out']) ?></td>
              <td><?= (int)$b['nights'] ?></td>
              <td><?= (int)$b['guests'] ?></td>
              <td>Rs. <?= number_format($b['total_amount']) ?></td>
              <td><span class="badge <?= $badge[$b['booking_status']] ?>"><?= htmlspecialchars($b['booking_status']) ?></span></td>
              <td>
                <?php if ($b['booking_status'] === 'Pending'): ?>
                  <form method="post" onsubmit="return confirm('Cancel this booking?');">
                    <input type="hidden" name="cancel_id" value="<?= (int)$b['booking_id'] ?>">
                    <button class="btn btn-outline-danger btn-sm">Cancel</button>
                  </form>
                <?php endif; ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php include 'includes/footer.php'; ?>
