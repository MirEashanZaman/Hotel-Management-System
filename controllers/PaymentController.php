<?php
class PaymentController extends BaseController {
    private $paymentModel;
    private $bookingModel;

    public function __construct() {
        requireLogin();
        $this->paymentModel = new Payment();
        $this->bookingModel = new Booking();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $me = $this->me();

        if ($method === 'GET') {
            $bookingId = $_GET['booking_id'] ?? null;
            if ($bookingId) {
                $payments = $this->paymentModel->getByBookingId($bookingId);
                if ($me['role'] === 'customer' && count($payments) && $payments[0]['customer_id'] != $me['id']) {
                    $this->jsonResponse(['error' => 'Access denied'], 403);
                }
                $this->jsonResponse($payments);
            } else {
                if ($me['role'] === 'customer') {
                    $this->jsonResponse($this->paymentModel->getByCustomer($me['id']));
                } else {
                    $this->jsonResponse($this->paymentModel->getAll());
                }
            }
        }

        if ($method === 'POST') {
            $this->checkCsrf();
            $data          = $this->getInput();
            $bookingId     = intval($data['booking_id']     ?? 0);
            $amount        = floatval($data['amount']        ?? 0);
            $paymentMethod = $data['payment_method']         ?? 'cash';
            $txId          = trim($data['transaction_id']    ?? '');

            $booking = $this->bookingModel->findById($bookingId);
            if (!$booking) {
                $this->jsonResponse(['error' => 'Booking not found']);
            }

            if ($me['role'] === 'customer' && $booking['customer_id'] != $me['id']) {
                $this->jsonResponse(['error' => 'Access denied'], 403);
            }

            if (!$amount) {
                $amount = $booking['total_price'];
            }

            $paymentStatus = ($paymentMethod === 'card') ? 'paid' : 'pending';
            $paidAt        = ($paymentMethod === 'card') ? date('Y-m-d H:i:s') : null;

            $existing = $this->paymentModel->getExistingByBooking($bookingId);

            if ($existing) {
                $this->paymentModel->updateByBooking($bookingId, $paymentMethod, $paymentStatus, $txId, $paidAt);
            } else {
                $this->paymentModel->insert($bookingId, $amount, $paymentMethod, $paymentStatus, $txId, $paidAt);
            }

            if ($paymentMethod === 'card') {
                $this->bookingModel->update($bookingId, 'confirmed', $booking['special_requests']);
                $this->logActivity($me['id'], 'Payment', "Booking #$bookingId paid by card — auto confirmed");
                $this->jsonResponse(['success' => true, 'status' => 'confirmed', 'message' => 'Card payment successful. Booking confirmed!']);
            } else {
                $this->bookingModel->update($bookingId, 'pending', $booking['special_requests']);
                $this->logActivity($me['id'], 'Payment', "Booking #$bookingId cash payment pending — awaiting staff confirmation");
                $this->jsonResponse(['success' => true, 'status' => 'pending', 'message' => 'Cash payment registered. Please pay at the front desk. Your booking will be confirmed by staff.']);
            }
        }

        if ($method === 'PUT') {
            $this->checkCsrf();
            requireRole(['admin', 'staff']);
            $data      = $this->getInput();
            $id        = intval($data['id'] ?? 0);
            $paidAt    = date('Y-m-d H:i:s');

            $payment = $this->paymentModel->findById($id);
            if (!$payment) {
                $this->jsonResponse(['error' => 'Payment not found']);
            }

            if ($this->paymentModel->confirmCash($id, $paidAt)) {
                $bookingId = $payment['booking_id'];
                $booking = $this->bookingModel->findById($bookingId);
                $this->bookingModel->update($bookingId, 'confirmed', $booking['special_requests'] ?? '');
                $this->logActivity($me['id'], 'Confirm Cash Payment', "Staff confirmed cash payment #$id — booking #$bookingId is now confirmed");
                $this->jsonResponse(['success' => true, 'message' => 'Cash payment confirmed. Booking is now confirmed.']);
            } else {
                $this->jsonResponse(['error' => 'Update failed']);
            }
        }

        $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }
}
