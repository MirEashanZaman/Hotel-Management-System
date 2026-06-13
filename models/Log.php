<?php
class Log extends BaseModel {
    public function getLogs($limit = 100) {
        $limit = intval($limit);
        $stmt = $this->db->query("SELECT l.*, u.name as user_name, u.role FROM activity_logs l LEFT JOIN users u ON l.user_id = u.id ORDER BY l.logged_at DESC LIMIT $limit");
        return $stmt->fetchAll();
    }

    public function write($userId, $action, $details = '') {
        $ip   = $_SERVER['REMOTE_ADDR'] ?? '';
        $stmt = $this->db->prepare("INSERT INTO activity_logs (user_id, action, details, ip_address) VALUES (?,?,?,?)");
        return $stmt->execute([$userId, $action, $details, $ip]);
    }
}

