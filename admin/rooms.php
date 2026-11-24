<?php
require_once '../includes/admin_check.php';
require_once '../includes/db.php';

// Activate / deactivate / delete a room (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room_id = (int)($_POST['room_id'] ?? 0);
    $action  = $_POST['action'] ?? '';
    $msg = 'invalid';

    if ($action === 'toggle') {
        $stmt = $pdo->prepare("UPDATE rooms SET room_status = IF(room_status = 'active', 'inactive', 'active') WHERE room_id = ?");
        $stmt->execute([$room_id]);
        $msg = 'toggled';
    } elseif ($action === 'delete') {
        // Do not delete rooms that already have bookings (keep booking records safe)
        $stmt = $pdo->prepare('SELECT COUNT(*) FROM bookings WHERE room_id = ?');
        $stmt->execute([$room_id]);
        if ($stmt->fetchColumn() > 0) {
            $msg = 'hasbookings';
        } else {
            $pdo->prepare('DELETE FROM rooms WHERE room_id = ?')->execute([$room_id]);
            $msg = 'deleted';
        }
    }
    header('Location: rooms.php?msg=' . $msg);
    exit;
}

$rooms = $pdo->query(
    "SELECT r.*, (SELECT COUNT(*) FROM bookings b WHERE b.room_id = r.room_id) AS booking_count
     FROM rooms r ORDER BY r.room_id"
)->fetchAll();

$messages = [
    'saved'       => ['success', 'Room saved successfully.'],
    'toggled'     => ['info',    'Room status updated.'],
    'deleted'     => ['success', 'Room deleted.'],
    'hasbookings' => ['danger',  'This room has bookings, so it cannot be deleted. Deactivate it instead.'],
    'invalid'     => ['danger',  'Invalid request.'],
];

$page_title = 'Manage Rooms | Coral Nest Villa';
$admin_page = 'rooms';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="container my-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h2 class="section-title mb-0">Manage Rooms</h2>
    <a href="room_form.php" class="btn btn-coral"><i class="bi bi-plus-lg"></i> Add New Room</a>
  </div>

  <?php if (isset($_GET['msg'], $messages[$_GET['msg']])): [$type, $text] = $messages[$_GET['msg']]; ?>
    <div class="alert alert-<?= $type ?>"><?= htmlspecialchars($text) ?></div>
  <?php endif; ?>

  <div class="table-responsive bg-white rounded-4 shadow-sm p-3">
    <table class="table align-middle mb-0">
      <thead><tr><th>#</th><th>Room</th><th>Type</th><th>Price / night</th><th>Max guests</th><th>Status</th><th>Bookings</th><th>Actions</th></tr></thead>
      <tbody>
        <?php foreach ($rooms as $r): ?>
          <tr>
            <td><?= (int)$r['room_id'] ?></td>
            <td><?= htmlspecialchars($r['room_name']) ?></td>
            <td><?= htmlspecialchars($r['room_type']) ?></td>
            <td>Rs. <?= number_format($r['price_per_night']) ?></td>
            <td><?= (int)$r['max_guests'] ?></td>
            <td><span class="badge <?= $r['room_status'] === 'active' ? 'bg-success' : 'bg-secondary' ?>"><?= htmlspecialchars($r['room_status']) ?></span></td>
            <td><?= (int)$r['booking_count'] ?></td>
            <td class="text-nowrap">
              <a href="room_form.php?id=<?= (int)$r['room_id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
              <form method="post" class="d-inline">
                <input type="hidden" name="room_id" value="<?= (int)$r['room_id'] ?>">
                <input type="hidden" name="action" value="toggle">
                <button class="btn btn-sm btn-outline-secondary"><?= $r['room_status'] === 'active' ? 'Deactivate' : 'Activate' ?></button>
              </form>
              <form method="post" class="d-inline" onsubmit="return confirm('Delete this room permanently?');">
                <input type="hidden" name="room_id" value="<?= (int)$r['room_id'] ?>">
                <input type="hidden" name="action" value="delete">
                <button class="btn btn-sm btn-outline-danger">Delete</button>
              </form>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
