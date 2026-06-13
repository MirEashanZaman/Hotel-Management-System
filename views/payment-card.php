<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Card Payment — Hotel Management System</title>
  <link rel="stylesheet" href="css/payment.css">
</head>

<body>
  <div class="wrap">

    <div class="page-header">
      <button class="back-btn" onclick="goBack()">← Back</button>
      <div>
        <h1>Card Payment</h1>
        <p>Secure payment gateway</p>
      </div>
    </div>

    <div class="summary" id="summaryBox">
      <div>
        <div class="summary-label">Booking</div>
        <div class="summary-value" id="summaryBooking">—</div>
        <div class="summary-value" id="summaryRoom" style="margin-top:3px;font-size:12px;color:var(--text-muted)">—
        </div>
      </div>
      <div style="text-align:right;">
        <div class="summary-label">Total Amount</div>
        <div class="summary-amount" id="summaryAmount">৳0</div>
      </div>
    </div>

    <div class="card-visual" id="cardVisual">
      <div class="card-chip"></div>
      <div class="card-number-display" id="displayNumber">•••• •••• •••• ••••</div>
      <div class="card-bottom">
        <div>
          <div class="card-bottom-label">Card Holder</div>
          <div class="card-bottom-value" id="displayName">YOUR NAME</div>
        </div>
        <div>
          <div class="card-bottom-label">Expires</div>
          <div class="card-bottom-value" id="displayExpiry">MM/YY</div>
        </div>
        <div class="card-logo">💳</div>
      </div>
    </div>

    <div class="card-form" id="cardForm">
      <div class="error-msg" id="errorMsg"></div>

      <div class="form-group">
        <label>Card Number</label>
        <input type="text" id="cardNumber" placeholder="1234 5678 9012 3456" maxlength="19"
          oninput="formatCardNumber(this)" autocomplete="cc-number">
      </div>

      <div class="form-group">
        <label>Card Holder Name</label>
        <input type="text" id="cardName" placeholder="Name on card" oninput="updateDisplay()" autocomplete="cc-name">
      </div>

      <div class="form-row">
        <div class="form-group">
          <label>Expiry Date</label>
          <input type="text" id="cardExpiry" placeholder="MM/YY" maxlength="5" oninput="formatExpiry(this)"
            autocomplete="cc-exp">
        </div>
        <div class="form-group">
          <label>CVV</label>
          <input type="password" id="cardCvv" placeholder="•••" maxlength="4" autocomplete="cc-csc">
        </div>
      </div>

      <div class="secure-note">
        <span>🔒</span> Your payment is secured with 256-bit SSL encryption
      </div>

      <button class="btn-pay" id="payBtn" onclick="processPayment()">
        Pay <span id="btnAmount">৳0</span>
      </button>
    </div>

    <div class="processing" id="processing">
      <div class="spinner"></div>
      <h3>Processing Payment...</h3>
      <p>Please do not close this window</p>
    </div>

    <div class="success-screen" id="successScreen">
      <div class="success-icon">✓</div>
      <h3>Payment Successful!</h3>
      <div class="success-amount" id="successAmount">৳0</div>
      <p>Your booking has been <strong style="color:var(--green)">confirmed</strong>.</p>
      <p id="successBooking" style="margin-top:6px;"></p>
      <button class="btn-done" onclick="window.location.href='index.php?route=dashboard'">Go to Dashboard</button>
    </div>

  </div>

  <script>
    const params = new URLSearchParams(window.location.search);
    const bookingId = params.get('booking_id');
    const amount = parseFloat(params.get('amount') || 0);
    const roomNum = params.get('room') || '—';
    const checkIn = params.get('checkin') || '';
    const checkOut = params.get('checkout') || '';

    if (!bookingId || !amount) {
      window.location.href = 'index.php?route=dashboard';
    }

    document.getElementById('summaryBooking').textContent = `Booking #${bookingId}`;
    document.getElementById('summaryRoom').textContent = `Room ${roomNum} · ${checkIn} → ${checkOut}`;
    document.getElementById('summaryAmount').textContent = `৳${Number(amount).toLocaleString()}`;
    document.getElementById('btnAmount').textContent = `৳${Number(amount).toLocaleString()}`;
    document.getElementById('successAmount').textContent = `৳${Number(amount).toLocaleString()}`;
    document.getElementById('successBooking').textContent = `Booking #${bookingId} · Room ${roomNum}`;

    function goBack() {
      window.location.href = 'index.php?route=dashboard';
    }

    function formatCardNumber(input) {
      let v = input.value.replace(/\D/g, '').substring(0, 16);
      input.value = v.replace(/(.{4})/g, '$1 ').trim();
      updateDisplay();
    }

    function formatExpiry(input) {
      let v = input.value.replace(/\D/g, '').substring(0, 4);
      if (v.length >= 2) v = v.substring(0, 2) + '/' + v.substring(2);
      input.value = v;
      updateDisplay();
    }

    function updateDisplay() {
      const num = document.getElementById('cardNumber').value || '•••• •••• •••• ••••';
      const name = document.getElementById('cardName').value || 'YOUR NAME';
      const exp = document.getElementById('cardExpiry').value || 'MM/YY';

      const parts = num.split(' ');
      const masked = parts.map((p, i) => (i === 1 || i === 2) ? '••••' : p).join(' ');
      document.getElementById('displayNumber').textContent = masked || '•••• •••• •••• ••••';
      document.getElementById('displayName').textContent = name.toUpperCase().substring(0, 22);
      document.getElementById('displayExpiry').textContent = exp;
    }

    function validate() {
      const num = document.getElementById('cardNumber').value.replace(/\s/g, '');
      const name = document.getElementById('cardName').value.trim();
      const exp = document.getElementById('cardExpiry').value;
      const cvv = document.getElementById('cardCvv').value;
      const err = document.getElementById('errorMsg');

      err.classList.remove('show');

      if (num.length !== 16) { err.textContent = 'Please enter a valid 16-digit card number.'; err.classList.add('show'); return false; }
      if (!name) { err.textContent = 'Please enter the card holder name.'; err.classList.add('show'); return false; }
      if (!/^\d{2}\/\d{2}$/.test(exp)) { err.textContent = 'Please enter a valid expiry date (MM/YY).'; err.classList.add('show'); return false; }

      const [mm, yy] = exp.split('/').map(Number);
      const now = new Date();
      const expDate = new Date(2000 + yy, mm - 1);
      if (expDate < new Date(now.getFullYear(), now.getMonth())) {
        err.textContent = 'This card has expired.'; err.classList.add('show'); return false;
      }

      if (cvv.length < 3) { err.textContent = 'Please enter a valid CVV.'; err.classList.add('show'); return false; }

      return true;
    }

    async function processPayment() {
      if (!validate()) return;

      const btn = document.getElementById('payBtn');
      btn.disabled = true;

      document.getElementById('cardForm').classList.add('hide');
      document.getElementById('cardVisual').style.display = 'none';
      document.getElementById('summaryBox').style.display = 'none';
      document.getElementById('processing').classList.add('show');

      await new Promise(r => setTimeout(r, 2200));

      try {
        const res = await fetch('api/payments.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify({
            booking_id: parseInt(bookingId),
            amount: amount,
            payment_method: 'card',
            transaction_id: 'CARD-' + Date.now()
          })
        });
        const data = await res.json();

        document.getElementById('processing').classList.remove('show');

        if (data.success) {
          document.getElementById('successScreen').classList.add('show');
        } else {
          document.getElementById('cardForm').classList.remove('hide');
          document.getElementById('cardVisual').style.display = '';
          document.getElementById('summaryBox').style.display = '';
          const err = document.getElementById('errorMsg');
          err.textContent = data.error || 'Payment failed. Please try again.';
          err.classList.add('show');
          btn.disabled = false;
        }
      } catch {
        document.getElementById('processing').classList.remove('show');
        document.getElementById('cardForm').classList.remove('hide');
        document.getElementById('cardVisual').style.display = '';
        document.getElementById('summaryBox').style.display = '';
        const err = document.getElementById('errorMsg');
        err.textContent = 'Connection error. Please try again.';
        err.classList.add('show');
        btn.disabled = false;
      }
    }
  </script>
</body>

</html>
