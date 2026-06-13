<?php
class BookingController extends BaseController {
    private $bookingModel;
    private $roomModel;

    public function __construct() {
        requireLogin();
        $this->bookingModel = new Booking();
        $this->roomModel = new Room();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $me = $this->me();

        if ($method === 'GET') {
            $id = $_GET['id'] ?? null;
            $customerId = $_GET['customer_id'] ?? null;

            if ($id) {
                $booking = $this->bookingModel->findById($id);
                if (!$booking) {
                    $this->jsonResponse(['error' => 'Booking not found']);
                }
                if ($me['role'] === 'customer' && $booking['customer_id'] != $me['id']) {
                    $this->jsonResponse(['error' => 'Access denied'], 403);
                }
                $this->jsonResponse($booking);
            } else {
                if ($me['role'] === 'customer') {
                    $this->jsonResponse($this->bookingModel->getByCustomer($me['id']));
                } else {
                    $this->jsonResponse($this->bookingModel->getAll($customerId));
                }
            }
        }

        if ($method === 'POST') {
            $this->checkCsrf();
            $data = $this->getInput();

            if ($me['role'] === 'customer') {
                $customerId = $me['id'];
            } else {
                $customerId = intval($data['customer_id'] ?? $me['id']);
            }

            $roomId   = intval($data['room_id'] ?? 0);
            $checkIn  = $data['check_in'] ?? '';
            $checkOut = $data['check_out'] ?? '';
            $requests = trim($data['special_requests'] ?? '');

            if (!$roomId || !$checkIn || !$checkOut) {
                $this->jsonResponse(['error' => 'Room, check-in and check-out required']);
            }

            $ci = new DateTime($checkIn);
            $co = new DateTime($checkOut);
            if ($co <= $ci) {
                $this->jsonResponse(['error' => 'Check-out must be after check-in']);
            }
            $nights = $ci->diff($co)->days;

            $room = $this->roomModel->findById($roomId);
            if (!$room) {
                $this->jsonResponse(['error' => 'Room not found']);
            }
            if ($room['status'] === 'maintenance') {
                $this->jsonResponse(['error' => 'Room is under maintenance']);
            }

            if ($this->bookingModel->checkOverlap($roomId, $checkIn, $checkOut)) {
                $this->jsonResponse(['error' => 'Room is not available for selected dates']);
            }

            $total = $room['price_per_night'] * $nights;
            $newId = $this->bookingModel->create($customerId, $roomId, $checkIn, $checkOut, $total, $requests);

            if ($newId) {
                if ($checkIn === date('Y-m-d')) {
                    $this->roomModel->updateStatus($roomId, 'occupied');
                }
                $this->logActivity($me['id'], 'Create Booking', "Booking #$newId for room $roomId");
                $this->jsonResponse(['success' => true, 'id' => $newId, 'total_price' => $total]);
            } else {
                $this->jsonResponse(['error' => 'Booking failed']);
            }
        }

        if ($method === 'PUT') {
            $this->checkCsrf();
            $data = $this->getInput();
            $id = intval($data['id'] ?? 0);

            $booking = $this->bookingModel->findById($id);
            if (!$booking) {
                $this->jsonResponse(['error' => 'Booking not found']);
            }

            if ($me['role'] === 'customer' && $booking['customer_id'] != $me['id']) {
                $this->jsonResponse(['error' => 'Access denied'], 403);
            }

            $status   = $data['status'] ?? $booking['status'];
            $requests = trim($data['special_requests'] ?? $booking['special_requests']);

            if ($me['role'] === 'customer') {
                if (!in_array($status, ['confirmed', 'cancelled'])) {
                    $this->jsonResponse(['error' => 'You can only cancel or keep the booking']);
                }
            } else {
                if ($status === 'checked_in') {
                    $this->roomModel->updateStatus($booking['room_id'], 'occupied');
                } elseif (in_array($status, ['checked_out', 'cancelled'])) {
                    $this->roomModel->updateStatus($booking['room_id'], 'available');
                }
            }

            if ($this->bookingModel->update($id, $status, $requests)) {
                $this->logActivity($me['id'], 'Update Booking', "Updated booking #$id to $status");
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Update failed']);
            }
        }

        if ($method === 'DELETE') {
            $this->checkCsrf();
            requireRole('admin');
            $data = $this->getInput();
            $id = intval($data['id'] ?? 0);
            if ($this->bookingModel->delete($id)) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Delete failed']);
            }
        }

        $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }
}
