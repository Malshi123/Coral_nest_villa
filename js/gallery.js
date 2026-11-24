// Gallery: category filter + lightbox (DOM manipulation)
(function () {
  const grid    = document.getElementById('gallery-grid');
  const box     = document.getElementById('lightbox');
  const img     = document.getElementById('lb-img');
  const caption = document.getElementById('lb-caption');
  let items = [];   // visible gallery items
  let index = 0;

  // Only the photos that are currently visible (filter applied)
  function collect() {
    items = Array.from(grid.querySelectorAll('.gallery-col'))
      .filter(col => col.style.display !== 'none')
      .map(col => col.querySelector('.gallery-item'));
  }

  function show(i) {
    index = (i + items.length) % items.length;
    const el = items[index];
    img.src = el.dataset.full;
    img.alt = el.dataset.caption;
    caption.textContent = el.dataset.caption + '  (' + (index + 1) + ' / ' + items.length + ')';
  }

  function open(el) {
    collect();
    show(items.indexOf(el));
    box.classList.add('open');
    document.body.style.overflow = 'hidden';
  }

  function close() {
    box.classList.remove('open');
    document.body.style.overflow = '';
  }

  // Open lightbox when a photo is clicked
  grid.addEventListener('click', function (e) {
    const el = e.target.closest('.gallery-item');
    if (el) open(el);
  });

  document.getElementById('lb-close').addEventListener('click', close);
  document.getElementById('lb-prev').addEventListener('click', () => show(index - 1));
  document.getElementById('lb-next').addEventListener('click', () => show(index + 1));
  box.addEventListener('click', function (e) { if (e.target === box) close(); });

  // Keyboard controls
  document.addEventListener('keydown', function (e) {
    if (!box.classList.contains('open')) return;
    if (e.key === 'Escape') close();
    if (e.key === 'ArrowLeft') show(index - 1);
    if (e.key === 'ArrowRight') show(index + 1);
  });

  // Category filter buttons
  document.getElementById('gallery-filters').addEventListener('click', function (e) {
    const btn = e.target.closest('button[data-filter]');
    if (!btn) return;
    const filter = btn.dataset.filter;
    this.querySelectorAll('button').forEach(b => {
      b.className = 'btn btn-sm ' + (b === btn ? 'btn-coral' : 'btn-outline-secondary');
    });
    grid.querySelectorAll('.gallery-col').forEach(col => {
      col.style.display = (filter === 'all' || col.dataset.tag === filter) ? '' : 'none';
    });
  });

  // Remove photos that fail to load (missing files)
  grid.querySelectorAll('.gallery-item img').forEach(function (im) {
    im.addEventListener('error', function () { im.closest('.gallery-col').remove(); });
  });
})();
