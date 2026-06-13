<?php
class Room extends BaseModel {
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function getByStatus($status) {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE status = ? ORDER BY room_number");
        $stmt->bind_param("s", $status);
        $stmt->execute();
        return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }

    public function getAll() {
        $result = $this->db->query("SELECT * FROM rooms ORDER BY room_number");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function create($room_number, $room_type, $price, $capacity, $description, $amenities, $status, $floor) {
        $stmt = $this->db->prepare("INSERT INTO rooms (room_number, room_type, price_per_night, capacity, description, amenities, status, floor) VALUES (?,?,?,?,?,?,?,?)");
        $stmt->bind_param("ssdisssi", $room_number, $room_type, $price, $capacity, $description, $amenities, $status, $floor);
        if ($stmt->execute()) {
            return $this->db->insert_id;
        }
        return false;
    }

    public function update($id, $room_type, $price, $capacity, $description, $amenities, $status, $floor) {
        $stmt = $this->db->prepare("UPDATE rooms SET room_type=?, price_per_night=?, capacity=?, description=?, amenities=?, status=?, floor=? WHERE id=?");
        $stmt->bind_param("sdiisssi", $room_type, $price, $capacity, $description, $amenities, $status, $floor, $id);
        return $stmt->execute();
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE rooms SET status=? WHERE id=?");
        $stmt->bind_param("si", $status, $id);
        return $stmt->execute();
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM rooms WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }
}
