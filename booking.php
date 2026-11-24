<?php
session_start();
require_once 'includes/db.php';
require_once 'includes/functions.php';

// Only logged-in guests can book
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php?need_login=1');
    exit;
}

// Load the selected room
$room_id = (int)($_GET['room_id'] ?? $_POST['room_id'] ?? 0);
$stmt = $pdo->prepare("SELECT * FROM rooms WHERE room_id = ? AND room_status = 'active'");
$stmt->execute([$room_id]);
$room = $stmt->fetch();
if (!$room) {
    header('Location: rooms.php');
    exit;
}

// Guest details (shown read-only on the form)
$stmt = $pdo->prepare('SELECT name, email, contact_number FROM users WHERE user_id = ?');
$stmt->execute([$_SESSION['user_id']]);
$guest = $stmt->fetch();

$today     = date('Y-m-d');
$errors    = [];
$check_in  = $_POST['check_in']  ?? ($_GET['check_in']  ?? '');
$check_out = $_POST['check_out'] ?? ($_GET['check_out'] ?? '');
$guests    = (int)($_POST['guests'] ?? ($_GET['guests'] ?? 1));
if ($guests < 1) $guests = 1;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Server-side validation
    if (!valid_date($check_in) || !valid_date($check_out)) {
        $errors[] = 'Please select valid check-in and check-out dates.';
    } else {
        if ($check_in < $today)       $errors[] = 'Check-in date cannot be in the past.';
        if ($check_out <= $check_in)  $errors[] = 'Check-out date must be after the check-in date.';
    }
    if ($guests < 1 || $guests > $room['max_guests']) {
        $errors[] = 'This room allows 1 to ' . (int)$room['max_guests'] . ' guests.';
    }

    // Availability check against CONFIRMED bookings
    if (!$errors && !is_room_available($pdo, $room_id, $check_in, $check_out)) {
        $errors[] = 'Sorry, this room is not available for the selected dates.';
    }

    // Save booking (price calculated on the server)
    if (!$errors) {
        $nights = count_nights($check_in, $check_out);
        $total  = calc_total($nights, $room['price_per_night']);
        $stmt = $pdo->prepare(
            "INSERT INTO bookings (user_id, room_id, check_in, check_out, guests, nights, total_amount, booking_status)
             VALUES (?, ?, ?, ?, ?, ?, ?, 'Pending')"
        );
        $stmt->execute([$_SESSION['user_id'], $room_id, $check_in, $check_out, $guests, $nights, $total]);
        header('Location: my_bookings.php?booked=1');
        exit;
    }
}

$page_title = 'Book ' . $room['room_name'] . ' | Coral Nest Villa';
include 'includes/header.php';
?>
<div class="container my-5">
  <h2 class="section-title text-center">Book Your Stay</h2>
  <div class="row g-4">

    <!-- Room summary -->
    <div class="col-lg-5">
      <div class="card room-card">
        <img src="images/<?= htmlspecialchars($room['image']) ?>" class="room-thumb" alt="<?= htmlspecialchars($room['room_name']) ?>">
        <div class="card-body">
          <h5><?= htmlspecialchars($room['room_name']) ?></h5>
          <p class="small"><?= htmlspecialchars($room['description']) ?></p>
          <p class="small mb-1"><i class="bi bi-check2-circle"></i> <?= htmlspecialchars($room['facilities']) ?></p>
          <p class="mb-1"><i class="bi bi-people"></i> Max <?= (int)$room['max_guests'] ?> guests</p>
          <p class="price mb-0">Rs. <?= number_format($room['price_per_night']) ?> / night</p>
        </div>
      </div>
    </div>

    <!-- Booking form -->
    <div class="col-lg-7">
      <div class="card shadow-sm border-0 rounded-4">
        <div class="card-body p-4">

          <?php if ($errors): ?>
            <div class="alert alert-danger"><ul class="mb-0">
              <?php foreach ($errors as $e): ?><li><?= htmlspecialchars($e) ?></li><?php endforeach; ?>
            </ul></div>
          <?php endif; ?>
          <div id="js-errors" class="alert alert-danger d-none"></div>

          <form method="post" id="bookingForm" novalidate
                data-price="<?= (float)$room['price_per_night'] ?>"
                data-max="<?= (int)$room['max_guests'] ?>">
            <input type="hidden" name="room_id" value="<?= $room_id ?>">

            <div class="row g-3">
              <div class="col-md-6">
                <label class="form-label">Guest Name</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($guest['name']) ?>" readonly>
              </div>
              <div class="col-md-6">
                <label class="form-label">Contact Number</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($guest['contact_number']) ?>" readonly>
              </div>
              <div class="col-12">
                <label class="form-label">Email</label>
                <input type="text" class="form-control" value="<?= htmlspecialchars($guest['email']) ?>" readonly>
              </div>
              <div class="col-md-4">
                <label class="form-label">Check-in</label>
                <input type="date" name="check_in" id="check_in" class="form-control" min="<?= $today ?>" value="<?= htmlspecialchars($check_in) ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Check-out</label>
                <input type="date" name="check_out" id="check_out" class="form-control" min="<?= $today ?>" value="<?= htmlspecialchars($check_out) ?>">
              </div>
              <div class="col-md-4">
                <label class="form-label">Guests</label>
                <input type="number" name="guests" id="guests" class="form-control" min="1" max="<?= (int)$room['max_guests'] ?>" value="<?= $guests ?>">
              </div>
            </div>

            <!-- Price summary (filled by JavaScript) -->
            <div id="price-summary" class="alert alert-secondary mt-4 mb-3">
              Select your dates to see the total cost.
            </div>

            <button type="submit" class="btn btn-coral w-100">Confirm Booking Request</button>
            <p class="small text-muted mt-2 mb-0">Your booking will be <strong>Pending</strong> until the villa confirms it.</p>
          </form>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="js/booking.js"></script>
<?php include 'includes/footer.php'; ?>
