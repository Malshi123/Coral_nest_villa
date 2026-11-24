<?php
session_start();
require_once 'includes/db.php';

// Gallery photos: villa hero photo(s) + every active room photo from the database
$photos = [];
foreach (['villa-hero.jpg' => 'Coral Nest Villa, Hikkaduwa', 'hero.jpg' => 'Coral Nest Villa, Hikkaduwa'] as $file => $caption) {
    if (file_exists(__DIR__ . '/images/' . $file)) {
        $photos[$file] = ['src' => 'images/' . $file, 'caption' => $caption, 'tag' => 'Villa'];
        break;
    }
}
$rows = $pdo->query("SELECT room_name, room_type, image FROM rooms WHERE room_status = 'active' ORDER BY room_id")->fetchAll();
foreach ($rows as $r) {
    if ($r['image'] !== '' && $r['image'] !== 'default.jpg' && file_exists(__DIR__ . '/images/' . $r['image'])) {
        $photos[$r['image']] = ['src' => 'images/' . $r['image'], 'caption' => $r['room_name'], 'tag' => 'Rooms'];
    }
}
// Facility photos: every picture inside images/facilities/ is added automatically.
// The caption comes from the file name, e.g. swimming-pool.jpg -> "Swimming Pool"
$fac_dir = __DIR__ . '/images/facilities/';
// Nicer captions (key = file name without numbers, lower case). Add your own lines here.
$fac_names = [
    'pool'         => 'Swimming Pool',
    'swimming pool'=> 'Swimming Pool',
    'buffet'       => 'Buffet Dining',
    'garden'       => 'Tropical Garden',
    'beach access' => 'Sunset Beach Dining',
    'beach'        => 'Beachfront',
    'restaurant'   => 'Restaurant',
    'spa'          => 'Spa & Wellness',
];
if (is_dir($fac_dir)) {
    $paths = glob($fac_dir . '*.{jpg,jpeg,png,webp,JPG,JPEG,PNG,WEBP}', GLOB_BRACE) ?: [];
    sort($paths);
    foreach ($paths as $path) {
        $file = basename($path);
        // Clean the file name: swimming_pool2.jpg -> "swimming pool" (numbers at the end are removed)
        $base = strtolower(trim(preg_replace('/[\s\-_]*\d+$/', '', preg_replace('/[-_]+/', ' ', pathinfo($file, PATHINFO_FILENAME)))));
        $name = $fac_names[$base] ?? ($base !== '' ? ucwords($base) : 'Facility');
        $photos['fac_' . $file] = ['src' => 'images/facilities/' . rawurlencode($file), 'caption' => $name, 'tag' => 'Facilities'];
    }
}
$photos = array_values($photos);

$page_title = 'Gallery | Coral Nest Villa';
include 'includes/header.php';
?>
<style>
  .gallery-item { position: relative; overflow: hidden; border-radius: 16px; cursor: pointer; box-shadow: 0 4px 14px rgba(0,0,0,.1); background: var(--teal); }
  .gallery-item img { width: 100%; height: 240px; object-fit: cover; transition: transform .4s; display: block; }
  .gallery-item:hover img { transform: scale(1.08); }
  .gallery-item .cap { position: absolute; left: 0; right: 0; bottom: 0; padding: 28px 14px 10px; color: #fff; font-size: .9rem; background: linear-gradient(transparent, rgba(0,0,0,.65)); }
  #lightbox { position: fixed; inset: 0; background: rgba(0,0,0,.9); display: none; align-items: center; justify-content: center; flex-direction: column; z-index: 2000; padding: 20px; }
  #lightbox.open { display: flex; }
  #lb-img { max-width: 92vw; max-height: 78vh; border-radius: 10px; }
  #lb-caption { color: #fff; margin-top: 12px; font-size: 1.05rem; }
  .lb-btn { position: absolute; background: rgba(255,255,255,.15); border: none; color: #fff; width: 46px; height: 46px; border-radius: 50%; font-size: 1.4rem; }
  .lb-btn:hover { background: var(--coral); }
  #lb-close { top: 18px; right: 18px; }
  #lb-prev { left: 18px; top: 50%; }
  #lb-next { right: 18px; top: 50%; }
</style>

<div class="container my-5">
  <h2 class="section-title text-center">Photo Gallery</h2>
  <p class="text-center text-muted">Click a photo to view it larger. Use the arrow keys or buttons to move between photos.</p>

  <?php if (!$photos): ?>
    <div class="alert alert-warning text-center">No photos found. Add photos to the images/ folder (room photos) or images/facilities/ (facility photos).</div>
  <?php endif; ?>

  <!-- Filter buttons (JavaScript) -->
  <div class="d-flex justify-content-center gap-2 mb-4" id="gallery-filters">
    <button class="btn btn-sm btn-coral" data-filter="all">All</button>
    <button class="btn btn-sm btn-outline-secondary" data-filter="Villa">Villa</button>
    <button class="btn btn-sm btn-outline-secondary" data-filter="Rooms">Rooms</button>
    <button class="btn btn-sm btn-outline-secondary" data-filter="Facilities">Facilities</button>
  </div>

  <div class="row g-4" id="gallery-grid">
    <?php foreach ($photos as $p): ?>
      <div class="col-sm-6 col-lg-4 gallery-col" data-tag="<?= htmlspecialchars($p['tag']) ?>">
        <div class="gallery-item" data-full="<?= htmlspecialchars($p['src']) ?>" data-caption="<?= htmlspecialchars($p['caption']) ?>">
          <img src="<?= htmlspecialchars($p['src']) ?>" alt="<?= htmlspecialchars($p['caption']) ?>" loading="lazy">
          <div class="cap"><?= htmlspecialchars($p['caption']) ?></div>
        </div>
      </div>
    <?php endforeach; ?>
  </div>
</div>

<!-- Lightbox -->
<div id="lightbox" aria-hidden="true">
  <button class="lb-btn" id="lb-close" aria-label="Close">&times;</button>
  <button class="lb-btn" id="lb-prev" aria-label="Previous">&#8249;</button>
  <img id="lb-img" src="" alt="">
  <div id="lb-caption"></div>
  <button class="lb-btn" id="lb-next" aria-label="Next">&#8250;</button>
</div>

<script src="js/gallery.js"></script>
<?php include 'includes/footer.php'; ?>
