<?php
class Review extends BaseModel {
    public function getByBooking($bookingId) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as customer_name
            FROM reviews r
            JOIN users u ON r.customer_id = u.id
            WHERE r.booking_id = ?");
        $stmt->bind_param("i", $bookingId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getByRoom($roomId) {
        $stmt = $this->db->prepare("
            SELECT r.*, u.name as customer_name
            FROM reviews r
            JOIN users u ON r.customer_id = u.id
            WHERE r.room_id = ?
            ORDER BY r.created_at DESC");
        $stmt->bind_param("i", $roomId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAll() {
        $result = $this->db->query("
            SELECT r.*, u.name as customer_name, ro.room_number, ro.room_type
            FROM reviews r
            JOIN users u ON r.customer_id = u.id
            JOIN rooms ro ON r.room_id = ro.id
            ORDER BY r.created_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function checkExists($bookingId, $customerId) {
        $stmt = $this->db->prepare("SELECT id FROM reviews WHERE booking_id = ? AND customer_id = ?");
        $stmt->bind_param("ii", $bookingId, $customerId);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function create($customerId, $bookingId, $roomId, $rating, $comment) {
        $stmt = $this->db->prepare("INSERT INTO reviews (customer_id, booking_id, room_id, rating, comment) VALUES (?,?,?,?,?)");
        $stmt->bind_param("iiiis", $customerId, $bookingId, $roomId, $rating, $comment);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }

    public function deleteByCustomer($id, $customerId) {
        $stmt = $this->db->prepare("DELETE FROM reviews WHERE id = ? AND customer_id = ?");
        $stmt->bind_param("ii", $id, $customerId);
        return $stmt->execute() && $stmt->affected_rows > 0;
    }
}
