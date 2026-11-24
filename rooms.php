<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// ---- Read search + filter values ----
$check_in  = $_GET['check_in']  ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests    = (int)($_GET['guests'] ?? 0);
$type      = trim($_GET['type'] ?? '');
$max_price = $_GET['max_price'] ?? '';
$sort      = $_GET['sort'] ?? 'price_asc';
$today     = date('Y-m-d');
$error     = '';
$searched  = false;
$nights    = 0;

// Room types for the filter drop-down
$types = $pdo->query("SELECT DISTINCT room_type FROM rooms WHERE room_status = 'active' ORDER BY room_type")->fetchAll(PDO::FETCH_COLUMN);

$sql    = "SELECT * FROM rooms r WHERE r.room_status = 'active'";
$params = [];

// Filter: number of guests
if ($guests > 0) {
    $sql .= " AND r.max_guests >= ?";
    $params[] = $guests;
}

// Filter: room type
if ($type !== '' && in_array($type, $types, true)) {
    $sql .= " AND r.room_type = ?";
    $params[] = $type;
} else {
    $type = '';
}

// Filter: maximum price per night
if ($max_price !== '' && is_numeric($max_price) && $max_price > 0) {
    $sql .= " AND r.price_per_night <= ?";
    $params[] = $max_price;
} else {
    $max_price = '';
}

// Availability search (dates)
if ($check_in !== '' || $check_out !== '') {
    if (!valid_date($check_in) || !valid_date($check_out)) {
        $error = 'Please select valid check-in and check-out dates.';
    } elseif ($check_in < $today) {
        $error = 'Check-in date cannot be in the past.';
    } elseif ($check_out <= $check_in) {
        $error = 'Check-out date must be after the check-in date.';
    } else {
        $searched = true;
        $nights = count_nights($check_in, $check_out);
        // Hide rooms that have an overlapping CONFIRMED booking
        $sql .= " AND NOT EXISTS (
                    SELECT 1 FROM bookings b
                    WHERE b.room_id = r.room_id AND b.booking_status = 'Confirmed'
                      AND b.check_in < ? AND b.check_out > ?)";
        $params[] = $check_out;
        $params[] = $check_in;
    }
}

// Sorting (whitelist so user input is never put into SQL)
$sorts = [
    'price_asc'  => 'r.price_per_night ASC',
    'price_desc' => 'r.price_per_night DESC',
    'name'       => 'r.room_name ASC',
];
if (!isset($sorts[$sort])) $sort = 'price_asc';
$sql .= ' ORDER BY ' . $sorts[$sort];

$stmt = $pdo->prepare($sql);
$stmt->execute($params);
$rooms = $stmt->fetchAll();

