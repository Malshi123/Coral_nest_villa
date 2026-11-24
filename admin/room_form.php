<?php
require_once '../includes/admin_check.php';
require_once '../includes/db.php';

// Same page is used for ADD (no id) and EDIT (id given)
$id = (int)($_GET['id'] ?? $_POST['room_id'] ?? 0);

$room = [
    'room_name' => '', 'room_type' => 'Standard', 'description' => '', 'facilities' => '',
    'price_per_night' => '', 'max_guests' => 2, 'image' => '', 'room_status' => 'active',
];

if ($id) {
    $stmt = $pdo->prepare('SELECT * FROM rooms WHERE room_id = ?');
    $stmt->execute([$id]);
    $existing = $stmt->fetch();
    if (!$existing) {
        header('Location: rooms.php');
        exit;
    }
    $room = $existing;
}

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $room['room_name']       = trim($_POST['room_name'] ?? '');
    $room['room_type']       = trim($_POST['room_type'] ?? '');
    $room['description']     = trim($_POST['description'] ?? '');
    $room['facilities']      = trim($_POST['facilities'] ?? '');
    $room['price_per_night'] = $_POST['price_per_night'] ?? '';
    $room['max_guests']      = (int)($_POST['max_guests'] ?? 0);
    $room['image']           = trim($_POST['image'] ?? '');
    $room['room_status']     = $_POST['room_status'] ?? 'active';

    // Server-side validation
    if (strlen($room['room_name']) < 3)                      $errors[] = 'Room name must be at least 3 characters.';
    if ($room['room_type'] === '')                           $errors[] = 'Room type is required.';
    if (!is_numeric($room['price_per_night']) || $room['price_per_night'] <= 0) $errors[] = 'Price must be a number greater than 0.';
    if ($room['max_guests'] < 1 || $room['max_guests'] > 10) $errors[] = 'Max guests must be between 1 and 10.';
    if (!in_array($room['room_status'], ['active', 'inactive'], true)) $errors[] = 'Invalid status.';
    if ($room['image'] === '') {
        $room['image'] = 'default.jpg';
    } elseif (!preg_match('/^[A-Za-z0-9_\-\.]+\.(jpg|jpeg|png|webp)$/i', $room['image'])) {
        $errors[] = 'Image must be a file name like room1.jpg (jpg, jpeg, png or webp).';
    }

    if (!$errors) {
        if ($id) {
            $stmt = $pdo->prepare(
                'UPDATE rooms SET room_name=?, room_type=?, description=?, facilities=?, price_per_night=?, max_guests=?, image=?, room_status=?
                 WHERE room_id=?'
            );
            $stmt->execute([$room['room_name'], $room['room_type'], $room['description'], $room['facilities'],
                            $room['price_per_night'], $room['max_guests'], $room['image'], $room['room_status'], $id]);
        } else {
            $stmt = $pdo->prepare(
                'INSERT INTO rooms (room_name, room_type, description, facilities, price_per_night, max_guests, image, room_status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?)'
            );
            $stmt->execute([$room['room_name'], $room['room_type'], $room['description'], $room['facilities'],
                            $room['price_per_night'], $room['max_guests'], $room['image'], $room['room_status']]);
        }
        header('Location: rooms.php?msg=saved');
        exit;
    }
}

$types = ['Standard', 'Deluxe', 'Suite', 'Budget', 'Family'];

$page_title = ($id ? 'Edit Room' : 'Add Room') . ' | Coral Nest Villa';
$admin_page = 'rooms';
include '../includes/header.php';
include '../includes/admin_nav.php';
?>
<div class="container my-4" style="max-width:720px;">
  <div class="card shadow-sm border-0 rounded-4">
    <div class="card-body p-4">
      <h3 class="section-title"><?= $id ? 'Edit Room' : 'Add New Room' ?></h3>

      <?php if ($errors): ?>
        <div class="alert alert-danger"><ul class="mb-0">
          <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
        </ul></div>
      <?php endif; ?>

      <form method="post">
        <input type="hidden" name="room_id" value="<?= $id ?>">
        <div class="row g-3">
          <div class="col-md-8">
            <label class="form-label">Room Name</label>
            <input type="text" name="room_name" class="form-control" value="<?= htmlspecialchars($room['room_name']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Room Type</label>
            <select name="room_type" class="form-select">
              <?php foreach ($types as $t): ?>
                <option <?= $room['room_type'] === $t ? 'selected' : '' ?>><?= $t ?></option>
              <?php endforeach; ?>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Description</label>
            <textarea name="description" rows="3" class="form-control"><?= htmlspecialchars($room['description']) ?></textarea>
          </div>
          <div class="col-12">
            <label class="form-label">Facilities (comma separated)</label>
            <input type="text" name="facilities" class="form-control" placeholder="AC, WiFi, Breakfast" value="<?= htmlspecialchars($room['facilities']) ?>">
          </div>
          <div class="col-md-4">
            <label class="form-label">Price per night (Rs.)</label>
            <input type="number" name="price_per_night" step="0.01" min="1" class="form-control" value="<?= htmlspecialchars($room['price_per_night']) ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Max guests</label>
            <input type="number" name="max_guests" min="1" max="10" class="form-control" value="<?= (int)$room['max_guests'] ?>" required>
          </div>
          <div class="col-md-4">
            <label class="form-label">Status</label>
            <select name="room_status" class="form-select">
              <option value="active" <?= $room['room_status'] === 'active' ? 'selected' : '' ?>>Active</option>
              <option value="inactive" <?= $room['room_status'] === 'inactive' ? 'selected' : '' ?>>Inactive</option>
            </select>
          </div>
          <div class="col-12">
            <label class="form-label">Image file name</label>
            <input type="text" name="image" class="form-control" placeholder="turtle_bay.jpg" value="<?= htmlspecialchars($room['image']) ?>">
            <div class="form-text">Put the photo inside the <code>images/</code> folder first, then type its file name here.</div>
          </div>
        </div>
        <div class="mt-4 d-flex gap-2">
          <button type="submit" class="btn btn-coral"><?= $id ? 'Update Room' : 'Add Room' ?></button>
          <a href="rooms.php" class="btn btn-outline-secondary">Cancel</a>
        </div>
      </form>
    </div>
  </div>
</div>
<?php include '../includes/footer.php'; ?>
