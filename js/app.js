let CURRENT_USER = null;

function escapeHtml(str) {
  if (str === null || str === undefined) return '';
  return String(str)
    .replace(/&/g, "&amp;")
    .replace(/</g, "&lt;")
    .replace(/>/g, "&gt;")
    .replace(/"/g, "&quot;")
    .replace(/'/g, "&#039;");
}

const icons = {
  home: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><polyline points="9,22 9,12 15,12 15,22"/></svg>`,
  dashboard: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/></svg>`,
  rooms: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M9 3v18M15 3v18M3 9h18M3 15h18"/></svg>`,
  bookings: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>`,
  payments: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="1" y="4" width="22" height="16" rx="2"/><line x1="1" y1="10" x2="23" y2="10"/></svg>`,
  services: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="3"/><path d="M12 1v4M12 19v4M4.22 4.22l2.83 2.83M16.95 16.95l2.83 2.83M1 12h4M19 12h4M4.22 19.78l2.83-2.83M16.95 7.05l2.83-2.83"/></svg>`,
  users: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>`,
  logs: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14,2 14,8 20,8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/><polyline points="10,9 9,9 8,9"/></svg>`,
  profile: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>`,
  reviews: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12,2 15.09,8.26 22,9.27 17,14.14 18.18,21.02 12,17.77 5.82,21.02 7,14.14 2,9.27 8.91,8.26"/></svg>`,
  timeline: `<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="9" y1="3" x2="9" y2="21"/><line x1="15" y1="3" x2="15" y2="21"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="3" y1="15" x2="21" y2="15"/></svg>`,
};

async function init() {
  try {
    const res = await fetch('api/session.php');
    const data = await res.json();
    if (!data.loggedIn) {
      window.location.href = 'index.php?route=login';
      return;
    }
    CURRENT_USER = data.user;
    window._csrfToken = data.csrf_token;
    renderSidebar();
    updateClock();
    setInterval(updateClock, 1000);
    navigate('dashboard');
  } catch (e) {
    console.error('Init error:', e);
    document.getElementById('pageContent').innerHTML =
      '<div class="alert alert-error" style="margin:40px">Failed to connect to server. Make sure XAMPP Apache & MySQL are running.<br><br>Error: ' + e.message + '</div>';
  }
}

function updateClock() {
  const el = document.getElementById('topbarTime');
  if (el) el.textContent = new Date().toLocaleString('en-GB', { weekday: 'short', day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

const NAV_CONFIG = {
  admin: [
    { section: 'Overview' },
    { id: 'dashboard', label: 'Dashboard', icon: icons.dashboard },
    { section: 'Operations' },
    { id: 'rooms', label: 'Rooms', icon: icons.rooms },
    { id: 'timeline', label: 'Timeline View', icon: icons.timeline },
    { id: 'bookings', label: 'Bookings', icon: icons.bookings },
    { id: 'payments', label: 'Payments', icon: icons.payments },
    { id: 'services', label: 'Services', icon: icons.services },
    { section: 'Users' },
    { id: 'users', label: 'All Users', icon: icons.users },
    { section: 'System' },
    { id: 'logs', label: 'Activity Logs', icon: icons.logs },
    { id: 'reviews', label: 'Reviews', icon: icons.reviews },
    { id: 'profile', label: 'My Profile', icon: icons.profile },
  ],
  staff: [
    { section: 'Overview' },
    { id: 'dashboard', label: 'Dashboard', icon: icons.dashboard },
    { section: 'Operations' },
    { id: 'rooms', label: 'Rooms', icon: icons.rooms },
    { id: 'timeline', label: 'Timeline View', icon: icons.timeline },
    { id: 'bookings', label: 'Bookings', icon: icons.bookings },
    { id: 'payments', label: 'Payments', icon: icons.payments },
    { id: 'services', label: 'Services', icon: icons.services },
    { section: 'Customers' },
    { id: 'users', label: 'Customers', icon: icons.users },
    { id: 'reviews', label: 'Reviews', icon: icons.reviews },
    { section: 'Account' },
    { id: 'profile', label: 'My Profile', icon: icons.profile },
  ],
  customer: [
    { section: 'Overview' },
    { id: 'dashboard', label: 'Dashboard', icon: icons.dashboard },
    { section: 'My Stay' },
    { id: 'rooms', label: 'Browse Rooms', icon: icons.rooms },
    { id: 'bookings', label: 'My Bookings', icon: icons.bookings },
    { id: 'payments', label: 'My Payments', icon: icons.payments },
    { id: 'services', label: 'Request Service', icon: icons.services },
    { id: 'reviews', label: 'My Reviews', icon: icons.reviews },
    { section: 'Account' },
    { id: 'profile', label: 'My Profile', icon: icons.profile },
  ]
};

function renderSidebar() {
  const nav = document.getElementById('sidebarNav');
  const role = CURRENT_USER.role;
  const items = NAV_CONFIG[role] || [];

  const avatarEl = document.getElementById('sidebarAvatar');
  if (CURRENT_USER.avatar_url) {
    avatarEl.innerHTML = `<img src="${CURRENT_USER.avatar_url}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
    avatarEl.style.padding = '0';
  } else {
    avatarEl.textContent = CURRENT_USER.name[0].toUpperCase();
    avatarEl.style.padding = '';
  }
  document.getElementById('sidebarName').textContent = CURRENT_USER.name;
  document.getElementById('sidebarRole').textContent = role.charAt(0).toUpperCase() + role.slice(1);

  nav.innerHTML = items.map(item => {
    if (item.section) return `<div class="nav-section">${item.section}</div>`;
    return `<a class="nav-item" id="nav-${item.id}" onclick="navigate('${item.id}')">
      ${item.icon} <span>${item.label}</span>
    </a>`;
  }).join('');
}

function setActiveNav(id) {
  document.querySelectorAll('.nav-item').forEach(el => el.classList.remove('active'));
  const el = document.getElementById(`nav-${id}`);
  if (el) el.classList.add('active');
}

async function navigate(page) {
  setActiveNav(page);
  document.getElementById('pageTitle').textContent = {
    dashboard: 'Dashboard', rooms: 'Browse Rooms', bookings: 'My Bookings',
    payments: 'My Payments', services: 'Services', users: 'Users',
    logs: 'Activity Logs', profile: 'My Profile', reviews: 'Reviews',
    timeline: 'Timeline View'
  }[page] || page;

  const content = document.getElementById('pageContent');
  content.innerHTML = `<div style="text-align:center;padding:60px 0;color:var(--text-muted)"><div class="loading-spinner" style="width:24px;height:24px;border:2px solid var(--border);border-top-color:var(--gold);border-radius:50%;animation:spin 0.7s linear infinite;display:inline-block;margin-bottom:12px;"></div><br>Loading...</div>`;

  switch (page) {
    case 'dashboard': await loadDashboard(); break;
    case 'rooms': await loadRooms(); break;
    case 'bookings': await loadBookings(); break;
    case 'payments': await loadPayments(); break;
    case 'services': await loadServices(); break;
    case 'users': await loadUsers(); break;
    case 'logs': await loadLogs(); break;
    case 'profile': await loadProfile(); break;
    case 'reviews': await loadReviews(); break;
    case 'timeline': await loadTimeline(); break;
  }
}

async function loadDashboard() {
  const stats = await api('dashboard.php');
  const role = CURRENT_USER.role;
  const el = document.getElementById('pageContent');

  let statsHTML = '';
  if (role === 'admin') {
    statsHTML = `
      <div class="stats-grid">
        ${statCard('Total Rooms', stats.total_rooms, icons.rooms, '--gold')}
        ${statCard('Available', stats.available_rooms, icons.rooms, '--green')}
        ${statCard('Occupied', stats.occupied_rooms, icons.rooms, '--orange')}
        ${statCard('Maintenance', stats.maintenance_rooms, icons.rooms, '--red')}
        ${statCard('Active Bookings', stats.active_bookings, icons.bookings, '--blue')}
        ${statCard('Total Revenue', '৳' + Number(stats.total_revenue).toLocaleString(), icons.payments, '--gold')}
        ${statCard('Customers', stats.total_customers, icons.users, '--green')}
        ${statCard('Staff Members', stats.total_staff, icons.users, '--blue')}
        ${statCard('Pending Payments', stats.pending_payments, icons.payments, '--orange')}
        ${statCard('Pending Services', stats.pending_services, icons.services, '--orange')}
      </div>
      <div class="dashboard-charts-grid">
        <div class="card" style="margin-bottom:0;">
          <div class="card-title">Revenue Trend <span class="subtitle">Last 6 Months</span></div>
          <div style="position:relative; height:280px; width:100%;">
            <canvas id="chartRevenue"></canvas>
          </div>
        </div>
        <div class="card" style="margin-bottom:0;">
          <div class="card-title">Room Occupancy <span class="subtitle">Current Status</span></div>
          <div style="position:relative; height:280px; width:100%; display:flex; justify-content:center; align-items:center;">
            <canvas id="chartOccupancy"></canvas>
          </div>
        </div>
      </div>`;
  } else if (role === 'staff') {
    statsHTML = `
      <div class="stats-grid">
        ${statCard('Total Rooms', stats.total_rooms, icons.rooms, '--gold')}
        ${statCard('Available', stats.available_rooms, icons.rooms, '--green')}
        ${statCard('Occupied', stats.occupied_rooms, icons.rooms, '--orange')}
        ${statCard('Active Bookings', stats.active_bookings, icons.bookings, '--blue')}
        ${statCard('Customers', stats.total_customers, icons.users, '--green')}
        ${statCard('Pending Services', stats.pending_services, icons.services, '--orange')}
      </div>`;
  } else {
    statsHTML = `
      <div class="stats-grid">
        ${statCard('My Bookings', stats.total_bookings, icons.bookings, '--gold')}
        ${statCard('Active Stays', stats.active_bookings, icons.bookings, '--green')}
        ${statCard('Total Spent', '৳' + Number(stats.total_spent).toLocaleString(), icons.payments, '--blue')}
        ${statCard('Service Requests', stats.service_requests, icons.services, '--orange')}
      </div>`;
  }

  const recentHTML = stats.recent_bookings?.length ? `
    <div class="card">
      <div class="card-title">Recent Bookings <span class="subtitle">Latest activity</span></div>
      <div class="table-wrap">
        <table>
          <thead><tr>
            <th>#</th><th>Room</th>${role !== 'customer' ? '<th>Customer</th>' : ''}<th>Check In</th><th>Check Out</th><th>Amount</th><th>Status</th>
          </tr></thead>
          <tbody>
            ${stats.recent_bookings.map(b => `<tr>
              <td class="text-muted">#${b.id}</td>
              <td class="name">${escapeHtml(b.room_number)} <span class="text-muted">(${escapeHtml(b.room_type || '')})</span></td>
              ${role !== 'customer' ? `<td>${escapeHtml(b.customer_name)}</td>` : ''}
              <td>${formatDate(b.check_in)}</td>
              <td>${formatDate(b.check_out)}</td>
              <td>৳${Number(b.total_price).toLocaleString()}</td>
              <td>${bookingBadge(b.status)}</td>
            </tr>`).join('')}
          </tbody>
        </table>
      </div>
    </div>` : '';

  el.innerHTML = statsHTML + recentHTML;
  if (role === 'admin') {
    initDashboardCharts(stats);
  }
}

function initDashboardCharts(stats) {
  const revCtx = document.getElementById('chartRevenue');
  if (revCtx) {
    const revenueData = stats.monthly_revenue || [];
    const labels = revenueData.map(d => d.month);
    const data = revenueData.map(d => parseFloat(d.revenue));

    new Chart(revCtx, {
      type: 'bar',
      data: {
        labels: labels,
        datasets: [{
          label: 'Revenue (৳)',
          data: data,
          backgroundColor: 'rgba(201, 168, 76, 0.6)',
          borderColor: '#c9a84c',
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            display: false
          }
        },
        scales: {
          y: {
            beginAtZero: true,
            grid: {
              color: 'rgba(255, 255, 255, 0.05)'
            },
            ticks: {
              color: '#b0a898',
              font: { family: 'Segoe UI' }
            }
          },
          x: {
            grid: {
              display: false
            },
            ticks: {
              color: '#b0a898',
              font: { family: 'Segoe UI' }
            }
          }
        }
      }
    });
  }

  const occCtx = document.getElementById('chartOccupancy');
  if (occCtx) {
    new Chart(occCtx, {
      type: 'doughnut',
      data: {
        labels: ['Available', 'Occupied', 'Maintenance'],
        datasets: [{
          data: [
            parseInt(stats.available_rooms || 0),
            parseInt(stats.occupied_rooms || 0),
            parseInt(stats.maintenance_rooms || 0)
          ],
          backgroundColor: [
            'rgba(76, 201, 122, 0.6)',
            'rgba(224, 146, 90, 0.6)',
            'rgba(224, 90, 90, 0.6)'
          ],
          borderColor: [
            '#4cc97a',
            '#e0925a',
            '#e05a5a'
          ],
          borderWidth: 1
        }]
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              color: '#e8e0d0',
              font: { family: 'Segoe UI', size: 10 }
            }
          }
        },
        cutout: '70%'
      }
    });
  }
}

function statCard(label, value, icon, accentVar) {
  return `<div class="stat-card" style="--accent: var(${accentVar})">
    <div class="stat-label">${label}</div>
    <div class="stat-value">${value}</div>
    <div class="stat-icon">${icon}</div>
  </div>`;
}

async function loadRooms() {
  let rooms = await api('rooms.php');
  const role = CURRENT_USER.role;
  const canEdit = role === 'admin' || role === 'staff';
  const isCustomer = role === 'customer';

  const filterBar = `
    <div class="d-flex justify-between align-center mb-16">
      <div class="tabs" id="roomFilter" style="border:none;margin:0;">
        <span class="tab active" onclick="filterRooms(event,'')">All</span>
        <span class="tab" onclick="filterRooms(event,'available')">Available</span>
        <span class="tab" onclick="filterRooms(event,'occupied')">Occupied</span>
        <span class="tab" onclick="filterRooms(event,'maintenance')">Maintenance</span>
      </div>
      ${canEdit ? `<button class="btn btn-primary btn-sm" onclick="openRoomModal()">+ Add Room</button>` : ''}
    </div>`;

  const roomCards = rooms.map(r => `
    <div class="room-wrap" data-status="${r.status}">
    <div class="card room-card" style="margin-bottom:0; position:relative; overflow:hidden; height:100%;">
      <div style="position:absolute;top:0;left:0;right:0;height:3px;background:${r.status === 'available' ? 'var(--green)' : r.status === 'occupied' ? 'var(--orange)' : 'var(--red)'}"></div>
      ${r.image_url ? `<div style="height:150px; width:100%; overflow:hidden; margin-bottom:12px; border-bottom: 1px solid var(--border);"><img src="${escapeHtml(r.image_url)}" style="width:100%; height:100%; object-fit:cover;"></div>` : ''}
      <div class="d-flex justify-between align-center" style="margin-bottom:12px; padding: ${r.image_url ? '0 16px' : '0'};">
        <div>
          <div style="font-family:'Cormorant Garamond',serif;font-size:22px;color:var(--text)">Room ${escapeHtml(r.room_number)}</div>
          <div style="font-size:10px;color:var(--text-muted);letter-spacing:1px;">Floor ${escapeHtml(r.floor)} · ${escapeHtml(r.room_type)}</div>
        </div>
        <div style="text-align:right;">
          ${roomStatusBadge(r.status)}
          <div style="font-family:'Cormorant Garamond',serif;font-size:20px;color:var(--gold);margin-top:4px;">৳${Number(r.price_per_night).toLocaleString()}<span style="font-size:11px;color:var(--text-muted)">/night</span></div>
        </div>
      </div>
      <div style="padding: ${r.image_url ? '0 16px 16px 16px' : '0'};">
        <div style="font-size:11px;color:var(--text2);margin-bottom:8px;">${escapeHtml(r.description || '')}</div>
        <div style="font-size:10px;color:var(--text-muted);">${escapeHtml(r.amenities || '')}</div>
        <div style="margin-top:14px;display:flex;gap:8px;flex-wrap:wrap;">
          ${canEdit ? `<button class="btn btn-ghost btn-sm" onclick="openRoomModal(${r.id})">Edit</button>` : ''}
          ${isCustomer && r.status === 'available' ? `<button class="btn btn-primary btn-sm" onclick="openBookingModal(${r.id})">Book Now</button>` : ''}
          ${canEdit ? `<button class="btn btn-danger btn-sm" onclick="deleteRoom(${r.id})">Delete</button>` : ''}
        </div>
      </div>
    </div></div>`).join('');

  document.getElementById('pageContent').innerHTML = filterBar + `<div class="rooms-grid" style="display:grid;grid-template-columns:repeat(auto-fill,minmax(300px,1fr));gap:16px;">${roomCards || '<div class="empty-state"><p>No rooms available.</p></div>'}</div>`;
}

function filterRooms(e, status) {
  document.querySelectorAll('#roomFilter .tab').forEach(t => t.classList.remove('active'));
  e.target.classList.add('active');
  document.querySelectorAll('.room-wrap').forEach(wrap => {
    wrap.style.display = (!status || wrap.dataset.status === status) ? '' : 'none';
  });
}

async function openRoomModal(id = null) {
  let room = {};
  if (id) { room = await api(`rooms.php?id=${id}`); }
  openModal(id ? 'Edit Room' : 'Add Room', `
    <div class="form-grid">
      ${!id ? `<div class="form-group"><label>Room Number</label><input id="f_rno" value="${escapeHtml(room.room_number || '')}"></div>` : ''}
      <div class="form-group"><label>Type</label>
        <select id="f_rtype">
          ${['Single', 'Double', 'Suite', 'Deluxe'].map(t => `<option ${room.room_type === t ? 'selected' : ''}>${t}</option>`).join('')}
        </select>
      </div>
      <div class="form-group"><label>Price/Night (৳)</label><input id="f_rprice" type="number" value="${room.price_per_night || ''}"></div>
      <div class="form-group"><label>Capacity</label><input id="f_rcap" type="number" value="${room.capacity || 1}"></div>
      <div class="form-group"><label>Floor</label><input id="f_rfloor" type="number" value="${room.floor || 1}"></div>
      <div class="form-group"><label>Status</label>
        <select id="f_rstatus">
          ${['available', 'occupied', 'maintenance'].map(s => `<option ${room.status === s ? 'selected' : ''}>${s}</option>`).join('')}
        </select>
      </div>
      <div class="form-group form-col-span"><label>Description</label><textarea id="f_rdesc">${escapeHtml(room.description || '')}</textarea></div>
      <div class="form-group form-col-span"><label>Amenities</label><input id="f_rame" value="${escapeHtml(room.amenities || '')}"></div>
      <div class="form-group form-col-span">
        <label>Room Picture</label>
        <input type="file" id="f_rimage" accept="image/*">
        ${room.image_url ? `<div style="margin-top: 8px;"><img src="${room.image_url}" style="max-height: 80px; border: 1px solid var(--border);"></div>` : ''}
      </div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveRoom(${id || 'null'})">${id ? 'Update' : 'Add Room'}</button>
    </div>
  `);
}

async function saveRoom(id) {
  const fd = new FormData();
  fd.append('room_type', val('f_rtype'));
  fd.append('price_per_night', val('f_rprice'));
  fd.append('capacity', val('f_rcap'));
  fd.append('floor', val('f_rfloor'));
  fd.append('status', val('f_rstatus'));
  fd.append('description', val('f_rdesc'));
  fd.append('amenities', val('f_rame'));

  if (!id) fd.append('room_number', val('f_rno'));
  if (id) fd.append('id', id);

  const fileInput = document.getElementById('f_rimage');
  if (fileInput && fileInput.files[0]) {
    fd.append('image', fileInput.files[0]);
  }

  try {
    const res = await fetch('api/rooms.php', {
      method: 'POST',
      headers: {
        'X-CSRF-Token': window._csrfToken || ''
      },
      body: fd
    });
    if (res.status === 401) {
      window.location.href = 'index.php?route=login';
      return;
    }
    const data = await res.json();
    if (data.success) {
      toast('Room saved!', 'success');
      closeModal();
      loadRooms();
    } else {
      toast(data.error, 'error');
    }
  } catch (e) {
    toast('Server error', 'error');
  }
}

async function deleteRoom(id) {
  if (!confirm('Delete this room?')) return;
  const res = await api('rooms.php', 'DELETE', { id });
  if (res.success) { toast('Room deleted', 'success'); loadRooms(); }
  else toast(res.error, 'error');
}

async function loadBookings() {
  const bookings = await api('bookings.php');
  const role = CURRENT_USER.role;
  const canManage = role === 'admin' || role === 'staff';

  let reviewedBookingIds = [];
  if (role === 'customer') {
    const reviews = await api('reviews.php');
    if (Array.isArray(reviews)) {
      reviewedBookingIds = reviews.map(r => parseInt(r.booking_id));
    }
  }

  const header = `
    <div class="d-flex justify-between align-center mb-16">
      <div style="font-size:12px;color:var(--text-muted)">${bookings.length} booking(s)</div>
      ${role === 'customer' ? `<button class="btn btn-primary btn-sm" onclick="navigate('rooms')">+ New Booking</button>` : ''}
    </div>`;

  const table = `<div class="card"><div class="table-wrap"><table>
    <thead><tr>
      <th>#</th>${canManage ? '<th>Customer</th>' : ''}
      <th>Room</th><th>Check In</th><th>Check Out</th><th>Nights</th><th>Total</th>
      <th>Booking</th>${role === 'customer' ? '<th>Payment</th>' : ''}
      <th>Actions</th>
    </tr></thead>
    <tbody>
      ${bookings.length ? bookings.map(b => {
    const nights = Math.round((new Date(b.check_out) - new Date(b.check_in)) / (86400000));

    const payBadge = role === 'customer'
      ? (b.payment_status === 'paid'
        ? '<span class="badge badge-green">Paid</span>'
        : b.payment_status === 'pending'
          ? '<span class="badge badge-orange">Pending</span>'
          : '<span class="badge badge-red">Not Paid</span>')
      : '';

    const showPay = role === 'customer' && b.payment_status !== 'paid';
    const isCompleted = b.status === 'checked_out';
    const showFeedbackBtn = role === 'customer' && isCompleted && !reviewedBookingIds.includes(parseInt(b.id));

    return `<tr>
          <td class="text-muted">#${b.id}</td>
          ${canManage ? `<td class="name">${escapeHtml(b.customer_name || '—')}</td>` : ''}
          <td class="name">${escapeHtml(b.room_number)} <span class="text-muted">(${escapeHtml(b.room_type)})</span></td>
          <td>${formatDate(b.check_in)}</td>
          <td>${formatDate(b.check_out)}</td>
          <td>${nights}</td>
          <td>৳${Number(b.total_price).toLocaleString()}</td>
          <td>${bookingBadge(b.status)}</td>
          ${role === 'customer' ? `<td>${payBadge}</td>` : ''}
          <td style="display:flex;gap:6px;flex-wrap:wrap;">
            <button class="btn btn-ghost btn-sm" onclick="openBookingDetail(${b.id})">View</button>
            ${canManage ? `<button class="btn btn-ghost btn-sm" onclick="editBooking(${b.id})">Edit</button>` : ''}
            ${showPay ? `<button class="btn btn-primary btn-sm" onclick="openPayFromBooking(${b.id}, ${b.total_price})">Pay Now</button>` : ''}
            ${showFeedbackBtn ? `<button class="btn btn-primary btn-sm" onclick="openFeedbackModal(${b.id}, '${escapeHtml(b.room_number)}')">Give Feedback</button>` : ''}
          </td>
        </tr>`;
  }).join('') : '<tr><td colspan="9" class="text-muted" style="text-align:center;padding:40px">No bookings found.</td></tr>'}
    </tbody>
  </table></div></div>`;

  document.getElementById('pageContent').innerHTML = header + table;
}

async function openNewBookingModal() {
  const rooms = await api('rooms.php?status=available');
  const customers = await api('users.php');
  const customerList = customers.filter(u => u.role === 'customer');
  openModal('New Booking', `
    <div class="form-grid">
      <div class="form-group form-col-span"><label>Customer</label>
        <select id="f_bcust">
          ${customerList.map(c => `<option value="${c.id}">${escapeHtml(c.name)} (${escapeHtml(c.email)})</option>`).join('')}
        </select>
      </div>
      <div class="form-group form-col-span"><label>Room</label>
        <select id="f_broom">
          ${rooms.map(r => `<option value="${r.id}">Room ${r.room_number} - ${r.room_type} - ৳${r.price_per_night}/night</option>`).join('')}
        </select>
      </div>
      <div class="form-group"><label>Check In</label><input type="date" id="f_bcin" min="${today()}"></div>
      <div class="form-group"><label>Check Out</label><input type="date" id="f_bcout" min="${today()}"></div>
      <div class="form-group form-col-span"><label>Special Requests</label><textarea id="f_breq"></textarea></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveBooking()">Confirm Booking</button>
    </div>
  `);
}

async function openBookingModal(roomId) {
  const rooms = await api('rooms.php');
  const selectedRoom = rooms.find(r => r.id == roomId);
  if (!selectedRoom) return;

  const sortedRooms = [...rooms].sort((a,b) => {
    if (a.floor !== b.floor) return a.floor - b.floor;
    return a.room_number.localeCompare(b.room_number);
  });

  const floors = {};
  sortedRooms.forEach(r => {
    if (!floors[r.floor]) floors[r.floor] = [];
    floors[r.floor].push(r);
  });

  let floorMapHTML = `<div style="margin-bottom: 20px;"><div style="font-size: 10px; color: var(--gold); letter-spacing: 1px; font-weight: bold; text-transform: uppercase; margin-bottom: 10px;">Select Room From Map</div>`;
  Object.keys(floors).forEach(f => {
    floorMapHTML += `
      <div style="margin-bottom: 12px;">
        <div style="font-size: 9px; color: var(--text-muted); margin-bottom: 6px; text-transform:uppercase; letter-spacing:1px;">Floor ${f}</div>
        <div style="display: flex; gap: 8px; flex-wrap: wrap;">
          ${floors[f].map(r => {
            let bgColor = 'var(--green)';
            let cursor = 'pointer';
            let title = `Room ${r.room_number} - Available`;
            if (r.status === 'occupied') {
              bgColor = 'var(--orange)';
              cursor = 'not-allowed';
              title = `Room ${r.room_number} - Occupied`;
            } else if (r.status === 'maintenance') {
              bgColor = 'var(--red)';
              cursor = 'not-allowed';
              title = `Room ${r.room_number} - Maintenance`;
            }

            const borderStyle = r.id == roomId ? 'border: 2px solid var(--text);' : 'border: 1px solid var(--border);';
            const onClickAttr = r.status === 'available' ? `onclick="changeModalSelectedRoom(${r.id})"` : '';
            return `<div ${onClickAttr} style="padding: 6px 12px; background:${bgColor}22; color:${bgColor}; ${borderStyle} border-radius: 4px; font-size: 11px; cursor: ${cursor}; font-weight:600;" title="${title}">Room ${escapeHtml(r.room_number)}</div>`;
          }).join('')}
        </div>
      </div>
    `;
  });
  floorMapHTML += `</div>`;

  openModal('Book Room', `
    ${floorMapHTML}
    <div id="bookingSelectedRoomCard" style="background:var(--dark4);border:1px solid var(--border);padding:16px;margin-bottom:20px;">
      <div style="font-family:'Cormorant Garamond',serif;font-size:20px;color:var(--text)">Room ${escapeHtml(selectedRoom.room_number)} — ${escapeHtml(selectedRoom.room_type)}</div>
      <div style="color:var(--gold);font-size:18px;margin-top:4px;">৳${Number(selectedRoom.price_per_night).toLocaleString()} / night</div>
      <div style="color:var(--text-muted);font-size:11px;margin-top:6px;">${escapeHtml(selectedRoom.amenities)}</div>
    </div>
    <div class="form-grid">
      <div class="form-group"><label>Check In</label><input type="date" id="f_bcin" min="${today()}" onchange="calcTotal(${selectedRoom.price_per_night})"></div>
      <div class="form-group"><label>Check Out</label><input type="date" id="f_bcout" min="${today()}" onchange="calcTotal(${selectedRoom.price_per_night})"></div>
      <div class="form-group form-col-span"><label>Special Requests</label><textarea id="f_breq" placeholder="Any special requirements..."></textarea></div>
    </div>
    <div id="totalPreview" style="background:var(--dark4);border:1px solid var(--border);padding:14px;margin-top:16px;display:none;">
      <div style="font-size:11px;color:var(--text-muted);">ESTIMATED TOTAL</div>
      <div style="font-family:'Cormorant Garamond',serif;font-size:26px;color:var(--gold);" id="totalAmount"></div>
    </div>
    <div class="form-actions" id="bookingModalActions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveCustomerBooking(${selectedRoom.id})">Confirm Booking</button>
    </div>
  `);
}

function changeModalSelectedRoom(roomId) {
  closeModal();
  setTimeout(() => openBookingModal(roomId), 150);
}

function calcTotal(pricePerNight) {
  const ci = val('f_bcin'), co = val('f_bcout');
  if (ci && co) {
    const nights = Math.round((new Date(co) - new Date(ci)) / 86400000);
    if (nights > 0) {
      document.getElementById('totalPreview').style.display = '';
      document.getElementById('totalAmount').textContent = `৳${(nights * pricePerNight).toLocaleString()} (${nights} night${nights > 1 ? 's' : ''})`;
    }
  }
}

async function saveCustomerBooking(roomId) {
  const res = await api('bookings.php', 'POST', {
    room_id: roomId,
    check_in: val('f_bcin'),
    check_out: val('f_bcout'),
    special_requests: val('f_breq')
  });
  if (res.success) { toast('Booking confirmed!', 'success'); closeModal(); loadBookings(); }
  else toast(res.error, 'error');
}

async function saveBooking() {
  const res = await api('bookings.php', 'POST', {
    customer_id: val('f_bcust'),
    room_id: val('f_broom'),
    check_in: val('f_bcin'),
    check_out: val('f_bcout'),
    special_requests: val('f_breq')
  });
  if (res.success) { toast('Booking confirmed!', 'success'); closeModal(); loadBookings(); }
  else toast(res.error, 'error');
}

async function openBookingDetail(id) {
  const b = await api(`bookings.php?id=${id}`);
  
  let reviewBtnHTML = '';
  const todayStr = new Date().toISOString().split('T')[0];
  const isCompleted = b.status === 'checked_out' || (b.status !== 'cancelled' && b.check_out <= todayStr);
  if (CURRENT_USER.role === 'customer' && isCompleted) {
    const rev = await api(`reviews.php?booking_id=${id}`);
    if (Array.isArray(rev) && rev.length > 0) {
      const firstRev = rev[0];
      reviewBtnHTML = `
        <button class="btn btn-ghost btn-sm" onclick="editReview(${firstRev.id}, ${firstRev.rating}, '${escapeHtml(firstRev.comment || '')}', ${b.id})">Edit Feedback</button>
        <button class="btn btn-danger btn-sm" onclick="deleteReviewFromModal(${firstRev.id}, ${b.id})">Delete Feedback</button>
      `;
    } else if (!rev || rev.error || !rev.id) {
      reviewBtnHTML = `<button class="btn btn-primary" onclick="openFeedbackModal(${b.id}, '${escapeHtml(b.room_number)}')">Give Feedback</button>`;
    }
  }

  openModal(`Booking #${b.id}`, `
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:20px;">
      <div style="background:var(--dark4);border:1px solid var(--border);padding:16px;">
        <div style="font-size:10px;letter-spacing:2px;color:var(--gold);text-transform:uppercase;margin-bottom:8px;">Room</div>
        <div style="font-size:18px;color:var(--text)">Room ${escapeHtml(b.room_number)}</div>
        <div style="color:var(--text-muted);font-size:11px;">${escapeHtml(b.room_type)} · ৳${b.price_per_night}/night</div>
      </div>
      <div style="background:var(--dark4);border:1px solid var(--border);padding:16px;">
        <div style="font-size:10px;letter-spacing:2px;color:var(--gold);text-transform:uppercase;margin-bottom:8px;">Status</div>
        <div>${bookingBadge(b.status)}</div>
        <div style="color:var(--text-muted);font-size:11px;margin-top:6px;">Total: ৳${Number(b.total_price).toLocaleString()}</div>
      </div>
    </div>
    ${CURRENT_USER.role !== 'customer' ? `
    <div style="background:var(--dark4);border:1px solid var(--border);padding:16px;margin-bottom:16px;">
      <div style="font-size:10px;letter-spacing:2px;color:var(--gold);text-transform:uppercase;margin-bottom:8px;">Customer</div>
      <div style="color:var(--text)">${escapeHtml(b.customer_name)}</div>
      <div style="color:var(--text-muted);font-size:11px;">${escapeHtml(b.customer_email)} · ${escapeHtml(b.customer_phone || '—')}</div>
    </div>` : ''}
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;margin-bottom:16px;">
      <div><div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Check In</div><div style="color:var(--text)">${formatDate(b.check_in)}</div></div>
      <div><div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:4px;">Check Out</div><div style="color:var(--text)">${formatDate(b.check_out)}</div></div>
    </div>
    ${b.special_requests ? `<div style="background:var(--dark4);border:1px solid var(--border);padding:14px;margin-bottom:16px;"><div style="font-size:10px;color:var(--text-muted);text-transform:uppercase;letter-spacing:1px;margin-bottom:6px;">Special Requests</div><div style="color:var(--text2);font-size:12px;">${escapeHtml(b.special_requests)}</div></div>` : ''}
    <div class="form-actions" style="margin-top:20px;">
      ${reviewBtnHTML}
      <a href="invoice.php?id=${b.id}" target="_blank" class="btn btn-ghost" style="display:inline-block; text-decoration:none; text-align:center; padding: 10px 20px;">Print Invoice / PDF</a>
      <button class="btn btn-ghost" onclick="closeModal()">Close</button>
    </div>
  `);
}

async function editBooking(id) {
  const b = await api(`bookings.php?id=${id}`);
  const role = CURRENT_USER.role;
  const canManage = role === 'admin' || role === 'staff';

  // Only staff/admin can change booking status; customers cannot cancel
  if (!canManage) {
    toast('Only staff or admin can modify booking status.', 'error');
    return;
  }
  const statusOptions = ['pending', 'confirmed', 'checked_in', 'checked_out', 'cancelled'];

  openModal(`Edit Booking #${id}`, `
    <div class="form-grid">
      <div class="form-group form-col-span"><label>Status</label>
        <select id="f_estatus">
          ${statusOptions.map(s => `<option ${b.status === s ? 'selected' : ''}>${s}</option>`).join('')}
        </select>
      </div>
      <div class="form-group form-col-span"><label>Special Requests</label>
        <textarea id="f_ereq">${b.special_requests || ''}</textarea>
      </div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="updateBooking(${id})">Update</button>
    </div>
  `);
}

async function updateBooking(id) {
  const res = await api('bookings.php', 'PUT', {
    id, status: val('f_estatus'), special_requests: val('f_ereq')
  });
  if (res.success) { toast('Booking updated!', 'success'); closeModal(); loadBookings(); }
  else toast(res.error, 'error');
}

async function loadPayments() {
  const role = CURRENT_USER.role;
  const canManage = role === 'admin' || role === 'staff';

  if (canManage) {
    const payments = await api('payments.php');

    const header = `<div class="d-flex justify-between align-center mb-16">
      <div style="font-size:12px;color:var(--text-muted)">${payments.length} payment record(s)</div>
      <button class="btn btn-primary btn-sm" onclick="openPaymentModal()">+ Record Payment</button>
    </div>`;

    const rows = payments.length ? payments.map(p => {
      const payBadge = p.payment_status === 'paid' ? '<span class="badge badge-green">Paid</span>' : p.payment_status === 'refunded' ? '<span class="badge badge-blue">Refunded</span>' : '<span class="badge badge-orange">Pending</span>';
      const methodBadge = `<span class="badge badge-muted">${p.payment_method}</span>`;
      const bookBadge = bookingBadge(p.booking_status || '—');
      const actionBtn = (p.payment_status === 'pending' && p.payment_method === 'cash')
        ? `<button class="btn btn-primary btn-sm" onclick="confirmCashPayment(${p.id})">Confirm Cash</button>`
        : '<span class="text-muted">—</span>';
      return `<tr>
        <td class="text-muted">#${p.id}</td>
        <td class="name">${escapeHtml(p.customer_name || '—')}</td>
        <td>${escapeHtml(p.room_number || '—')}</td>
        <td>#${p.booking_id}</td>
        <td>৳${Number(p.amount).toLocaleString()}</td>
        <td>${methodBadge}</td>
        <td>${payBadge}</td>
        <td>${p.paid_at ? formatDateTime(p.paid_at) : '<span class="text-muted">—</span>'}</td>
        <td>${bookBadge}</td>
        <td>${actionBtn}</td>
      </tr>`;
    }).join('') : `<tr><td colspan="10" class="text-muted" style="text-align:center;padding:40px">No payment records found.</td></tr>`;

    document.getElementById('pageContent').innerHTML = header + `<div class="card"><div class="table-wrap"><table>
      <thead><tr><th>#</th><th>Customer</th><th>Room</th><th>Booking</th><th>Amount</th><th>Method</th><th>Payment</th><th>Paid At</th><th>Booking</th><th>Action</th></tr></thead>
      <tbody>${rows}</tbody>
    </table></div></div>`;

  } else {
    const bookings = await api('bookings.php');
    const payments = await api('payments.php');

    const payMap = {};
    payments.forEach(p => { payMap[p.booking_id] = p; });

    const rows = bookings.length ? bookings.map(b => {
      const p = payMap[b.id];
      const amount = b.total_price;

      const payBadge = p
        ? (p.payment_status === 'paid' ? '<span class="badge badge-green">Paid</span>' : '<span class="badge badge-orange">Pending — Awaiting confirmation</span>')
        : '<span class="badge badge-red">Not Paid</span>';

      const methodBadge = p
        ? `<span class="badge badge-muted">${p.payment_method}</span>`
        : '<span class="text-muted">—</span>';

      const canPay = !p || p.payment_status === 'pending';
      const actionBtn = canPay
        ? `<button class="btn btn-primary btn-sm" onclick="openCustomerPayModal(${b.id}, ${amount})">Pay Now</button>`
        : '<span class="badge badge-green" style="font-size:10px;">✓ Paid</span>';

      return `<tr>
        <td class="text-muted">#${b.id}</td>
        <td class="name">${escapeHtml(b.room_number)} <span class="text-muted">(${escapeHtml(b.room_type)})</span></td>
        <td>${formatDate(b.check_in)} → ${formatDate(b.check_out)}</td>
        <td>৳${Number(amount).toLocaleString()}</td>
        <td>${methodBadge}</td>
        <td>${payBadge}</td>
        <td>${bookingBadge(b.status)}</td>
        <td>${actionBtn}</td>
      </tr>`;
    }).join('') : `<tr><td colspan="8" class="text-muted" style="text-align:center;padding:40px">No bookings found.</td></tr>`;

    document.getElementById('pageContent').innerHTML = `
      <div class="card"><div class="card-title">My Payments <span class="subtitle">All booking payments</span></div>
      <div class="table-wrap"><table>
        <thead><tr><th>Booking</th><th>Room</th><th>Dates</th><th>Amount</th><th>Method</th><th>Payment Status</th><th>Booking Status</th><th>Action</th></tr></thead>
        <tbody>${rows}</tbody>
      </table></div></div>`;
  }
}

async function openPayFromBooking(bookingId, amount) {
  const payments = await api('payments.php?booking_id=' + bookingId);
  const existing = Array.isArray(payments) ? payments.find(p => p.booking_id == bookingId) : null;
  if (existing && existing.payment_status === 'paid') {
    toast('This booking is already paid!', 'info'); return;
  }
  openCustomerPayModal(bookingId, amount);
}

function openCustomerPayModal(bookingId, amount) {
  openModal('Choose Payment Method', `
    <div style="background:var(--dark4);border:1px solid var(--border);padding:16px;margin-bottom:20px;">
      <div style="font-size:10px;color:var(--text-muted);letter-spacing:2px;text-transform:uppercase;margin-bottom:6px;">Amount Due</div>
      <div style="font-size:32px;color:var(--gold);">৳${Number(amount).toLocaleString()}</div>
    </div>
    <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:16px;">
      <div id="optCard" onclick="selectMethod('card')"
        style="background:rgba(76,201,122,0.07);border:2px solid var(--green);padding:20px 12px;cursor:pointer;text-align:center;transition:all 0.2s;">
        <div style="font-size:28px;margin-bottom:8px;">💳</div>
        <div style="color:var(--green);font-size:12px;font-weight:600;letter-spacing:1px;">CARD</div>
        <div style="color:var(--text-muted);font-size:10px;margin-top:4px;">Instant confirmation</div>
      </div>
      <div id="optCash" onclick="selectMethod('cash')"
        style="background:var(--dark4);border:2px solid var(--border);padding:20px 12px;cursor:pointer;text-align:center;transition:all 0.2s;">
        <div style="font-size:28px;margin-bottom:8px;">💵</div>
        <div style="color:var(--text2);font-size:12px;font-weight:600;letter-spacing:1px;">CASH</div>
        <div style="color:var(--text-muted);font-size:10px;margin-top:4px;">Pay at front desk</div>
      </div>
    </div>
    <div id="methodNote" style="padding:12px 14px;font-size:12px;background:rgba(76,201,122,0.07);border:1px solid rgba(76,201,122,0.2);color:#4cc97a;margin-bottom:4px;">
      ✓ You will be taken to a secure card payment page. Booking confirms instantly.
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="proceedWithPayment(${bookingId}, ${amount})">Proceed</button>
    </div>
  `);
  window._payMethod = 'card';
}

function selectMethod(method) {
  window._payMethod = method;
  const card = document.getElementById('optCard');
  const cash = document.getElementById('optCash');
  const note = document.getElementById('methodNote');
  if (method === 'card') {
    card.style.border = '2px solid var(--green)';
    card.style.background = 'rgba(76,201,122,0.07)';
    cash.style.border = '2px solid var(--border)';
    cash.style.background = 'var(--dark4)';
    note.style.background = 'rgba(76,201,122,0.07)';
    note.style.borderColor = 'rgba(76,201,122,0.2)';
    note.style.color = '#4cc97a';
    note.textContent = '✓ You will be taken to a secure card payment page. Booking confirms instantly.';
  } else {
    cash.style.border = '2px solid var(--gold)';
    cash.style.background = 'rgba(201,168,76,0.07)';
    card.style.border = '2px solid var(--border)';
    card.style.background = 'var(--dark4)';
    note.style.background = 'rgba(201,168,76,0.07)';
    note.style.borderColor = 'rgba(201,168,76,0.2)';
    note.style.color = 'var(--gold)';
    note.textContent = 'ℹ Your booking stays pending until staff confirms cash at the front desk.';
  }
}

async function proceedWithPayment(bookingId, amount) {
  const method = window._payMethod || 'card';
  if (method === 'card') {
    closeModal();
    window.location.href = `index.php?route=payment-card&booking_id=${bookingId}&amount=${amount}`;
  } else {
    const res = await api('payments.php', 'POST', {
      booking_id: bookingId,
      amount: amount,
      payment_method: 'cash'
    });
    if (res.success) {
      toast('Cash payment registered. Booking is pending until staff confirms.', 'info');
      closeModal();
      loadBookings();
    } else toast(res.error, 'error');
  }
}

async function submitCustomerPayment(bookingId, amount) {
  proceedWithPayment(bookingId, amount);
}

async function confirmCashPayment(id) {
  if (!confirm('Confirm that cash has been received?')) return;
  const res = await api('payments.php', 'PUT', { id });
  if (res.success) {
    toast('Cash payment confirmed — booking updated!', 'success');
    loadPayments();
  } else toast(res.error, 'error');
}

async function openPaymentModal() {
  const bookings = await api('bookings.php');
  const active = bookings.filter(b => ['pending', 'confirmed', 'checked_in'].includes(b.status));
  openModal('Record Payment', `
    <div class="form-grid">
      <div class="form-group form-col-span"><label>Booking</label>
        <select id="f_pbid">
          ${active.map(b => `<option value="${b.id}">#${b.id} - ${escapeHtml(b.customer_name || '')} - Room ${escapeHtml(b.room_number)} - ৳${Number(b.total_price).toLocaleString()}</option>`).join('')}
        </select>
      </div>
      <div class="form-group"><label>Amount (৳)</label><input id="f_pamount" type="number"></div>
      <div class="form-group"><label>Payment Method</label>
        <select id="f_pmethod">
          <option value="cash">Cash</option>
          <option value="card">Card</option>
          <option value="online">Online</option>
        </select>
      </div>
      <div class="form-group form-col-span"><label>Transaction ID (optional)</label><input id="f_ptxid"></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="savePayment()">Save Payment</button>
    </div>
  `);
}

async function savePayment() {
  const method = val('f_pmethod');
  const res = await api('payments.php', 'POST', {
    booking_id: val('f_pbid'), amount: val('f_pamount'),
    payment_method: method, transaction_id: val('f_ptxid')
  });
  if (res.success) {
    toast(method === 'card' ? 'Card payment recorded — booking confirmed!' : 'Cash payment recorded!', 'success');
    closeModal(); loadPayments();
  } else toast(res.error, 'error');
}

async function loadServices() {
  const services = await api('services.php');
  const requests = await api('services.php?type=requests');
  const role = CURRENT_USER.role;
  const canManage = role === 'admin' || role === 'staff';

  const svcCards = services.map(s => `
    <div class="card" style="margin-bottom:0;">
      <div class="d-flex justify-between align-center">
        <div>
          <div style="color:var(--text);font-weight:500;">${escapeHtml(s.name)}</div>
          <div style="font-size:11px;color:var(--text-muted);margin-top:3px;">${escapeHtml(s.description || '')}</div>
          <span class="badge badge-muted" style="margin-top:8px;font-size:8px;">${escapeHtml(s.category)}</span>
        </div>
        <div style="text-align:right;">
          <div style="font-family:'Cormorant Garamond',serif;font-size:20px;color:var(--gold)">৳${Number(s.price).toLocaleString()}</div>
          ${role === 'customer' ? `<button class="btn btn-primary btn-sm" style="margin-top:8px;" onclick="requestService(${s.id},'${escapeHtml(s.name)}')">Request</button>` : ''}
        </div>
      </div>
    </div>`).join('');

  const reqTable = `<div class="card">
    <div class="card-title">Service Requests <span class="subtitle">All requests</span></div>
    <div class="table-wrap"><table>
      <thead><tr>
        <th>#</th>${canManage ? '<th>Customer</th>' : ''}<th>Service</th><th>Booking</th><th>Qty</th><th>Status</th><th>Requested</th>${canManage ? '<th>Action</th>' : ''}
      </tr></thead>
      <tbody>
        ${requests.length ? requests.map(r => `<tr>
          <td class="text-muted">#${r.id}</td>
          ${canManage ? `<td class="name">${escapeHtml(r.customer_name || '—')}</td>` : ''}
          <td class="name">${escapeHtml(r.service_name)}</td>
          <td>#${r.booking_id}</td>
          <td>${r.quantity}</td>
          <td>${serviceStatusBadge(r.status)}</td>
          <td style="font-size:11px;">${formatDateTime(r.requested_at)}</td>
          ${canManage ? `<td>${r.status === 'pending' ? `<button class="btn btn-ghost btn-sm" onclick="updateServiceStatus(${r.id},'in_progress')">Start</button>` : r.status === 'in_progress' ? `<button class="btn btn-ghost btn-sm" onclick="updateServiceStatus(${r.id},'completed')">Done</button>` : serviceStatusBadge(r.status)}</td>` : ''}
        </tr>`).join('') : '<tr><td colspan="8" class="text-muted" style="text-align:center;padding:40px">No service requests.</td></tr>'}
      </tbody>
    </table></div>
  </div>`;

  document.getElementById('pageContent').innerHTML = `
    <div style="display:grid;grid-template-columns:repeat(auto-fill,minmax(280px,1fr));gap:16px;margin-bottom:24px;">
      ${svcCards}
    </div>
    ${reqTable}
  `;
}

async function requestService(serviceId, serviceName) {
  const bookings = await api('bookings.php');
  const active = bookings.filter(b => ['confirmed', 'checked_in'].includes(b.status));
  if (!active.length) { toast('No active bookings to request service for.', 'error'); return; }
  openModal(`Request: ${escapeHtml(serviceName)}`, `
    <div class="form-grid">
      <div class="form-group form-col-span"><label>Booking</label>
        <select id="f_srbid">
          ${active.map(b => `<option value="${b.id}">#${b.id} - Room ${escapeHtml(b.room_number)}</option>`).join('')}
        </select>
      </div>
      <div class="form-group"><label>Quantity</label><input id="f_srqty" type="number" value="1" min="1"></div>
      <div class="form-group form-col-span"><label>Notes</label><textarea id="f_srnotes"></textarea></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="submitServiceRequest(${serviceId})">Submit Request</button>
    </div>
  `);
}

async function submitServiceRequest(serviceId) {
  const res = await api('services.php', 'POST', {
    type: 'request',
    booking_id: val('f_srbid'),
    service_id: serviceId,
    quantity: val('f_srqty'),
    notes: val('f_srnotes')
  });
  if (res.success) { toast('Service requested!', 'success'); closeModal(); loadServices(); }
  else toast(res.error, 'error');
}

async function updateServiceStatus(id, status) {
  const res = await api('services.php', 'PUT', { id, status });
  if (res.success) { toast('Status updated', 'success'); loadServices(); }
  else toast(res.error, 'error');
}

async function loadUsers() {
  const users = await api('users.php');
  const role = CURRENT_USER.role;

  const visible = role === 'staff' ? users : users;

  const canAddStaff = role === 'admin';
  const canAddCustomer = role === 'admin' || role === 'staff';

  const header = `<div class="d-flex justify-between align-center mb-16">
    <div style="font-size:12px;color:var(--text-muted)">${visible.length} user(s)</div>
    <div class="d-flex gap-8">
      ${canAddCustomer ? `<button class="btn btn-primary btn-sm" onclick="openAddUserModal('customer')">+ Add Customer</button>` : ''}
      ${canAddStaff ? `<button class="btn btn-ghost btn-sm"   onclick="openAddUserModal('staff')">+ Add Staff</button>` : ''}
    </div>
  </div>`;

  const table = `<div class="card"><div class="card-title">
    ${role === 'admin' ? 'All Users' : role === 'staff' ? 'Customers' : 'My Account'}
    <span class="subtitle">${visible.length} user(s)</span>
  </div>
  <div class="table-wrap"><table>
    <thead><tr>
      <th>#</th><th>Name</th><th>Email</th><th>Role</th><th>Phone</th><th>Joined</th><th>Actions</th>
    </tr></thead>
    <tbody>
      ${visible.map(u => `<tr>
        <td class="text-muted">${u.id}</td>
        <td class="name">${escapeHtml(u.name)}</td>
        <td style="color:var(--text2)">${escapeHtml(u.email)}</td>
        <td>${roleBadge(u.role)}</td>
        <td style="color:var(--text-muted)">${escapeHtml(u.phone || '—')}</td>
        <td style="font-size:11px;color:var(--text-muted)">${formatDate(u.created_at)}</td>
        <td>
          ${(role === 'admin' || (role === 'staff' && u.role === 'customer') || u.id == CURRENT_USER.id)
      ? `<button class="btn btn-ghost btn-sm" onclick="openEditUserModal(${u.id})">Edit</button>` : ''}
          ${role === 'admin' && u.id != CURRENT_USER.id ? `<button class="btn btn-danger btn-sm" onclick="deleteUser(${u.id})">Delete</button>` : ''}
        </td>
      </tr>`).join('')}
    </tbody>
  </table></div></div>`;

  document.getElementById('pageContent').innerHTML = header + table;
}

async function openAddUserModal(role) {
  const label = role === 'staff' ? 'Staff Member' : 'Customer';
  openModal(`Add ${label}`, `
    <div class="form-grid">
      <div class="form-group form-col-span"><label>Full Name</label><input id="f_aname" placeholder="Full name"></div>
      <div class="form-group form-col-span"><label>Email</label><input id="f_aemail" type="email" placeholder="email@example.com"></div>
      <div class="form-group"><label>Phone</label><input id="f_aphone" placeholder="+880..."></div>
      <div class="form-group"><label>Address</label><input id="f_aaddr" placeholder="City, Country"></div>
      <div class="form-group"><label>Password</label><input id="f_apass" type="password" placeholder="Min 8 characters"></div>
      <div class="form-group"><label>Confirm Password</label><input id="f_apass2" type="password" placeholder="Repeat password"></div>
    </div>
    <div id="addUserAlert"></div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveNewUser('${role}')">Add ${label}</button>
    </div>
  `);
}

async function saveNewUser(role) {
  const pass = val('f_apass');
  const pass2 = val('f_apass2');
  const alertEl = document.getElementById('addUserAlert');
  alertEl.innerHTML = '';

  if (pass.length < 8) {
    alertEl.innerHTML = '<div class="alert alert-error">Password must be at least 8 characters.</div>'; return;
  }
  if (pass !== pass2) {
    alertEl.innerHTML = '<div class="alert alert-error">Passwords do not match.</div>'; return;
  }

  const res = await api('users.php', 'POST', {
    name: val('f_aname'), email: val('f_aemail'),
    phone: val('f_aphone'), address: val('f_aaddr'),
    password: pass, role
  });

  if (res.success) {
    toast('User added successfully!', 'success');
    closeModal(); loadUsers();
  } else {
    alertEl.innerHTML = `<div class="alert alert-error">${res.error}</div>`;
  }
}

async function openEditUserModal(id) {
  const u = await api(`users.php?id=${id}`);
  openModal('Edit User: ' + escapeHtml(u.name), `
    <div class="form-grid">
      <div class="form-group form-col-span"><label>Full Name</label><input id="f_uname" value="${escapeHtml(u.name)}"></div>
      <div class="form-group form-col-span"><label>Email <span style="color:var(--text-muted);font-size:9px">(cannot change)</span></label><input value="${escapeHtml(u.email)}" disabled style="opacity:0.5"></div>
      <div class="form-group"><label>Phone</label><input id="f_uphone" value="${escapeHtml(u.phone || '')}"></div>
      <div class="form-group"><label>Address</label><input id="f_uaddr" value="${escapeHtml(u.address || '')}"></div>
      <div class="form-group"><label>New Password <span style="color:var(--text-muted);font-size:9px">(min 8 chars, leave blank to keep)</span></label><input id="f_upass" type="password" placeholder="Leave blank to keep"></div>
    </div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="saveUser(${id})">Save Changes</button>
    </div>
  `);
}

async function saveUser(id) {
  const pass = val('f_upass');
  if (pass && pass.length < 8) { toast('Password must be at least 8 characters', 'error'); return; }
  const body = { id, name: val('f_uname'), phone: val('f_uphone'), address: val('f_uaddr') };
  if (pass) body.password = pass;
  const res = await api('users.php', 'PUT', body);
  if (res.success) { toast('User updated!', 'success'); closeModal(); loadUsers(); }
  else toast(res.error, 'error');
}

async function deleteUser(id) {
  if (!confirm('Delete this user? All their bookings will also be deleted.')) return;
  const res = await api('users.php', 'DELETE', { id });
  if (res.success) { toast('User deleted', 'success'); loadUsers(); }
  else toast(res.error, 'error');
}

async function loadLogs() {
  const logs = await api('logs.php');
  document.getElementById('pageContent').innerHTML = `
    <div class="card">
      <div class="card-title">Activity Logs <span class="subtitle">Last 100 entries</span></div>
      <div class="table-wrap"><table>
        <thead><tr><th>#</th><th>User</th><th>Role</th><th>Action</th><th>Details</th><th>IP</th><th>Time</th></tr></thead>
        <tbody>
          ${logs.map(l => `<tr>
            <td class="text-muted">${l.id}</td>
            <td class="name">${escapeHtml(l.user_name || 'System')}</td>
            <td>${l.role ? roleBadge(l.role) : '—'}</td>
            <td style="color:var(--text)">${escapeHtml(l.action)}</td>
            <td style="color:var(--text-muted);font-size:11px;">${escapeHtml(l.details || '—')}</td>
            <td style="color:var(--text-muted);font-size:11px;">${escapeHtml(l.ip_address || '—')}</td>
            <td style="font-size:11px;color:var(--text-muted)">${formatDateTime(l.logged_at)}</td>
          </tr>`).join('')}
        </tbody>
      </table></div>
    </div>`;
}

async function loadProfile() {
  window._deleteAvatar = false;
  const u = await api(`users.php?id=${CURRENT_USER.id}`);
  document.getElementById('pageContent').innerHTML = `
    <div style="max-width:580px;">
      <div class="card">
        <div style="display:flex;align-items:center;gap:20px;margin-bottom:28px;padding-bottom:24px;border-bottom:1px solid var(--border);">
          <div id="pf_avatar_container" style="width:72px;height:72px;border:1px solid var(--border);border-radius:50%;overflow:hidden;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
            ${u.avatar_url
              ? `<img id="pf_avatar_img" src="${escapeHtml(u.avatar_url)}" style="width:100%;height:100%;object-fit:cover;">`
              : `<div id="pf_avatar_txt" style="width:100%;height:100%;background:var(--gold-dim);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:32px;color:var(--gold);">${escapeHtml(u.name[0].toUpperCase())}</div>`
            }
          </div>
          <div>
            <div style="font-family:'Cormorant Garamond',serif;font-size:24px;color:var(--text)">${escapeHtml(u.name)}</div>
            <div style="color:var(--text-muted);font-size:12px;">${escapeHtml(u.email)}</div>
            <div style="margin-top:6px;">${roleBadge(u.role)}</div>
            <div style="margin-top:10px;display:flex;gap:8px;">
              <input type="file" id="pf_avatar_file" accept="image/*" style="display:none;" onchange="previewAvatar(event)">
              <button class="btn btn-ghost btn-sm" onclick="document.getElementById('pf_avatar_file').click()">Upload Photo</button>
              <button class="btn btn-danger btn-sm" id="btn_delete_avatar" style="display:${u.avatar_url ? 'inline-block' : 'none'}" onclick="markDeleteAvatar()">Remove Photo</button>
            </div>
          </div>
        </div>
        <div class="form-grid">
          <div class="form-group form-col-span"><label>Full Name</label><input id="pf_name" value="${escapeHtml(u.name)}"></div>
          <div class="form-group form-col-span"><label>Email <span style="color:var(--text-muted);font-size:9px">(cannot change)</span></label><input value="${escapeHtml(u.email)}" disabled style="opacity:0.5"></div>
          <div class="form-group"><label>Phone</label><input id="pf_phone" value="${escapeHtml(u.phone || '')}"></div>
          <div class="form-group"><label>Address</label><input id="pf_addr" value="${escapeHtml(u.address || '')}"></div>
        </div>
        <div style="margin-top:24px;padding-top:20px;border-top:1px solid var(--border);">
          <div style="font-size:14px;font-weight:500;color:var(--text);margin-bottom:16px;">Change Password</div>
          <div class="form-grid">
            <div class="form-group"><label>New Password</label><input id="pf_pass" type="password" placeholder="Min 8 characters"></div>
            <div class="form-group"><label>Confirm Password</label><input id="pf_pass2" type="password" placeholder="Repeat password"></div>
          </div>
        </div>
        <div id="profileAlert"></div>
        <div class="form-actions">
          <button class="btn btn-primary" onclick="saveProfile(${u.id})">Save Changes</button>
        </div>
      </div>
    </div>`;
}

function previewAvatar(e) {
  const file = e.target.files[0];
  if (file) {
    const reader = new FileReader();
    reader.onload = function(event) {
      const container = document.getElementById('pf_avatar_container');
      container.innerHTML = `<img id="pf_avatar_img" src="${event.target.result}" style="width:100%;height:100%;object-fit:cover;">`;
      document.getElementById('btn_delete_avatar').style.display = 'inline-block';
      window._deleteAvatar = false;
    };
    reader.readAsDataURL(file);
  }
}

function markDeleteAvatar() {
  window._deleteAvatar = true;
  const container = document.getElementById('pf_avatar_container');
  const name = val('pf_name') || CURRENT_USER.name;
  container.innerHTML = `<div id="pf_avatar_txt" style="width:100%;height:100%;background:var(--gold-dim);display:flex;align-items:center;justify-content:center;font-family:'Cormorant Garamond',serif;font-size:32px;color:var(--gold);">${name[0].toUpperCase()}</div>`;
  document.getElementById('btn_delete_avatar').style.display = 'none';
  document.getElementById('pf_avatar_file').value = '';
}

async function saveProfile(id) {
  const pass = val('pf_pass');
  const pass2 = val('pf_pass2');
  const alertEl = document.getElementById('profileAlert');
  alertEl.innerHTML = '';
  if (pass) {
    if (pass.length < 8) { alertEl.innerHTML = '<div class="alert alert-error">Password must be at least 8 characters.</div>'; return; }
    if (pass !== pass2) { alertEl.innerHTML = '<div class="alert alert-error">Passwords do not match.</div>'; return; }
  }

  const fd = new FormData();
  fd.append('id', id);
  fd.append('name', val('pf_name'));
  fd.append('phone', val('pf_phone'));
  fd.append('address', val('pf_addr'));
  if (pass) fd.append('password', pass);
  if (window._deleteAvatar) fd.append('delete_avatar', '1');

  const fileInput = document.getElementById('pf_avatar_file');
  if (fileInput && fileInput.files[0]) {
    fd.append('avatar', fileInput.files[0]);
  }

  try {
    const res = await fetch('api/users.php', {
      method: 'POST',
      headers: {
        'X-CSRF-Token': window._csrfToken || ''
      },
      body: fd
    });
    if (res.status === 401) {
      window.location.href = 'index.php?route=login';
      return;
    }
    const data = await res.json();
    if (data.success) {
      alertEl.innerHTML = '<div class="alert alert-success">Profile updated successfully!</div>';
      
      // Refresh current session state in JS and update sidebar
      const sessRes = await fetch('api/session.php');
      const sessData = await sessRes.json();
      if (sessData.loggedIn) {
        CURRENT_USER = sessData.user;
        window._csrfToken = sessData.csrf_token;
        renderSidebar();
      }
      toast('Profile saved!', 'success');
      loadProfile();
    } else {
      alertEl.innerHTML = `<div class="alert alert-error">${data.error}</div>`;
    }
  } catch (e) {
    alertEl.innerHTML = '<div class="alert alert-error">Server error. Please try again.</div>';
  }
}

async function loadReviews() {
  const role = CURRENT_USER.role;
  const reviews = await api('reviews.php');

  let html = '';

  if (role === 'customer') {
    const bookings = await api('bookings.php');
    const checkedOut = bookings.filter(b => b.status === 'checked_out');
    const reviewedBookingIds = reviews.map(r => r.booking_id);
    const canReview = checkedOut.filter(b => !reviewedBookingIds.includes(b.id));

    if (canReview.length > 0) {
      html += `<div class="card" style="margin-bottom:20px;">
        <div class="card-title">Write a Review</div>
        <div class="form-grid">
          <div class="form-group form-col-span">
            <label>Select Booking</label>
            <select id="rev_booking">
              ${canReview.map(b => `<option value="${b.id}" data-room="${b.room_id}">#${b.id} — Room ${b.room_number} (${formatDate(b.check_in)} → ${formatDate(b.check_out)})</option>`).join('')}
            </select>
          </div>
          <div class="form-group form-col-span">
            <label>Rating</label>
            <div id="starRating" style="display:flex;gap:6px;margin-top:4px;">
              ${[1, 2, 3, 4, 5].map(n => `<span onclick="setRating(${n})" data-star="${n}"
                style="font-size:28px;cursor:pointer;color:var(--border);transition:color 0.15s;">★</span>`).join('')}
            </div>
          </div>
          <div class="form-group form-col-span">
            <label>Comment</label>
            <textarea id="rev_comment" placeholder="Share your experience..." style="min-height:90px;"></textarea>
          </div>
        </div>
        <div id="reviewAlert"></div>
        <div class="form-actions">
          <button class="btn btn-primary" onclick="submitReview()">Post Review</button>
        </div>
      </div>`;
    }

    const myReviews = reviews.filter(r => r.customer_id == CURRENT_USER.id);
    html += `<div class="card">
      <div class="card-title">My Reviews <span class="subtitle">${myReviews.length} review(s)</span></div>
      ${myReviews.length ? myReviews.map(r => reviewCard(r, true)).join('') : '<div class="empty-state"><p>You have not posted any reviews yet.</p></div>'}
    </div>`;

  } else {
    html += `<div class="card">
      <div class="card-title">All Customer Reviews <span class="subtitle">${reviews.length} review(s)</span></div>
      ${reviews.length ? reviews.map(r => reviewCard(r, false)).join('') : '<div class="empty-state"><p>No reviews yet.</p></div>'}
    </div>`;
  }

  document.getElementById('pageContent').innerHTML = html;
  window._selectedRating = 0;
}

function reviewCard(r, isOwn) {
  const stars = [1, 2, 3, 4, 5].map(n =>
    `<span style="color:${n <= r.rating ? '#c9a84c' : 'var(--border)'};font-size:18px;">★</span>`
  ).join('');

  return `<div style="border-bottom:1px solid var(--border2);padding:16px 0;">
    <div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:8px;">
      <div>
        <div style="font-size:13px;color:var(--text);font-weight:500;">${escapeHtml(r.customer_name || 'Customer')}</div>
        <div style="font-size:11px;color:var(--text-muted);margin-top:2px;">
          Room ${escapeHtml(r.room_number || '—')} · Booking #${r.booking_id} · ${formatDate(r.created_at)}
        </div>
      </div>
      <div style="display:flex;align-items:center;gap:10px;">
        <div>${stars}</div>
        ${isOwn ? `<button class="btn btn-ghost btn-sm" onclick="editReview(${r.id}, ${r.rating}, '${escapeHtml(r.comment || '')}')">Edit</button>` : ''}
        ${isOwn ? `<button class="btn btn-danger btn-sm" onclick="deleteReview(${r.id})">Delete</button>` : ''}
        ${CURRENT_USER.role === 'admin' ? `<button class="btn btn-danger btn-sm" onclick="deleteReview(${r.id})">Delete</button>` : ''}
      </div>
    </div>
    ${r.comment ? `<div style="font-size:12px;color:var(--text2);line-height:1.7;">${escapeHtml(r.comment)}</div>` : ''}
  </div>`;
}

function setRating(n) {
  window._selectedRating = n;
  document.querySelectorAll('#starRating span').forEach(s => {
    s.style.color = parseInt(s.dataset.star) <= n ? 'var(--gold)' : 'var(--border)';
  });
}

async function submitReview() {
  const bookingId = val('rev_booking');
  const rating = window._selectedRating || 0;
  const comment = val('rev_comment');
  const alertEl = document.getElementById('reviewAlert');
  alertEl.innerHTML = '';

  if (!rating) {
    alertEl.innerHTML = '<div class="alert alert-error">Please select a star rating.</div>'; return;
  }

  const res = await api('reviews.php', 'POST', { booking_id: bookingId, rating, comment });
  if (res.success) {
    toast('Review posted!', 'success');
    loadReviews();
  } else {
    alertEl.innerHTML = `<div class="alert alert-error">${res.error}</div>`;
  }
}

async function deleteReview(id) {
  if (!confirm('Delete this review?')) return;
  const res = await api('reviews.php', 'DELETE', { id });
  if (res.success) { toast('Review deleted', 'success'); loadReviews(); }
  else toast(res.error, 'error');
}

function openFeedbackModal(bookingId, roomNumber) {
  window._selectedRating = 0;
  openModal(`Give Feedback for Room ${escapeHtml(roomNumber)}`, `
    <div class="form-grid">
      <div class="form-group form-col-span">
        <label>Rating</label>
        <div id="starRating" style="display:flex;gap:6px;margin-top:4px;">
          ${[1, 2, 3, 4, 5].map(n => `<span onclick="setRating(${n})" data-star="${n}"
            style="font-size:28px;cursor:pointer;color:var(--border);transition:color 0.15s;">★</span>`).join('')}
        </div>
      </div>
      <div class="form-group form-col-span">
        <label>Comment</label>
        <textarea id="rev_comment" placeholder="Share your experience..." style="min-height:90px;"></textarea>
      </div>
    </div>
    <div id="reviewAlert"></div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="submitModalReview(${bookingId})">Post Feedback</button>
    </div>
  `);
}

async function submitModalReview(bookingId) {
  const rating = window._selectedRating || 0;
  const comment = val('rev_comment');
  const alertEl = document.getElementById('reviewAlert');
  if (alertEl) alertEl.innerHTML = '';

  if (!rating) {
    if (alertEl) alertEl.innerHTML = '<div class="alert alert-error">Please select a star rating.</div>';
    return;
  }

  const res = await api('reviews.php', 'POST', { booking_id: bookingId, rating, comment });
  if (res.success) {
    toast('Feedback posted! Thank you.', 'success');
    closeModal();
    loadBookings();
  } else {
    if (alertEl) alertEl.innerHTML = `<div class="alert alert-error">${res.error}</div>`;
  }
}

function editReview(reviewId, currentRating, currentComment, bookingId = null) {
  window._selectedRating = currentRating;
  openModal(`Edit Feedback`, `
    <div class="form-grid">
      <div class="form-group form-col-span">
        <label>Rating</label>
        <div id="starRating" style="display:flex;gap:6px;margin-top:4px;">
          ${[1, 2, 3, 4, 5].map(n => {
            const color = n <= currentRating ? 'var(--gold)' : 'var(--border)';
            return `<span onclick="setRating(${n})" data-star="${n}"
              style="font-size:28px;cursor:pointer;color:${color};transition:color 0.15s;">★</span>`;
          }).join('')}
        </div>
      </div>
      <div class="form-group form-col-span">
        <label>Comment</label>
        <textarea id="rev_comment" placeholder="Share your experience..." style="min-height:90px;">${escapeHtml(currentComment)}</textarea>
      </div>
    </div>
    <div id="reviewAlert"></div>
    <div class="form-actions">
      <button class="btn btn-ghost" onclick="closeModal()">Cancel</button>
      <button class="btn btn-primary" onclick="submitReviewUpdate(${reviewId}, ${bookingId})">Update Feedback</button>
    </div>
  `);
}

async function submitReviewUpdate(reviewId, bookingId) {
  const rating = window._selectedRating || 0;
  const comment = val('rev_comment');
  const alertEl = document.getElementById('reviewAlert');
  if (alertEl) alertEl.innerHTML = '';

  if (!rating) {
    if (alertEl) alertEl.innerHTML = '<div class="alert alert-error">Please select a star rating.</div>';
    return;
  }

  const res = await api('reviews.php', 'PUT', { id: reviewId, rating, comment });
  if (res.success) {
    toast('Feedback updated!', 'success');
    closeModal();
    if (bookingId) {
      openBookingDetail(bookingId);
    } else {
      loadReviews();
    }
  } else {
    if (alertEl) alertEl.innerHTML = `<div class="alert alert-error">${res.error}</div>`;
  }
}

async function deleteReviewFromModal(reviewId, bookingId) {
  if (!confirm('Delete this review?')) return;
  const res = await api('reviews.php', 'DELETE', { id: reviewId });
  if (res.success) {
    toast('Review deleted', 'success');
    closeModal();
    openBookingDetail(bookingId);
  } else {
    toast(res.error, 'error');
  }
}

async function logout() {
  if (!confirm('Sign out?')) return;
  await fetch('api/logout.php');
  window.location.href = 'index.php?route=login';
}

async function api(endpoint, method = 'GET', body = null) {
  const headers = { 'Content-Type': 'application/json' };
  if (method !== 'GET') {
    headers['X-CSRF-Token'] = window._csrfToken || '';
  }
  const opts = { method, headers };
  if (body) opts.body = JSON.stringify(body);
  const res = await fetch(`api/${endpoint}`, opts);
  if (res.status === 401) {
    window.location.href = 'index.php?route=login';
    return {};
  }
  const text = await res.text();
  try {
    return JSON.parse(text);
  } catch (e) {
    console.error('API parse error for', endpoint, ':', text);
    return { error: 'Server error: ' + text.substring(0, 200) };
  }
}

function val(id) {
  const el = document.getElementById(id);
  return el ? el.value : '';
}

function today() {
  return new Date().toISOString().split('T')[0];
}

function formatDate(d) {
  if (!d) return '—';
  return new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
}

function formatDateTime(d) {
  if (!d) return '—';
  return new Date(d).toLocaleString('en-GB', { day: '2-digit', month: 'short', year: 'numeric', hour: '2-digit', minute: '2-digit' });
}

function bookingBadge(s) {
  const map = {
    pending: 'badge-muted', confirmed: 'badge-blue',
    checked_in: 'badge-green', checked_out: 'badge-gold', cancelled: 'badge-red'
  };
  return `<span class="badge ${map[s] || 'badge-muted'}">${s.replace('_', ' ')}</span>`;
}

function roomStatusBadge(s) {
  const map = { available: 'badge-green', occupied: 'badge-orange', maintenance: 'badge-red' };
  return `<span class="badge ${map[s] || 'badge-muted'}">${s}</span>`;
}

function serviceStatusBadge(s) {
  const map = { pending: 'badge-orange', in_progress: 'badge-blue', completed: 'badge-green', cancelled: 'badge-red' };
  return `<span class="badge ${map[s] || 'badge-muted'}">${s.replace('_', ' ')}</span>`;
}

function roleBadge(r) {
  const map = { admin: 'badge-gold', staff: 'badge-blue', customer: 'badge-green' };
  return `<span class="badge ${map[r] || 'badge-muted'}">${r}</span>`;
}

function openModal(title, body) {
  document.getElementById('modalTitle').textContent = title;
  document.getElementById('modalBody').innerHTML = body;
  document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() {
  document.getElementById('modalOverlay').classList.remove('open');
}

function closeModalOnBg(e) {
  if (e.target === document.getElementById('modalOverlay')) closeModal();
}

function toast(msg, type = 'info') {
  const container = document.getElementById('toastContainer');
  const t = document.createElement('div');
  t.className = `toast ${type}`;
  const icons_map = { success: '✓', error: '✕', info: 'ℹ' };
  t.innerHTML = `<span style="color:${type === 'success' ? 'var(--green)' : type === 'error' ? 'var(--red)' : 'var(--blue)'}">${icons_map[type]}</span> ${msg}`;
  container.appendChild(t);
  setTimeout(() => t.remove(), 3500);
}

async function loadTimeline() {
  if (!window._timelineMonth && window._timelineMonth !== 0) {
    const d = new Date();
    window._timelineYear = d.getFullYear();
    window._timelineMonth = d.getMonth();
  }

  const rooms = await api('rooms.php');
  const bookings = await api('bookings.php');

  const year = window._timelineYear;
  const month = window._timelineMonth;
  const dateObj = new Date(year, month, 1);
  const monthName = dateObj.toLocaleString('en-GB', { month: 'long', year: 'numeric' });
  const totalDays = new Date(year, month + 1, 0).getDate();

  let html = `
    <div class="card" style="padding:16px; margin-bottom:20px; overflow-x: auto;">
      <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
        <div style="font-family:'Cormorant Garamond',serif; font-size:22px; color:var(--text);">${monthName}</div>
        <div style="display:flex; gap:8px;">
          <button class="btn btn-ghost btn-sm" onclick="changeTimelineMonth(-1)">◀ Prev</button>
          <button class="btn btn-ghost btn-sm" onclick="changeTimelineMonth(1)">Next ▶</button>
        </div>
      </div>
      
      <div class="table-wrap">
        <table class="timeline-table" style="border-collapse:collapse; min-width:800px; width:100%;">
          <thead>
            <tr>
              <th style="width:100px; text-align:left; border: 1px solid var(--border); padding:8px; position:sticky; left:0; background:var(--dark2); z-index:10;">Room</th>
              ${Array.from({ length: totalDays }, (_, i) => {
                const day = i + 1;
                const isToday = new Date().getDate() === day && new Date().getMonth() === month && new Date().getFullYear() === year;
                return `<th style="text-align:center; border: 1px solid var(--border); padding:8px; width:30px; font-size:10px; background:${isToday ? 'var(--gold-dim)' : 'transparent'}; color:${isToday ? 'var(--gold)' : 'inherit'};">${day}</th>`;
              }).join('')}
            </tr>
          </thead>
          <tbody>
  `;

  rooms.forEach(r => {
    html += `
      <tr>
        <td style="font-weight:500; border: 1px solid var(--border); padding:8px; position:sticky; left:0; background:var(--dark2); z-index:10; font-size:12px;">
          Room ${escapeHtml(r.room_number)} <span style="font-size:9px; color:var(--text-muted); display:block;">${escapeHtml(r.room_type)}</span>
        </td>
    `;

    for (let day = 1; day <= totalDays; day++) {
      const cellDateStr = `${year}-${String(month + 1).padStart(2, '0')}-${String(day).padStart(2, '0')}`;
      const cellDate = new Date(cellDateStr);

      const b = bookings.find(book => {
        if (Number(book.room_id) !== Number(r.id)) return false;
        if (book.status === 'cancelled') return false;
        const ci = new Date(book.check_in);
        const co = new Date(book.check_out);
        return cellDate >= ci && cellDate < co;
      });

      if (b) {
        let color = 'var(--gold)';
        if (b.status === 'checked_in') color = 'var(--green)';
        if (b.status === 'checked_out') color = 'var(--text-muted)';
        if (b.status === 'pending') color = 'var(--orange)';
        
        html += `
          <td style="border: 1px solid var(--border); padding:0; text-align:center; cursor:pointer; background:${color}33;" onclick="openBookingDetail(${b.id})" title="Booking #${b.id} - ${escapeHtml(b.customer_name)}">
            <div style="width:100%; height:20px; background:${color}; opacity:0.8; border-radius:2px;" title="Booking #${b.id}"></div>
          </td>
        `;
      } else {
        const isToday = new Date().getDate() === day && new Date().getMonth() === month && new Date().getFullYear() === year;
        html += `<td style="border: 1px solid var(--border); padding:8px; background:${isToday ? 'var(--gold-dim)' : 'transparent'};"></td>`;
      }
    }
    html += `</tr>`;
  });

  html += `
          </tbody>
        </table>
      </div>
    </div>
  `;

  document.getElementById('pageContent').innerHTML = html;
}

function changeTimelineMonth(offset) {
  window._timelineMonth += offset;
  if (window._timelineMonth > 11) {
    window._timelineMonth = 0;
    window._timelineYear++;
  } else if (window._timelineMonth < 0) {
    window._timelineMonth = 11;
    window._timelineYear--;
  }
  loadTimeline();
}

init();
