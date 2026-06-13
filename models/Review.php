<?php
class Review extends BaseModel {
    public function getByBooking($bookingId) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as customer_name
            FROM reviews r
            JOIN users u ON r.customer_id = u.id
            WHERE r.booking_id = ?");
        $stmt->execute([$bookingId]);
        return $stmt->fetchAll();
    }

    public function getByRoom($roomId) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as customer_name
            FROM reviews r
            JOIN users u ON r.customer_id = u.id
            WHERE r.room_id = ?
            ORDER BY r.created_at DESC");
        $stmt->execute([$roomId]);
        return $stmt->fetchAll();
    }

    public function getAll() {
        $stmt = $this->db->query("
            SELECT r.*, u.name as customer_name, ro.room_number, ro.room_type
            FROM reviews r
            JOIN users u ON r.customer_id = u.id
            JOIN rooms ro ON r.room_id = ro.id
            ORDER BY r.created_at DESC");
        return $stmt->fetchAll();
    }

    public function checkExists($bookingId, $customerId) {
        $stmt = $this->db->prepare("SELECT id FROM reviews WHERE booking_id = ? AND customer_id = ?");
        $stmt->execute([$bookingId, $customerId]);
        return $stmt->fetch() !== false;
    }

    public function create($customerId, $bookingId, $roomId, $rating, $comment) {
        $stmt = $this->db->prepare("INSERT INTO reviews (customer_id, booking_id, room_id, rating, comment) VALUES (?,?,?,?,?)");
        return $stmt->execute([$customerId, $bookingId, $roomId, $rating, $comment]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->rowCount() > 0;
    }

    public function deleteByCustomer($id, $customerId) {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ? AND customer_id = ?");
        $stmt->execute([$id, $customerId]);
        return $stmt->rowCount() > 0;
    }
}

