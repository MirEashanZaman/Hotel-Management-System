<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Welcome to Mir Eashan Zaman Hotel</title>
  <link rel="stylesheet" href="css/style.css">
  <style>
    .public-navbar {
      height: 70px;
      background: var(--dark2);
      border-bottom: 1px solid var(--border);
      display: flex;
      align-items: center;
      justify-content: space-between;
      padding: 0 40px;
      position: sticky;
      top: 0;
      z-index: 100;
    }

    .public-logo {
      display: flex;
      align-items: center;
      gap: 12px;
      text-decoration: none;
      color: var(--text);
    }

    .public-nav-links {
      display: flex;
      gap: 24px;
      align-items: center;
    }

    .public-nav-links a {
      color: var(--text2);
      text-decoration: none;
      font-size: 13px;
      letter-spacing: 1px;
      transition: color 0.2s;
    }

    .public-nav-links a:hover {
      color: var(--gold);
    }

    .hero-banner {
      background: linear-gradient(135deg, var(--dark3) 0%, var(--dark2) 100%);
      border: 1px solid var(--border);
      padding: 80px 40px;
      text-align: center;
      margin-bottom: 40px;
      position: relative;
      overflow: hidden;
    }

    .hero-blur {
      position: absolute;
      top: -100px;
      right: -100px;
      width: 300px;
      height: 300px;
      border-radius: 50%;
      background: var(--gold-dim);
      filter: blur(80px);
      pointer-events: none;
    }

    .home-section-title {
      font-family: 'Cormorant Garamond', serif;
      font-size: 28px;
      color: var(--gold);
      margin-bottom: 24px;
      text-align: center;
      letter-spacing: 1px;
      font-weight: 300;
    }
  </style>
</head>

