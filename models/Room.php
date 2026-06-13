<?php
class Room extends BaseModel {
    public function findById($id) {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function getByStatus($status) {
        $stmt = $this->db->prepare("SELECT * FROM rooms WHERE status = ? ORDER BY room_number");
        $stmt->execute([$status]);
        return $stmt->fetchAll();
    }

    public function getAll() {
        $result = $this->db->query("SELECT * FROM rooms ORDER BY room_number");
        return $result->fetchAll();
    }

    public function create($room_number, $room_type, $price, $capacity, $description, $amenities, $status, $floor, $image_url = null) {
        $stmt = $this->db->prepare("INSERT INTO rooms (room_number, room_type, price_per_night, capacity, description, amenities, status, floor, image_url) VALUES (?,?,?,?,?,?,?,?,?)");
        if ($stmt->execute([$room_number, $room_type, $price, $capacity, $description, $amenities, $status, $floor, $image_url])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $room_type, $price, $capacity, $description, $amenities, $status, $floor, $image_url = null) {
        if ($image_url) {
            $stmt = $this->db->prepare("UPDATE rooms SET room_type=?, price_per_night=?, capacity=?, description=?, amenities=?, status=?, floor=?, image_url=? WHERE id=?");
            return $stmt->execute([$room_type, $price, $capacity, $description, $amenities, $status, $floor, $image_url, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE rooms SET room_type=?, price_per_night=?, capacity=?, description=?, amenities=?, status=?, floor=? WHERE id=?");
            return $stmt->execute([$room_type, $price, $capacity, $description, $amenities, $status, $floor, $id]);
        }
    }

    public function updateStatus($id, $status) {
        $stmt = $this->db->prepare("UPDATE rooms SET status=? WHERE id=?");
        return $stmt->execute([$status, $id]);
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM rooms WHERE id = ?");
        return $stmt->execute([$id]);
    }
}