$page_title = 'Rooms | Coral Nest Villa';
include 'includes/header.php';
?>
<div class="container my-5">
  <h2 class="section-title text-center">Rooms &amp; Accommodation</h2>

  <!-- Availability search + filters -->
  <form method="get" action="rooms.php" id="searchForm" class="quick-search-card mb-4">
    <div class="row g-3 align-items-end">
      <div class="col-md-3">
        <label class="form-label fw-semibold">Check-in</label>
        <input type="date" name="check_in" id="s_check_in" class="form-control" min="<?= $today ?>" value="<?= htmlspecialchars($check_in) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Check-out</label>
        <input type="date" name="check_out" id="s_check_out" class="form-control" min="<?= $today ?>" value="<?= htmlspecialchars($check_out) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Guests</label>
        <select name="guests" class="form-select">
          <?php for ($g = 1; $g <= 6; $g++): ?>
            <option value="<?= $g ?>" <?= $guests === $g ? 'selected' : '' ?>><?= $g ?> Guest<?= $g > 1 ? 's' : '' ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Room type</label>
        <select name="type" class="form-select">
          <option value="">All types</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= htmlspecialchars($t) ?>" <?= $type === $t ? 'selected' : '' ?>><?= htmlspecialchars($t) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Max price / night (Rs.)</label>
        <input type="number" name="max_price" min="1" step="500" class="form-control" placeholder="e.g. 20000" value="<?= htmlspecialchars($max_price) ?>">
      </div>
      <div class="col-md-3">
        <label class="form-label fw-semibold">Sort by</label>
        <select name="sort" class="form-select">
          <option value="price_asc"  <?= $sort === 'price_asc'  ? 'selected' : '' ?>>Price: low to high</option>
          <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: high to low</option>
          <option value="name"       <?= $sort === 'name'       ? 'selected' : '' ?>>Name (A-Z)</option>
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-coral w-100">Search / Filter</button>
      </div>
      <div class="col-md-3">
        <a href="rooms.php" class="btn btn-outline-secondary w-100">Reset</a>
      </div>
    </div>
  </form>

  <?php if ($error): ?>
    <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
  <?php endif; ?>

  <?php if ($searched): ?>
    <div class="alert alert-info">
      Showing rooms available from <strong><?= htmlspecialchars($check_in) ?></strong>
      to <strong><?= htmlspecialchars($check_out) ?></strong> (<?= $nights ?> night<?= $nights > 1 ? 's' : '' ?>).
    </div>
  <?php endif; ?>

  <p class="text-muted small"><?= count($rooms) ?> room<?= count($rooms) !== 1 ? 's' : '' ?> found</p>

  <?php if (!$rooms): ?>
    <div class="alert alert-warning text-center">No rooms match your search. Try different dates, fewer guests or a higher price.</div>
  <?php endif; ?>

  <div class="row g-4">
    <?php foreach ($rooms as $room):
        $link = 'booking.php?room_id=' . (int)$room['room_id'];
        if ($searched) {
            $link .= '&' . http_build_query(['check_in' => $check_in, 'check_out' => $check_out, 'guests' => max($guests, 1)]);
        }
    ?>
      <div class="col-md-6 col-lg-4">
        <div class="card room-card">
          <img src="images/<?= htmlspecialchars($room['image']) ?>" class="room-thumb" alt="<?= htmlspecialchars($room['room_name']) ?>">
          <div class="card-body d-flex flex-column">
            <h5><?= htmlspecialchars($room['room_name']) ?>
              <span class="badge bg-secondary fw-normal"><?= htmlspecialchars($room['room_type']) ?></span></h5>
            <p class="small mb-2"><?= htmlspecialchars($room['description']) ?></p>
            <p class="small mb-1"><i class="bi bi-check2-circle"></i> <?= htmlspecialchars($room['facilities']) ?></p>
            <p class="mb-2"><i class="bi bi-people"></i> Max <?= (int)$room['max_guests'] ?> guests</p>
            <p class="price mb-1">Rs. <?= number_format($room['price_per_night']) ?> / night</p>
            <?php if ($searched): ?>
              <p class="small text-muted">
                <?= $nights ?> night<?= $nights > 1 ? 's' : '' ?>:
                <strong>Rs. <?= number_format(calc_total($nights, $room['price_per_night'])) ?></strong>
                <?= $nights >= DISCOUNT_MIN_NIGHTS ? '(10% discount applied)' : '' ?>
              </p>
            <?php endif; ?>
            <a href="<?= $link ?>" class="btn btn-coral mt-auto">Book This Room</a>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>
<script>
// Prevent invalid date ranges on the search form
const sIn = document.getElementById('s_check_in');
const sOut = document.getElementById('s_check_out');
sIn.addEventListener('change', function () {
  if (!sIn.value) return;
  const d = new Date(sIn.value + 'T00:00:00');
  d.setDate(d.getDate() + 1);
  const next = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  sOut.min = next;
  if (sOut.value && sOut.value < next) sOut.value = '';
});
document.getElementById('searchForm').addEventListener('submit', function (e) {
  if ((sIn.value && !sOut.value) || (!sIn.value && sOut.value)) {
    e.preventDefault();
    alert('Please select both check-in and check-out dates.');
  }
});
</script>
<?php include 'includes/footer.php'; ?>
