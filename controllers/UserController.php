<?php
class UserController extends BaseController {
    private $userModel;

    public function __construct() {
        requireLogin();
        $this->userModel = new User();
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $me = $this->me();

        if ($method === 'GET') {
            $id = $_GET['id'] ?? null;
            if ($id) {
                if ($me['role'] === 'customer' && $id != $me['id']) {
                    $this->jsonResponse(['error' => 'Access denied'], 403);
                }
                $user = $this->userModel->findById($id);
                if (!$user) {
                    $this->jsonResponse(['error' => 'User not found']);
                }
                if ($me['role'] === 'staff' && $user['role'] === 'admin') {
                    $this->jsonResponse(['error' => 'Access denied'], 403);
                }
                $this->jsonResponse($user);
            } else {
                if ($me['role'] === 'customer') {
                    $this->jsonResponse([$this->userModel->findById($me['id'])]);
                } elseif ($me['role'] === 'staff') {
                    $this->jsonResponse($this->userModel->getCustomersOnly());
                } else {
                    $this->jsonResponse($this->userModel->getAll());
                }
            }
        }

        if ($method === 'POST') {
            $data = !empty($_POST) ? $_POST : $this->getInput();
            $targetId = intval($data['id'] ?? 0);

            if ($targetId > 0) {
                // This is an UPDATE action
                $target = $this->userModel->findById($targetId);
                if (!$target) {
                    $this->jsonResponse(['error' => 'User not found']);
                }

                if ($me['role'] === 'customer' && $targetId != $me['id']) {
                    $this->jsonResponse(['error' => 'Access denied'], 403);
                }
                if ($me['role'] === 'staff' && $target['role'] !== 'customer' && $targetId != $me['id']) {
                    $this->jsonResponse(['error' => 'Staff can only edit customers or their own profile']);
                }
                if ($me['role'] === 'staff' && $target['role'] === 'admin') {
                    $this->jsonResponse(['error' => 'Access denied'], 403);
                }

                $name    = trim($data['name']    ?? '');
                $phone   = trim($data['phone']   ?? '');
                $address = trim($data['address'] ?? '');
                $newPass = trim($data['password'] ?? '');

                // Handle File Upload/Delete
                $avatar_url = null;
                $deleteAvatar = isset($data['delete_avatar']) && $data['delete_avatar'] == '1';

                if (!$deleteAvatar && isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
                    $fileTmpPath = $_FILES['avatar']['tmp_name'];
                    $fileName = $_FILES['avatar']['name'];
                    $fileNameCmps = explode(".", $fileName);
                    $fileExtension = strtolower(end($fileNameCmps));
                    
                    $allowedExtensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
                    if (in_array($fileExtension, $allowedExtensions)) {
                        $newFileName = md5(time() . $fileName) . '.' . $fileExtension;
                        $uploadFileDir = __DIR__ . '/../uploads/';
                        $dest_path = $uploadFileDir . $newFileName;
                        if (move_uploaded_file($fileTmpPath, $dest_path)) {
                            $avatar_url = 'uploads/' . $newFileName;
                        }
                    }
                }

                $hashed = $newPass ? password_hash($newPass, PASSWORD_DEFAULT) : null;
                if ($newPass && strlen($newPass) < 8) {
                    $this->jsonResponse(['error' => 'Password must be at least 8 characters']);
                }

                if ($this->userModel->update($targetId, $name, $phone, $address, $hashed, $avatar_url, $deleteAvatar)) {
                    $this->logActivity($me['id'], 'Update User', "Updated user ID $targetId");
                    if ($targetId == $me['id']) {
                        $_SESSION['name'] = $name;
                        if ($deleteAvatar) {
                            $_SESSION['avatar_url'] = null;
                        } elseif ($avatar_url) {
                            $_SESSION['avatar_url'] = $avatar_url;
                        }
                    }
                    $this->jsonResponse(['success' => true, 'message' => 'User updated']);
                } else {
                    $this->jsonResponse(['error' => 'Update failed']);
                }
            } else {
                // This is a CREATE action
                if ($me['role'] === 'customer') {
                    $this->jsonResponse(['error' => 'Access denied'], 403);
                }

                $name     = trim($data['name']     ?? '');
                $email    = trim($data['email']    ?? '');
                $phone    = trim($data['phone']    ?? '');
                $address  = trim($data['address']  ?? '');
                $password = $data['password']      ?? '';
                $role     = $data['role']          ?? 'customer';

                if ($me['role'] === 'staff' && $role !== 'customer') {
                    $this->jsonResponse(['error' => 'Staff can only add customers']);
                }
                if ($role === 'admin') {
                    $this->jsonResponse(['error' => 'Cannot create admin accounts']);
                }

                if (!$name || !$email || !$password) {
                    $this->jsonResponse(['error' => 'Name, email and password are required']);
                }
                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    $this->jsonResponse(['error' => 'Invalid email address']);
                }
                if (strlen($password) < 8) {
                    $this->jsonResponse(['error' => 'Password must be at least 8 characters']);
                }

                if ($this->userModel->emailExists($email)) {
                    $this->jsonResponse(['error' => 'This email is already registered']);
                }

                $hash = password_hash($password, PASSWORD_DEFAULT);
                $newId = $this->userModel->create($name, $email, $hash, $role, $phone, $address);

                if ($newId) {
                    $this->logActivity($me['id'], 'Add User', "Added $role: $email");
                    $this->jsonResponse(['success' => true, 'id' => $newId]);
                } else {
                    $this->jsonResponse(['error' => 'Failed to create user']);
                }
            }
        }

