<?php
class Payment extends BaseModel {
    public function getByBookingId($bookingId) {
        $stmt = $this->db->prepare("SELECT p.*, b.customer_id FROM payments p JOIN bookings b ON p.booking_id = b.id WHERE p.booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByCustomer($customerId) {
        $stmt = $this->db->prepare("
            SELECT p.*, r.room_number, b.check_in, b.check_out, b.status as booking_status
            FROM payments p
            JOIN bookings b ON p.booking_id = b.id
            JOIN rooms r ON b.room_id = r.id
            WHERE b.customer_id = ?
            ORDER BY p.created_at DESC");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM payments WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getExistingByBooking($bookingId) {
        $stmt = $this->db->prepare("SELECT id, payment_status FROM payments WHERE booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function insert($bookingId, $amount, $paymentMethod, $paymentStatus, $transactionId, $paidAt) {
        $stmt = $this->db->prepare("INSERT INTO payments (booking_id, amount, payment_method, payment_status, transaction_id, paid_at) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("idssss", $bookingId, $amount, $paymentMethod, $paymentStatus, $transactionId, $paidAt);
        return $stmt->execute();
    }

    public function updateByBooking($bookingId, $paymentMethod, $paymentStatus, $transactionId, $paidAt) {
        $stmt = $this->db->prepare("UPDATE payments SET payment_method=?, payment_status=?, transaction_id=?, paid_at=? WHERE booking_id=?");
        $stmt->bind_param("ssssi", $paymentMethod, $paymentStatus, $transactionId, $paidAt, $bookingId);
        return $stmt->execute();
    }

    public function confirmCash($id, $paidAt) {
        $stmt = $this->db->prepare("UPDATE payments SET payment_status='paid', paid_at=? WHERE id=?");
        $stmt->bind_param("si", $paidAt, $id);
        return $stmt->execute();
    }
}
