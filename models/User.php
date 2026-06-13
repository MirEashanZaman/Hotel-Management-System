<?php
class User extends BaseModel {
    public function findByEmail($email) {
        $stmt = $this->db->prepare("SELECT id, name, email, password, role, avatar_url FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function findById($id) {
        $stmt = $this->db->prepare("SELECT id, name, email, role, phone, address, avatar_url, created_at FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function emailExists($email) {
        $stmt = $this->db->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        return $stmt->get_result()->num_rows > 0;
    }

    public function create($name, $email, $hashedPassword, $role, $phone, $address) {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, role, phone, address) VALUES (?,?,?,?,?,?)");
        $stmt->bind_param("ssssss", $name, $email, $hashedPassword, $role, $phone, $address);
        if ($stmt->execute()) {
            return $this->db->insert_id;
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
            $stmt->bind_param("sssi", $name, $phone, $address, $id);
            $stmt->execute();
        } elseif ($avatarUrl) {
            $user = $this->findById($id);
            if ($user && $user['avatar_url'] && file_exists(__DIR__ . '/../' . $user['avatar_url'])) {
                @unlink(__DIR__ . '/../' . $user['avatar_url']);
            }
            $stmt = $this->db->prepare("UPDATE users SET name=?, phone=?, address=?, avatar_url=? WHERE id=?");
            $stmt->bind_param("ssssi", $name, $phone, $address, $avatarUrl, $id);
            $stmt->execute();
        } else {
            $stmt = $this->db->prepare("UPDATE users SET name=?, phone=?, address=? WHERE id=?");
            $stmt->bind_param("sssi", $name, $phone, $address, $id);
            $stmt->execute();
        }

        if ($hashedPassword) {
            $stmt = $this->db->prepare("UPDATE users SET password=? WHERE id=?");
            $stmt->bind_param("si", $hashedPassword, $id);
            $stmt->execute();
        }
        return true;
    }

    public function delete($id) {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->bind_param("i", $id);
        return $stmt->execute();
    }

    public function getAll() {
        $result = $this->db->query("SELECT id, name, email, role, phone, address, created_at FROM users ORDER BY role, name");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function getCustomersOnly() {
        $result = $this->db->query("SELECT id, name, email, role, phone, address, created_at FROM users WHERE role = 'customer'");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public function updatePassword($id, $hashedPassword) {
        $stmt = $this->db->prepare("UPDATE users SET password=? WHERE id=?");
        $stmt->bind_param("si", $hashedPassword, $id);
        return $stmt->execute();
    }
}
