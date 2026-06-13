<?php
class DashboardController extends BaseController {
    private $db;

    public function __construct() {
        requireLogin();
        global $conn;
        $this->db = $conn;
    }

    public function index() {
        $me = $this->me();
        $stats = [];

        if ($me['role'] === 'admin') {
            $stats['total_rooms']      = $this->db->query("SELECT COUNT(*) c FROM rooms")->fetch()['c'];
            $stats['available_rooms']  = $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='available'")->fetch()['c'];
            $stats['occupied_rooms']   = $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='occupied'")->fetch()['c'];
            $stats['maintenance_rooms']= $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='maintenance'")->fetch()['c'];
            $stats['total_bookings']   = $this->db->query("SELECT COUNT(*) c FROM bookings")->fetch()['c'];
            $stats['active_bookings']  = $this->db->query("SELECT COUNT(*) c FROM bookings WHERE status IN ('confirmed','checked_in')")->fetch()['c'];
            $stats['total_revenue']    = $this->db->query("SELECT COALESCE(SUM(amount),0) s FROM payments WHERE payment_status='paid'")->fetch()['s'];
            $stats['total_customers']  = $this->db->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch()['c'];
            $stats['total_staff']      = $this->db->query("SELECT COUNT(*) c FROM users WHERE role='staff'")->fetch()['c'];
            $stats['pending_payments'] = $this->db->query("SELECT COUNT(*) c FROM payments WHERE payment_status='pending'")->fetch()['c'];
            $stats['pending_services'] = $this->db->query("SELECT COUNT(*) c FROM service_requests WHERE status='pending'")->fetch()['c'];

            $stmt = $this->db->query("SELECT b.id, b.check_in, b.check_out, b.status, b.total_price, u.name as customer_name, r.room_number FROM bookings b JOIN users u ON b.customer_id=u.id JOIN rooms r ON b.room_id=r.id ORDER BY b.booked_at DESC LIMIT 5");
            $stats['recent_bookings'] = $stmt->fetchAll();

            $stmt = $this->db->query("SELECT DATE_FORMAT(paid_at,'%Y-%m') as month, SUM(amount) as revenue FROM payments WHERE payment_status='paid' AND paid_at >= DATE_SUB(NOW(), INTERVAL 6 MONTH) GROUP BY month ORDER BY month");
            $stats['monthly_revenue'] = $stmt->fetchAll();

        } elseif ($me['role'] === 'staff') {
            $stats['total_rooms']     = $this->db->query("SELECT COUNT(*) c FROM rooms")->fetch()['c'];
            $stats['available_rooms'] = $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='available'")->fetch()['c'];
            $stats['occupied_rooms']  = $this->db->query("SELECT COUNT(*) c FROM rooms WHERE status='occupied'")->fetch()['c'];
            $stats['active_bookings'] = $this->db->query("SELECT COUNT(*) c FROM bookings WHERE status IN ('confirmed','checked_in')")->fetch()['c'];
            $stats['total_customers'] = $this->db->query("SELECT COUNT(*) c FROM users WHERE role='customer'")->fetch()['c'];
            $stats['pending_services']= $this->db->query("SELECT COUNT(*) c FROM service_requests WHERE status='pending'")->fetch()['c'];

            $stmt = $this->db->query("SELECT b.id, b.check_in, b.check_out, b.status, b.total_price, u.name as customer_name, r.room_number FROM bookings b JOIN users u ON b.customer_id=u.id JOIN rooms r ON b.room_id=r.id WHERE b.status IN ('confirmed','checked_in') ORDER BY b.check_in ASC LIMIT 5");
            $stats['recent_bookings'] = $stmt->fetchAll();

        } else {
            $uid = $me['id'];
            $stmt = $this->db->prepare("SELECT COUNT(*) c FROM bookings WHERE customer_id=?"); 
            $stmt->execute([$uid]);
            $stats['total_bookings'] = $stmt->fetch()['c'];

            $stmt = $this->db->prepare("SELECT COUNT(*) c FROM bookings WHERE customer_id=? AND status IN ('confirmed','checked_in')"); 
            $stmt->execute([$uid]);
            $stats['active_bookings'] = $stmt->fetch()['c'];

            $stmt = $this->db->prepare("SELECT COALESCE(SUM(p.amount),0) s FROM payments p JOIN bookings b ON p.booking_id=b.id WHERE b.customer_id=? AND p.payment_status='paid'"); 
            $stmt->execute([$uid]);
            $stats['total_spent'] = $stmt->fetch()['s'];

            $stmt = $this->db->prepare("SELECT COUNT(*) c FROM service_requests sr JOIN bookings b ON sr.booking_id=b.id WHERE b.customer_id=?"); 
            $stmt->execute([$uid]);
            $stats['service_requests'] = $stmt->fetch()['c'];

            $stmt = $this->db->prepare("SELECT b.*, r.room_number, r.room_type FROM bookings b JOIN rooms r ON b.room_id=r.id WHERE b.customer_id=? ORDER BY b.booked_at DESC LIMIT 5"); 
            $stmt->execute([$uid]);
            $stats['recent_bookings'] = $stmt->fetchAll();
        }

        $this->jsonResponse($stats);
    }
}

