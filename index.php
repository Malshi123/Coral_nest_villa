<?php
require_once 'includes/db.php';
$page_title = 'Coral Nest Villa | Hikkaduwa';
$today = date('Y-m-d');

// ---- Hero slideshow photos (only photos that really exist are used) ----
$slides = [];
foreach (['villa-hero.jpg', 'hero.jpg'] as $f) {
    if (file_exists(__DIR__ . '/images/' . $f)) { $slides[] = 'images/' . $f; break; }
}
$homeFiles = glob(__DIR__ . '/images/home/*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [];
sort($homeFiles);
$extra = 0;
foreach ($homeFiles as $path) {
        $slides[] = 'images/home/' . rawurlencode(basename($path));
        $extra++;
}

// ---- Rooms preview (from database) ----
$rooms = $pdo->query("SELECT * FROM rooms WHERE room_status = 'active' ORDER BY room_id LIMIT 4")->fetchAll();

include 'includes/header.php';
?>
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Dancing+Script:wght@600&family=Playfair+Display:wght@600;700&display=swap">
<link rel="stylesheet" href="css/home.css">

<!-- ===== Hero ===== -->
<section class="hero-home">
  <?php if ($slides): ?>
  <div id="heroCarousel" class="carousel slide carousel-fade hero-bg" data-bs-ride="carousel" data-bs-interval="5000">
    <?php if (count($slides) > 1): ?>
    <div class="carousel-indicators">
      <?php foreach ($slides as $i => $s): ?>
        <button type="button" data-bs-target="#heroCarousel" data-bs-slide-to="<?= $i ?>" class="<?= $i === 0 ? 'active' : '' ?>" aria-label="Slide <?= $i + 1 ?>"></button>
      <?php endforeach; ?>
    </div>
    <?php endif; ?>
    <div class="carousel-inner">
      <?php foreach ($slides as $i => $s): ?>
        <div class="carousel-item <?= $i === 0 ? 'active' : '' ?>">
          <div class="hero-slide" style="background-image:url('<?= htmlspecialchars($s) ?>')"></div>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>
  <div class="hero-overlay"></div>

  <div class="container hero-content">
    <p class="hero-script">Welcome to</p>
    <h1 class="hero-title"><span>Coral</span> Nest Villa</h1>
    <p class="hero-sub">Your Perfect Getaway on the Beautiful Sri Lankan Coast</p>
    <p class="hero-tag">Snorkel with sea turtles, relax by the sea and create unforgettable memories.</p>
    <a href="rooms.php" class="btn btn-coral btn-lg"><i class="bi bi-calendar-check"></i> Book Your Stay</a>
    <a href="gallery.php" class="btn btn-ghost btn-lg ms-2"><i class="bi bi-images"></i> View Gallery</a>
  </div>
</section>

<!-- ===== Quick availability search ===== -->
<div class="container search-wrap">
  <form method="get" action="rooms.php" class="search-card" id="homeSearch">
    <div class="row g-3 align-items-end">
      <div class="col-md-3 col-6">
        <label class="form-label"><i class="bi bi-calendar-event"></i> Check-in</label>
        <input type="date" name="check_in" id="h_in" class="form-control" min="<?= $today ?>" required>
      </div>
      <div class="col-md-3 col-6">
        <label class="form-label"><i class="bi bi-calendar-event"></i> Check-out</label>
        <input type="date" name="check_out" id="h_out" class="form-control" min="<?= $today ?>" required>
      </div>
      <div class="col-md-3">
        <label class="form-label"><i class="bi bi-person"></i> Guests</label>
        <select name="guests" class="form-select">
          <?php for ($g = 1; $g <= 6; $g++): ?>
            <option value="<?= $g ?>" <?= $g === 2 ? 'selected' : '' ?>><?= $g ?> Guest<?= $g > 1 ? 's' : '' ?></option>
          <?php endfor; ?>
        </select>
      </div>
      <div class="col-md-3">
        <button type="submit" class="btn btn-coral w-100"><i class="bi bi-search"></i> Search</button>
      </div>
    </div>
  </form>
</div>

<!-- ===== Features strip ===== -->
<div class="container">
  <div class="feature-strip">
    <div class="item"><i class="bi bi-water"></i> Beachfront<br>Location</div>
    <div class="item"><i class="bi bi-shield-check"></i> 24/7 Support</div>
    <div class="item"><i class="bi bi-wifi"></i> Free Wi-Fi</div>
    <div class="item"><i class="bi bi-car-front"></i> Free Parking</div>
    <div class="item"><i class="bi bi-cup-hot"></i> Delicious Food</div>
  </div>
</div>

<!-- ===== Our Rooms ===== -->
<section class="container my-5">
  <h2 class="serif-title title-line text-center">Our Rooms</h2>
  <p class="text-center text-muted mb-4">Comfortable rooms for a perfect stay</p>
  <div class="row g-4">
    <?php foreach ($rooms as $room): ?>
      <div class="col-6 col-lg-3">
        <div class="card room-card room-mini">
          <img src="images/<?= htmlspecialchars($room['image']) ?>" class="room-thumb" alt="<?= htmlspecialchars($room['room_name']) ?>">
          <div class="card-body d-flex flex-column">
            <h6><?= htmlspecialchars($room['room_name']) ?></h6>
            <p class="small text-muted mb-2"><i class="bi bi-people"></i> Max <?= (int)$room['max_guests'] ?> guests</p>
            <div class="d-flex justify-content-between align-items-center mt-auto flex-wrap gap-2">
              <span class="price">Rs. <?= number_format($room['price_per_night']) ?> <small class="text-muted fw-normal">/ night</small></span>
              <a href="rooms.php" class="btn btn-outline-teal">View Details &rarr;</a>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
  <div class="text-center mt-4"><a href="rooms.php" class="btn btn-coral">See All Rooms</a></div>
</section>

<!-- ===== Why Coral Nest + guest comments ===== -->
<section class="container my-5">
  <div class="why-card">
    <div class="row g-4 align-items-center">
      <div class="col-lg-3">
        <h3 class="serif-title">Why <span style="color:var(--teal)">Coral Nest?</span></h3>
        <p class="small text-muted mb-3">Experience the perfect blend of comfort and natural beauty at Coral Nest Villa.</p>
        <a href="gallery.php" class="btn btn-coral btn-sm">Learn More &rarr;</a>
      </div>
      <div class="col-lg-4">
        <div class="row">
          <div class="col-4 why-icon"><i class="bi bi-gem"></i><div>Luxury<br>Accommodation</div></div>
          <div class="col-4 why-icon"><i class="bi bi-flower1"></i><div>Natural<br>Surroundings</div></div>
          <div class="col-4 why-icon"><i class="bi bi-heart-fill"></i><div>Unforgettable<br>Experience</div></div>
        </div>
      </div>
      <div class="col-lg-5 testi-col">
        <div id="testiCarousel" class="carousel slide" data-bs-ride="carousel" data-bs-interval="6000">
          <div class="carousel-inner">
            <div class="carousel-item active">
              <div class="d-flex align-items-center gap-3 px-lg-3">
                <div class="avatar">SP</div>
                <div>
                  <p class="fst-italic mb-1 small">&ldquo;A truly amazing stay! The view, the service and the atmosphere were beyond perfect.&rdquo;</p>
                  <strong class="small">Sarah Perera</strong> <span style="color:#f5a623">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                </div>
              </div>
            </div>
            <div class="carousel-item">
              <div class="d-flex align-items-center gap-3 px-lg-3">
                <div class="avatar">DF</div>
                <div>
                  <p class="fst-italic mb-1 small">&ldquo;We saw sea turtles right in front of the villa. Breakfast was delicious and the staff were so kind.&rdquo;</p>
                  <strong class="small">Daniel Fernando</strong> <span style="color:#f5a623">&#9733;&#9733;&#9733;&#9733;&#9733;</span>
                </div>
              </div>
            </div>
            <div class="carousel-item">
              <div class="d-flex align-items-center gap-3 px-lg-3">
                <div class="avatar">AK</div>
                <div>
                  <p class="fst-italic mb-1 small">&ldquo;Peaceful, clean and just steps from the beach. Booking online was quick and easy.&rdquo;</p>
                  <strong class="small">Aisha Khan</strong> <span style="color:#f5a623">&#9733;&#9733;&#9733;&#9733;&#9734;</span>
                </div>
              </div>
            </div>
          </div>
          <div class="d-flex justify-content-end gap-2 mt-2">
            <button class="testi-nav" type="button" data-bs-target="#testiCarousel" data-bs-slide="prev" aria-label="Previous"><i class="bi bi-chevron-left"></i></button>
            <button class="testi-nav" type="button" data-bs-target="#testiCarousel" data-bs-slide="next" aria-label="Next"><i class="bi bi-chevron-right"></i></button>
          </div>
        </div>
        <p class="text-muted text-end mb-0" style="font-size:.7rem;">Sample guest comments (fictional)</p>
      </div>
    </div>
  </div>
</section>

<!-- ===== Location map ===== -->
<section class="container my-5">
  <h2 class="serif-title title-line text-center mb-4">Find Us in Hikkaduwa</h2>
  <div class="row g-4 align-items-center">
    <div class="col-lg-4">
      <p class="mb-2"><i class="bi bi-geo-alt"></i> Galle Road, Hikkaduwa, Sri Lanka</p>
      <p class="mb-2"><i class="bi bi-telephone"></i> 077 123 4567</p>
      <p class="mb-2"><i class="bi bi-envelope"></i> info@coralnest.lk</p>
      <p class="small text-muted mb-0">Just steps from Hikkaduwa beach, famous for coral reefs and sea turtles.</p>
    </div>
    <div class="col-lg-8">
      <div class="map-wrap">
        <iframe src="https://maps.google.com/maps?q=Hikkaduwa+Beach,+Sri+Lanka&z=14&output=embed"
                width="100%" height="380" style="border:0;" allowfullscreen loading="lazy"
                referrerpolicy="no-referrer-when-downgrade" title="Coral Nest Villa location"></iframe>
      </div>
    </div>
  </div>
</section>

<!-- Wave divider above footer -->
<svg class="wave-divider" viewBox="0 0 1440 80" preserveAspectRatio="none" aria-hidden="true">
  <path d="M0,40 C240,90 480,0 720,30 C960,60 1200,90 1440,30 L1440,80 L0,80 Z" fill="#095a62"/>
</svg>

<script>
// Search card: prevent invalid date ranges
const hIn = document.getElementById('h_in');
const hOut = document.getElementById('h_out');
hIn.addEventListener('change', function () {
  if (!hIn.value) return;
  const d = new Date(hIn.value + 'T00:00:00');
  d.setDate(d.getDate() + 1);
  const next = d.getFullYear() + '-' + String(d.getMonth() + 1).padStart(2, '0') + '-' + String(d.getDate()).padStart(2, '0');
  hOut.min = next;
  if (hOut.value && hOut.value < next) hOut.value = '';
});
</script>
<?php include 'includes/footer.php'; ?>
