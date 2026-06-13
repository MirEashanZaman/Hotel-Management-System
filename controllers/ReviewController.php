<?php
class ReviewController extends BaseController {
    private $reviewModel;
    private $bookingModel;

    public function __construct() {
        requireLogin();
        $this->reviewModel = new Review();
        $this->bookingModel = new Booking();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $me = $this->me();

        if ($method === 'GET') {
            $roomId    = $_GET['room_id']    ?? null;
            $bookingId = $_GET['booking_id'] ?? null;

            if ($bookingId) {
                $this->jsonResponse($this->reviewModel->getByBooking($bookingId));
            } elseif ($roomId) {
                $this->jsonResponse($this->reviewModel->getByRoom($roomId));
            } else {
                $this->jsonResponse($this->reviewModel->getAll());
            }
        }

        if ($method === 'POST') {
            $this->checkCsrf();
            if ($me['role'] !== 'customer') {
                $this->jsonResponse(['error' => 'Only customers can post reviews']);
            }

            $data      = $this->getInput();
            $bookingId = intval($data['booking_id'] ?? 0);
            $rating    = intval($data['rating']     ?? 0);
            $comment   = trim($data['comment']      ?? '');

            if (!$bookingId || !$rating) {
                $this->jsonResponse(['error' => 'Booking and rating are required']);
            }

            if ($rating < 1 || $rating > 5) {
                $this->jsonResponse(['error' => 'Rating must be between 1 and 5']);
            }

            $booking = $this->bookingModel->findById($bookingId);
            if (!$booking || $booking['customer_id'] != $me['id']) {
                $this->jsonResponse(['error' => 'Booking not found']);
            }

            if ($booking['status'] !== 'checked_out') {
                $this->jsonResponse(['error' => 'You can only review completed stays (checked out)']);
            }

            if ($this->reviewModel->checkExists($bookingId, $me['id'])) {
                $this->jsonResponse(['error' => 'You have already reviewed this booking']);
            }

            $roomId = $booking['room_id'];
            if ($this->reviewModel->create($me['id'], $bookingId, $roomId, $rating, $comment)) {
                $this->logActivity($me['id'], 'Review Posted', "Review for booking #$bookingId");
                $this->jsonResponse(['success' => true, 'message' => 'Review posted successfully!']);
            } else {
                $this->jsonResponse(['error' => 'Failed to post review']);
            }
        }

        if ($method === 'DELETE') {
            $this->checkCsrf();
            $data = $this->getInput();
            $id   = intval($data['id'] ?? 0);

            if ($me['role'] === 'customer') {
                $success = $this->reviewModel->deleteByCustomer($id, $me['id']);
            } elseif ($me['role'] === 'admin') {
                $success = $this->reviewModel->delete($id);
            } else {
                $this->jsonResponse(['error' => 'Access denied'], 403);
            }

            if ($success) {
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Delete failed or not authorized']);
            }
        }

        $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }
}
