<?php
class RoomController extends BaseController {
    private $roomModel;

    public function __construct() {
        requireLogin();
        $this->roomModel = new Room();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $me = $this->me();

        if ($method === 'GET') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                $room = $this->roomModel->findById($id);
                $this->jsonResponse($room ?: ['error' => 'Room not found']);
            } else {
                $status = $_GET['status'] ?? null;
                if ($status) {
                    $this->jsonResponse($this->roomModel->getByStatus($status));
                } else {
                    $this->jsonResponse($this->roomModel->getAll());
                }
            }
        }

        if ($method === 'POST') {
            requireRole(['admin', 'staff']);
            $data = $this->getInput();
            $room_number = trim($data['room_number'] ?? '');
            $room_type   = $data['room_type'] ?? 'Single';
            $price       = floatval($data['price_per_night'] ?? 0);
            $capacity    = intval($data['capacity'] ?? 1);
            $description = trim($data['description'] ?? '');
            $amenities   = trim($data['amenities'] ?? '');
            $status      = $data['status'] ?? 'available';
            $floor       = intval($data['floor'] ?? 1);

            if (!$room_number || !$price) {
                $this->jsonResponse(['error' => 'Room number and price required']);
            }

            $newId = $this->roomModel->create($room_number, $room_type, $price, $capacity, $description, $amenities, $status, $floor);
            if ($newId) {
                $this->logActivity($me['id'], 'Add Room', "Added room $room_number");
                $this->jsonResponse(['success' => true, 'id' => $newId]);
            } else {
                $this->jsonResponse(['error' => 'Room number already exists or insert failed']);
            }
        }

        if ($method === 'PUT') {
            requireRole(['admin', 'staff']);
            $data = $this->getInput();
            $id          = intval($data['id'] ?? 0);
            $room_type   = $data['room_type'] ?? 'Single';
            $price       = floatval($data['price_per_night'] ?? 0);
            $capacity    = intval($data['capacity'] ?? 1);
            $description = trim($data['description'] ?? '');
            $amenities   = trim($data['amenities'] ?? '');
            $status      = $data['status'] ?? 'available';
            $floor       = intval($data['floor'] ?? 1);

            if ($this->roomModel->update($id, $room_type, $price, $capacity, $description, $amenities, $status, $floor)) {
                $this->logActivity($me['id'], 'Update Room', "Updated room ID $id");
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Update failed']);
            }
        }

        if ($method === 'DELETE') {
            requireRole('admin');
            $data = $this->getInput();
            $id = intval($data['id'] ?? 0);
            if ($this->roomModel->delete($id)) {
                $this->logActivity($me['id'], 'Delete Room', "Deleted room ID $id");
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Delete failed']);
            }
        }

        $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }
}