<body>
  <nav class="public-navbar">
    <a href="index.php" class="public-logo">
      <div class="logo-icon"><span>H</span></div>
      <div class="logo-text">
        <h2 style="font-size: 14px; margin: 0;">Mir Eashan Zaman</h2>
        <p style="font-size: 8px; margin: 0;">Luxury Hotel</p>
      </div>
    </a>
    <div class="public-nav-links">
      <a href="#rooms">Rooms</a>
      <a href="#amenities">Amenities</a>
      <a href="#reviews">Reviews</a>
      <a href="index.php?route=login" class="btn btn-ghost btn-sm" style="border-radius: 0;">Sign In</a>
      <a href="index.php?route=signup" class="btn btn-primary btn-sm" style="border-radius: 0;">Register</a>
    </div>
  </nav>

  <main style="max-width: 1200px; margin: 40px auto; padding: 0 20px;">
    <!-- Hero Banner -->
    <div class="hero-banner">
      <div class="hero-blur"></div>
      <div style="font-size: 11px; color: var(--gold); letter-spacing: 3px; font-weight: bold; text-transform: uppercase; margin-bottom: 20px;">Premium Luxury Retreat</div>
      <h1 style="font-family: 'Cormorant Garamond', serif; font-size: 48px; font-weight: 300; line-height: 1.2; margin-bottom: 20px; color: var(--text); letter-spacing: 1.2px;">Prestige, Comfort, & Elegance</h1>
      <p style="color: var(--text2); font-size: 14px; max-width: 700px; margin: 0 auto 32px; line-height: 1.8;">
        Indulge in a sophisticated stay designed to exceed expectations. Discover our range of bespoke suite layouts, curated dining options, and personal concierge services.
      </p>
      <a href="index.php?route=login" class="btn btn-primary" style="padding: 12px 32px; text-decoration: none;">Book Your Stay Now</a>
    </div>

    <!-- Stats Section -->
    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 20px; margin-bottom: 50px;">
      <div class="card" style="text-align: center; margin-bottom: 0; padding: 28px 16px;">
        <div style="font-size: 36px; color: var(--gold); font-family: 'Cormorant Garamond', serif; font-weight: bold;">15+</div>
        <div style="font-size: 10px; color: var(--text-muted); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 6px;">Years of Excellence</div>
      </div>
      <div class="card" style="text-align: center; margin-bottom: 0; padding: 28px 16px;">
        <div style="font-size: 36px; color: var(--gold); font-family: 'Cormorant Garamond', serif; font-weight: bold;">5 Star</div>
        <div style="font-size: 10px; color: var(--text-muted); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 6px;">Hospitality Rating</div>
      </div>
      <div class="card" style="text-align: center; margin-bottom: 0; padding: 28px 16px;">
        <div style="font-size: 36px; color: var(--gold); font-family: 'Cormorant Garamond', serif; font-weight: bold;">12,000+</div>
        <div style="font-size: 10px; color: var(--text-muted); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 6px;">Happy Guest Stays</div>
      </div>
      <div class="card" style="text-align: center; margin-bottom: 0; padding: 28px 16px;">
        <div style="font-size: 36px; color: var(--gold); font-family: 'Cormorant Garamond', serif; font-weight: bold;">24/7</div>
        <div style="font-size: 10px; color: var(--text-muted); letter-spacing: 1.5px; text-transform: uppercase; margin-top: 6px;">Concierge Care</div>
      </div>
    </div>

    <!-- Rooms Section -->
    <section id="rooms" style="margin-bottom: 60px;">
      <h3 class="home-section-title">Our Luxurious Accommodations</h3>
      <div id="publicRoomsGrid" style="display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 20px;">
        <!-- Loaded via JavaScript -->
        <div style="grid-column: 1 / -1; text-align: center; padding: 40px; color: var(--text-muted);">Loading rooms...</div>
      </div>
    </section>

    <!-- Amenities Section -->
    <section id="amenities" class="card" style="margin-bottom: 60px; padding: 40px;">
      <h3 class="home-section-title" style="margin-top: 0;">Featured Guest Experiences</h3>
      <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 32px;">
        <div style="display: flex; gap: 16px; align-items: flex-start;">
          <div style="font-size: 28px; color: var(--gold);">🍽️</div>
          <div>
            <h4 style="font-weight: 500; font-size: 15px; margin-bottom: 6px; color: var(--text);">Culinary Excellence</h4>
            <p style="font-size: 12px; color: var(--text2); line-height: 1.6;">Indulge in a sensory dining adventure prepared with top locally sourced organic ingredients.</p>
          </div>
        </div>
        <div style="display: flex; gap: 16px; align-items: flex-start;">
          <div style="font-size: 28px; color: var(--gold);">💆‍♀️</div>
          <div>
            <h4 style="font-weight: 500; font-size: 15px; margin-bottom: 6px; color: var(--text);">Holistic Wellness</h4>
            <p style="font-size: 12px; color: var(--text2); line-height: 1.6;">Recharge at our signature spa containing hot stones, sauna circuits, and massage lounges.</p>
          </div>
        </div>
        <div style="display: flex; gap: 16px; align-items: flex-start;">
          <div style="font-size: 28px; color: var(--gold);">🏊‍♂️</div>
          <div>
            <h4 style="font-weight: 500; font-size: 15px; margin-bottom: 6px; color: var(--text);">Panoramic Pool</h4>
            <p style="font-size: 12px; color: var(--text2); line-height: 1.6;">Relax at the rooftop deck with full skyline view, private luxury cabanas, and fresh juice bar.</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Reviews Section -->
    <section id="reviews" style="margin-bottom: 60px;">
      <h3 class="home-section-title">What Guests Say</h3>
      <div id="publicReviewsGrid" class="card" style="padding: 24px;">
        <!-- Loaded via JavaScript -->
        <div style="text-align: center; padding: 20px; color: var(--text-muted);">Loading reviews...</div>
      </div>
    </section>
  </main>

  <footer style="border-top: 1px solid var(--border); padding: 40px 20px; text-align: center; background: var(--dark2); color: var(--text-muted); font-size: 12px;">
    <p style="margin-bottom: 8px;">© 2026 Mir Eashan Zaman Hotel Group. All rights reserved.</p>
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
            <div class="card" style="margin-bottom:0; padding:0; overflow:hidden; border-color:var(--border);">
              ${r.image_url ? `<div style="height:170px; width:100%; overflow:hidden;"><img src="${escapeHtml(r.image_url)}" style="width:100%; height:100%; object-fit:cover;"></div>` : ''}
              <div style="padding:20px;">
                <div style="font-family:'Cormorant Garamond',serif; font-size:22px; color:var(--text)">Room ${escapeHtml(r.room_number)}</div>
                <div style="font-size:11px; color:var(--text-muted); margin-bottom:12px;">Floor ${escapeHtml(r.floor)} · ${escapeHtml(r.room_type)}</div>
                <p style="font-size:12px; color:var(--text2); margin-bottom:16px; line-height:1.6; min-height:50px;">${escapeHtml(r.description || '')}</p>
                <div style="display:flex; justify-content:space-between; align-items:center;">
                  <span style="font-size:18px; font-family:'Cormorant Garamond',serif; color:var(--gold);">৳${Number(r.price_per_night).toLocaleString()}<span style="font-size:11px; color:var(--text-muted);">/night</span></span>
                  <a href="index.php?route=login" class="btn btn-primary btn-sm" style="text-decoration:none;">Book Now</a>
                </div>
              </div>
            </div>
          `).join('');
        } else {
          roomsGrid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 20px; color: var(--text-muted);">No rooms found.</div>';
        }
      } catch (e) {
        document.getElementById('publicRoomsGrid').innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 20px; color: var(--red);">Error loading hotel suites.</div>';
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
              `<span style="color:${n <= r.rating ? '#c9a84c' : 'var(--border)'}; font-size:16px;">★</span>`
            ).join('');
            return `
              <div style="border-bottom:1px solid var(--border2); padding:16px 0;">
                <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:6px;">
                  <div>
                    <strong style="color:var(--text); font-size:13px;">${escapeHtml(r.customer_name || 'Guest')}</strong>
                    <span style="color:var(--text-muted); font-size:11px; margin-left:8px;">Room ${escapeHtml(r.room_number || '—')}</span>
                  </div>
                  <div>${stars}</div>
                </div>
                <p style="font-size:12px; color:var(--text2); line-height:1.6; margin:0;">${escapeHtml(r.comment)}</p>
              </div>
            `;
          }).join('');
        } else {
          reviewsGrid.innerHTML = '<div style="text-align: center; padding: 20px; color: var(--text-muted);">No reviews posted yet.</div>';
        }
      } catch (e) {
        document.getElementById('publicReviewsGrid').innerHTML = '<div style="text-align: center; padding: 20px; color: var(--red);">Error loading guest reviews.</div>';
      }
    }

    loadPublicHome();
  </script>
</body>

</html>
