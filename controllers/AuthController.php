<?php
class AuthController extends BaseController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function session() {
        if (isLoggedIn()) {
            $this->jsonResponse(['loggedIn' => true, 'user' => $this->me()]);
        } else {
            $this->jsonResponse(['loggedIn' => false]);
        }
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $data     = $this->getInput();
        $email    = trim($data['email']    ?? '');
        $password = trim($data['password'] ?? '');

        if (!$email || !$password) {
            $this->jsonResponse(['error' => 'Email and password required']);
        }

        $user = $this->userModel->findByEmail($email);

        if (!$user) {
            $this->jsonResponse(['error' => 'Invalid email or password']);
        }

        $stored   = $user['password'];
        $verified = false;

        if (strpos($stored, 'sha256:') === 0) {
            $hash = substr($stored, 7);
            if (hash('sha256', $password) === $hash) {
                $verified = true;
                $bcrypt = password_hash($password, PASSWORD_DEFAULT);
                $this->userModel->updatePassword($user['id'], $bcrypt);
            }
        }
        elseif (strpos($stored, 'NEEDS_HASH:') === 0) {
            $plain = substr($stored, 11);
            if ($password === $plain) {
                $verified = true;
                $bcrypt = password_hash($password, PASSWORD_DEFAULT);
                $this->userModel->updatePassword($user['id'], $bcrypt);
            }
        }
        elseif (strpos($stored, '$2y$') === 0 || strpos($stored, '$2a$') === 0) {
            $verified = password_verify($password, $stored);
        }
        else {
            $verified = ($password === $stored);
            if ($verified) {
                $bcrypt = password_hash($password, PASSWORD_DEFAULT);
                $this->userModel->updatePassword($user['id'], $bcrypt);
            }
        }

        if (!$verified) {
            $this->jsonResponse(['error' => 'Invalid email or password']);
        }

        $_SESSION['user_id'] = $user['id'];
        $_SESSION['name']    = $user['name'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['email']   = $user['email'];

        $this->logActivity($user['id'], 'Login', 'User logged in from ' . ($_SERVER['REMOTE_ADDR'] ?? ''));

        $this->jsonResponse([
            'success' => true,
            'user' => [
                'id'    => $user['id'],
                'name'  => $user['name'],
                'role'  => $user['role'],
                'email' => $user['email']
            ]
        ]);
    }

    public function logout() {
        if (isLoggedIn()) {
            $this->logActivity($this->me()['id'], 'Logout', 'User logged out');
        }
        session_destroy();
        $this->jsonResponse(['success' => true]);
    }

    public function signup() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(['error' => 'Method not allowed'], 405);
        }

        $data     = $this->getInput();
        $name     = trim($data['name']     ?? '');
        $email    = trim($data['email']    ?? '');
        $phone     = trim($data['phone']    ?? '');
        $address   = trim($data['address']  ?? '');
        $password = $data['password']      ?? '';

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

        $hash  = password_hash($password, PASSWORD_DEFAULT);
        $newId = $this->userModel->create($name, $email, $hash, 'customer', $phone, $address);

        if ($newId) {
            $this->logActivity($newId, 'Register', "New customer registered: $email");
            $this->jsonResponse(['success' => true, 'message' => 'Account created successfully']);
        } else {
            $this->jsonResponse(['error' => 'Registration failed. Please try again.']);
        }
    }
}
