<?php
class Service extends BaseModel {
    public function getServices() {
        $result = $this->db->query("SELECT * FROM services ORDER BY category, name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getRequestsByCustomer($customerId) {
        $stmt = $this->db->prepare("SELECT sr.*, s.name as service_name, s.price, s.category FROM service_requests sr JOIN services s ON sr.service_id = s.id JOIN bookings b ON sr.booking_id = b.id WHERE b.customer_id = ?");
        $stmt->bind_param("i", $customerId);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getRequestsAll($bookingId = null) {
        $where = $bookingId ? "WHERE sr.booking_id = " . intval($bookingId) : '';
        $result = $this->db->query("SELECT sr.*, s.name as service_name, s.price, s.category, u.name as customer_name FROM service_requests sr JOIN services s ON sr.service_id = s.id JOIN bookings b ON sr.booking_id = b.id JOIN users u ON b.customer_id = u.id $where ORDER BY sr.requested_at DESC");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function createService($name, $description, $price, $category) {
        $stmt = $this->db->prepare("INSERT INTO services (name, description, price, category) VALUES (?,?,?,?)");
        $stmt->bind_param("ssds", $name, $description, $price, $category);
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function createRequest($bookingId, $serviceId, $quantity, $notes) {
        $stmt = $this->db->prepare("INSERT INTO service_requests (booking_id, service_id, quantity, notes) VALUES (?,?,?,?)");
        $stmt->bind_param("iiis", $bookingId, $serviceId, $quantity, $notes);
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function updateRequestStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE service_requests SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }
}
