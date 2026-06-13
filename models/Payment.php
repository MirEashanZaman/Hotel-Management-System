<?php
class Payment extends BaseModel {
    public function getByBookingId($bookingId) {
        $stmt = $this->db->prepare("SELECT p.*, b.customer_id FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE p.booking_id = ?");
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll();
    }

    public function getByCustomer($customerId) {
        $stmt = $this->db->prepare("
            SELECT p.*, r.room_number, b.check_in, b.check_out, b.status as booking_status
            FROM payments p
            JOIN bookings b ON p.booking_id = b.id
            JOIN rooms r ON b.room_id = r.id
            WHERE b.customer_id = ?
            ORDER BY p.created_at DESC");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    public function getAll() {
        $result = $this->db->query("
            SELECT p.*, b.customer_id, b.status as booking_status,
                   u.name as customer_name, r.room_number
            FROM payments p
            JOIN bookings b ON p.booking_id = b.id
            JOIN users u ON b.customer_id = u.id
            JOIN rooms r ON b.room_id = r.id
            ORDER BY p.created_at DESC");
        return $result->fetchAll();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getExistingByBooking($bookingId) {
        $stmt = $this->db->prepare("SELECT id, payment_status FROM payments WHERE booking_id = ?");
        $stmt->execute([$bookingId]);
        return $stmt->fetch();
    }

    public function insert($bookingId, $amount, $paymentMethod, $paymentStatus, $transactionId, $paidAt) {
        $stmt = $this->db->prepare("INSERT INTO payments (booking_id, amount, payment_method, payment_status, transaction_id, paid_at) VALUES (?,?,?,?,?,?)");
        return $stmt->execute([$bookingId, $amount, $paymentMethod, $paymentStatus, $transactionId, $paidAt]);
    }

    public function updateByBooking($bookingId, $paymentMethod, $paymentStatus, $transactionId, $paidAt) {
        $stmt = $this->db->prepare("UPDATE payments SET payment_method=?, payment_status=?, transaction_id=?, paid_at=? WHERE booking_id=?");
        return $stmt->execute([$paymentMethod, $paymentStatus, $transactionId, $paidAt, $bookingId]);
    }

    public function confirmCash($id, $paidAt) {
        $stmt = $this->db->prepare("UPDATE payments SET payment_status='paid', paid_at=? WHERE id=?");
        return $stmt->execute([$paidAt, $id]);
    }
}