        if ($method === 'PUT') {
            $data     = $this->getInput();
            $targetId = intval($data['id'] ?? 0);

            if (!$targetId) {
                $this->jsonResponse(['error' => 'User ID required']);
            }

            $target = $this->userModel->findById($targetId);
            if (!$target) {
                $this->jsonResponse(['error' => 'User not found']);
            }

            if ($me['role'] === 'customer' && $targetId != $me['id']) {
                $this->jsonResponse(['error' => 'Access denied'], 403);
            }
            if ($me['role'] === 'staff' && $target['role'] !== 'customer' && $targetId != $me['id']) {
                $this->jsonResponse(['error' => 'Staff can only edit customers or their own profile']);
            }
            if ($me['role'] === 'staff' && $target['role'] === 'admin') {
                $this->jsonResponse(['error' => 'Access denied'], 403);
            }

            $name    = trim($data['name']    ?? '');
            $phone   = trim($data['phone']   ?? '');
            $address = trim($data['address'] ?? '');
            $newPass = trim($data['password'] ?? '');

            $hashed = $newPass ? password_hash($newPass, PASSWORD_DEFAULT) : null;

            if ($newPass && strlen($newPass) < 8) {
                $this->jsonResponse(['error' => 'Password must be at least 8 characters']);
            }

            if ($this->userModel->update($targetId, $name, $phone, $address, $hashed)) {
                $this->logActivity($me['id'], 'Update User', "Updated user ID $targetId");
                $this->jsonResponse(['success' => true, 'message' => 'User updated']);
            } else {
                $this->jsonResponse(['error' => 'Update failed']);
            }
        }

        if ($method === 'DELETE') {
            requireRole('admin');
            $data     = $this->getInput();
            $targetId = intval($data['id'] ?? 0);
            if ($targetId == $me['id']) {
                $this->jsonResponse(['error' => 'Cannot delete yourself']);
            }
            if ($this->userModel->delete($targetId)) {
                $this->logActivity($me['id'], 'Delete User', "Deleted user ID $targetId");
                $this->jsonResponse(['success' => true]);
            } else {
                $this->jsonResponse(['error' => 'Delete failed']);
            }
        }

        $this->jsonResponse(['error' => 'Method not allowed'], 405);
    }
}
