<?php
class Service extends BaseModel {
    public function getServices() {
        $stmt = $this->db->query("SELECT * FROM services ORDER BY category, name");
        return $stmt->fetchAll();
    }

    public function getRequestsByCustomer($customerId) {
        $stmt = $this->db->prepare("SELECT sr.*, s.name as service_name, s.price, s.category FROM service_requests sr JOIN services s ON sr.service_id = s.id JOIN bookings b ON sr.booking_id = b.id WHERE b.customer_id = ?");
        $stmt->execute([$customerId]);
        return $stmt->fetchAll();
    }

    public function getRequestsAll($bookingId = null) {
        if ($bookingId) {
            $stmt = $this->db->prepare("SELECT sr.*, s.name as service_name, s.price, s.category, u.name as customer_name FROM service_requests sr JOIN services s ON sr.service_id = s.id JOIN bookings b ON sr.booking_id = b.id JOIN users u ON b.customer_id = u.id WHERE sr.booking_id = ? ORDER BY sr.requested_at DESC");
            $stmt->execute([intval($bookingId)]);
        } else {
            $stmt = $this->db->query("SELECT sr.*, s.name as service_name, s.price, s.category, u.name as customer_name FROM service_requests sr JOIN services s ON sr.service_id = s.id JOIN bookings b ON sr.booking_id = b.id JOIN users u ON b.customer_id = u.id ORDER BY sr.requested_at DESC");
        }
        return $stmt->fetchAll();
    }

    public function createService($name, $description, $price, $category) {
        $stmt = $this->db->prepare("INSERT INTO services (name, description, price, category) VALUES (?,?,?,?)");
        if ($stmt->execute([$name, $description, $price, $category])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function createRequest($bookingId, $serviceId, $quantity, $notes) {
        $stmt = $this->db->prepare("INSERT INTO service_requests (booking_id, service_id, quantity, notes) VALUES (?,?,?,?)");
        if ($stmt->execute([$bookingId, $serviceId, $quantity, $notes])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function updateRequestStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE service_requests SET status=? WHERE id=?");
        return $stmt->execute([$status, $id]);
    }
}

