<?php
class User extends BaseModel {
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, name, email, password, role, avatar_url FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, name, email, role, phone, address, avatar_url, created_at FROM users WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch();
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch() !== false;
    }

    public function create($name, $email, $hashedPassword, $role, $phone, $address) {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?,?,?,?,?,?)");
        if ($stmt->execute([$name, $email, $hashedPassword, $role, $phone, $address])) {
            return $this->db->lastInsertId();
        }
        return false;
    }

    public function update($id, $name, $phone, $address, $hashedPassword = null, $avatarUrl = null, $deleteAvatar = false) {
        if ($deleteAvatar) {
            $user = $this->findById($id);
            if ($user && $user['avatar_url'] && file_exists(__DIR__ . '/../' . $user['avatar_url'])) {
                @unlink(__DIR__ . '/../' . $user['avatar_url']);
            }
            $stmt = $this->db->prepare("UPDATE users SET name=?, phone=?, address=?, avatar_url=NULL WHERE id=?");
            $stmt->execute([$name, $phone, $address, $id]);
        } elseif ($avatarUrl) {
            $user = $this->findById($id);
            if ($user && $user['avatar_url'] && file_exists(__DIR__ . '/../' . $user['avatar_url'])) {
                @unlink(__DIR__ . '/../' . $user['avatar_url']);
            }
            $stmt = $this->db->prepare("UPDATE users SET name=?, phone=?, address=?, avatar_url=? WHERE id=?");
            $stmt->execute([$name, $phone, $address, $avatarUrl, $id]);
        } else {
            $stmt = $this->db->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?");
            $stmt->execute([$name, $phone, $address, $id]);
        }

        if ($hashedPassword) {
            $stmt = $this->db->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->execute([$hashedPassword, $id]);
        }
        return true;
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function getAll() {
        $result = $this->db->query("SELECT id, name, email, role, phone, address, created_at FROM users ORDER BY role, name");
        return $result->fetchAll();
    }

    public function getCustomersOnly() {
        $result = $this->db->query("SELECT id, name, email, role, phone, address, created_at FROM users WHERE role = 'customer'");
        return $result->fetchAll();
    }

    public function updatePassword($id, $hashedPassword) {
        $stmt = $this->db->prepare("UPDATE users SET password=? WHERE id=?");
        return $stmt->execute([$hashedPassword, $id]);
    }
}
