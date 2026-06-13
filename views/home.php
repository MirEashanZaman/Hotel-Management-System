<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to Mir Eashan Zaman Hotel</title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/home.css">
</head>

<body>
  <nav class="public-navbar">
    <a href="index.php" class="public-logo">
      <div class="logo-icon"><span>H</span></div>
      <div class="logo-text">
        <h2 class="public-logo-title">Mir Eashan Zaman</h2>
        <p class="public-logo-subtitle">Luxury Hotel</p>
      </div>
    </a>
    <div class="public-nav-links">
      <a href="#rooms">Rooms</a>
      <a href="#amenities">Amenities</a>
      <a href="#reviews">Reviews</a>
      <a href="index.php?route=login" class="btn btn-ghost btn-sm public-nav-btn">Sign In</a>
      <a href="index.php?route=signup" class="btn btn-primary btn-sm public-nav-btn">Register</a>
    </div>
  </nav>

  <main class="home-main">
    <!-- Hero Banner -->
    <div class="hero-banner">
      <div class="hero-blur"></div>
      <div class="hero-tag">Premium Luxury Retreat</div>
      <h1 class="hero-heading">Prestige, Comfort, &amp; Elegance</h1>
      <p class="hero-desc">
        Indulge in a sophisticated stay designed to exceed expectations. Discover our range of bespoke suite layouts, curated dining options, and personal concierge services.
      </p>
      <a href="index.php?route=login" class="btn btn-primary hero-cta">Book Your Stay Now</a>
    </div>

    <!-- Stats Section -->
    <div class="stats-grid">
      <div class="card stat-card">
        <div class="stat-value">15+</div>
        <div class="stat-label">Years of Excellence</div>
      </div>
      <div class="card stat-card">
        <div class="stat-value">5 Star</div>
        <div class="stat-label">Hospitality Rating</div>
      </div>
      <div class="card stat-card">
        <div class="stat-value">12,000+</div>
        <div class="stat-label">Happy Guest Stays</div>
      </div>
      <div class="card stat-card">
        <div class="stat-value">24/7</div>
        <div class="stat-label">Concierge Care</div>
      </div>
    </div>

    <!-- Rooms Section -->
    <section id="rooms" class="home-rooms-section">
      <h3 class="home-section-title">Our Luxurious Accommodations</h3>
      <div id="publicRoomsGrid" class="home-rooms-grid">
        <!-- Loaded via JavaScript -->
        <div class="home-rooms-loading">Loading rooms...</div>
      </div>
    </section>

    <!-- Amenities Section -->
    <section id="amenities" class="card amenities-section">
      <h3 class="home-section-title home-section-title--flush">Featured Guest Experiences</h3>
      <div class="amenities-grid">
        <div class="amenity-item">
          <div class="amenity-icon">🍽️</div>
          <div>
            <h4 class="amenity-title">Culinary Excellence</h4>
            <p class="amenity-desc">Indulge in a sensory dining adventure prepared with top locally sourced organic ingredients.</p>
          </div>
        </div>
        <div class="amenity-item">
          <div class="amenity-icon">💆‍♀️</div>
          <div>
            <h4 class="amenity-title">Holistic Wellness</h4>
            <p class="amenity-desc">Recharge at our signature spa containing hot stones, sauna circuits, and massage lounges.</p>
          </div>
        </div>
        <div class="amenity-item">
          <div class="amenity-icon">🏊‍♂️</div>
          <div>
            <h4 class="amenity-title">Panoramic Pool</h4>
            <p class="amenity-desc">Relax at the rooftop deck with full skyline view, private luxury cabanas, and fresh juice bar.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Reviews Section -->
    <section id="reviews" class="home-reviews-section">
      <h3 class="home-section-title">What Guests Say</h3>
      <div id="publicReviewsGrid" class="card home-reviews-box">
        <!-- Loaded via JavaScript -->
        <div class="home-reviews-loading">Loading reviews...</div>
      </div>
    </section>
  </main>

  <footer class="home-footer">
    <p>© 2026 Mir Eashan Zaman Hotel Group. All rights reserved.</p>
    <p>12 prestige Boulevard, Grand Capital Sector · Booking Hotline: +880-12345678</p>
  </footer>

  <script>
    function escapeHtml(str) {
      if (str === null || str === undefined) return '';
      return String(str)
        .replace(/&/g, "&amp;")
        .replace(/</g, "&lt;")
        .replace(/>/g, "&gt;")
        .replace(/"/g, "&quot;")
        .replace(/'/g, "&#039;");
    }

    async function loadPublicHome() {
      // Fetch Rooms
      try {
        const roomsRes = await fetch('api/rooms.php');
        const rooms = await roomsRes.json();
        const roomsGrid = document.getElementById('publicRoomsGrid');
        
        if (Array.isArray(rooms) && rooms.length > 0) {
          const featured = rooms.slice(0, 6);
          roomsGrid.innerHTML = featured.map(r => `
            <div class="card room-card">
              ${r.image_url ? `<div class="room-card-img"><img src="${escapeHtml(r.image_url)}" alt="Room ${escapeHtml(r.room_number)}"></div>` : ''}
              <div class="room-card-body">
                <div class="room-card-title">Room ${escapeHtml(r.room_number)}</div>
                <div class="room-card-meta">Floor ${escapeHtml(r.floor)} · ${escapeHtml(r.room_type)}</div>
                <p class="room-card-desc">${escapeHtml(r.description || '')}</p>
                <div class="room-card-footer">
                  <span class="room-card-price">৳${Number(r.price_per_night).toLocaleString()}<span class="room-card-price-unit">/night</span></span>
                  <a href="index.php?route=login" class="btn btn-primary btn-sm">Book Now</a>
                </div>
              </div>
            </div>
          `).join('');
        } else {
          roomsGrid.innerHTML = '<div class="grid-msg-full">No rooms found.</div>';
        }
      } catch (e) {
        document.getElementById('publicRoomsGrid').innerHTML = '<div class="grid-msg-error">Error loading hotel suites.</div>';
      }

      // Fetch Reviews
      try {
        const reviewsRes = await fetch('api/reviews.php');
        const reviews = await reviewsRes.json();
        const reviewsGrid = document.getElementById('publicReviewsGrid');
        
        if (Array.isArray(reviews) && reviews.length > 0) {
          const highlights = reviews.slice(0, 4);
          reviewsGrid.innerHTML = highlights.map(r => {
            const stars = [1, 2, 3, 4, 5].map(n =>
              `<span class="${n <= r.rating ? 'star-on' : 'star-off'}">★</span>`
            ).join('');
            return `
              <div class="review-item">
                <div class="review-header">
                  <div>
                    <strong class="review-author">${escapeHtml(r.customer_name || 'Guest')}</strong>
                    <span class="review-room">Room ${escapeHtml(r.room_number || '—')}</span>
                  </div>
                  <div>${stars}</div>
                </div>
                <p class="review-comment">${escapeHtml(r.comment)}</p>
              </div>
            `;
          }).join('');
        } else {
          reviewsGrid.innerHTML = '<div class="reviews-msg-empty">No reviews posted yet.</div>';
        }
      } catch (e) {
        document.getElementById('publicReviewsGrid').innerHTML = '<div class="reviews-msg-error">Error loading guest reviews.</div>';
      }
    }

    loadPublicHome();
  </script>
</body>

</html>
