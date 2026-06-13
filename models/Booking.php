<?php
class Booking extends BaseModel {
    public function findById($id) {
        $stmt = $this->db->prepare("
            SELECT b.*, r.room_number, r.room_type, r.price_per_night,
                   u.name as customer_name, u.email as customer_email, u.phone as customer_phone
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            JOIN users u ON b.customer_id = u.id
            WHERE b.id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByCustomer($customerId) {
        $stmt = $this->db->prepare("
            SELECT b.*, r.room_number, r.room_type, r.price_per_night,
                   p.payment_status, p.payment_method, p.amount as paid_amount
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            LEFT JOIN payments p ON p.booking_id = b.id
            WHERE b.customer_id = ? ORDER BY b.booked_at DESC");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAll($customerId = null) {
        $where = '';
        if ($customerId) {
            $where = "WHERE b.customer_id = " . intval($customerId);
        }
        $result = $this->db->query("
            SELECT b.*, r.room_number, r.room_type, r.price_per_night,
                   u.name as customer_name, u.email as customer_email
            FROM bookings b
            JOIN rooms r ON b.room_id = r.id
            JOIN users u ON b.customer_id = u.id
            $where ORDER BY b.booked_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function checkOverlap($roomId, $checkIn, $checkOut) {
        $stmt = $this->db->prepare("
            SELECT id FROM bookings
            WHERE room_id = ? AND status NOT IN ('cancelled','checked_out')
            AND check_in < ? AND check_out > ?");
        $stmt->bind_param("iss", $roomId, $checkOut, $checkIn);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function create($customerId, $roomId, $checkIn, $checkOut, $totalPrice, $requests) {
        $stmt = $this->db->prepare("INSERT INTO bookings (customer_id, room_id, check_in, check_out, total_price, status, special_requests) VALUES (?,?,?,?,?,'pending',?)");
        $stmt->bind_param("iissds", $customerId, $roomId, $checkIn, $checkOut, $totalPrice, $requests);
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function update($id, $status, $requests) {
        $stmt = $this->db->prepare("UPDATE bookings SET status=?, special_requests=? WHERE id=?");
        $stmt->bind_param("ssi", $status, $requests, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM bookings WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
