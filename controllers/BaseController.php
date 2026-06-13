<?php
abstract class BaseController {
    protected function jsonResponse($data, $statusCode = 200) {
        header('Content-Type: application/json');
        http_response_code($statusCode);
        echo json_encode($data);
        exit;
    }

    protected function getInput() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    protected function me() {
        return currentUser();
    }

    protected function logActivity($userId, $action, $details = '') {
        $logModel = new Log();
        $logModel->write($userId, $action, $details);
    }

    protected function checkCsrf() {
        $token = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!validateCsrfToken($token)) {
            $this->jsonResponse(['error' => 'CSRF verification failed'], 403);
        }
    }

    protected function imageCompress($source, $destination, $quality) {
        $info = getimagesize($source);
        if ($info === false) {
            return false;
        }
        $mime = $info['mime'];
        switch ($mime) {
            case 'image/jpeg':
                $image = imagecreatefromjpeg($source);
                break;
            case 'image/png':
                $image = imagecreatefrompng($source);
                break;
            case 'image/gif':
                $image = imagecreatefromgif($source);
                break;
            case 'image/webp':
                $image = imagecreatefromwebp($source);
                break;
            default:
                return false;
        }
        if (!$image) {
            return false;
        }
        $ext = strtolower(pathinfo($destination, PATHINFO_EXTENSION));
        $success = false;
        switch ($ext) {
            case 'jpeg':
            case 'jpg':
                $success = imagejpeg($image, $destination, $quality);
                break;
            case 'png':
                $pngQuality = 9 - round(($quality * 9) / 100);
                $success = imagepng($image, $destination, $pngQuality);
                break;
            case 'gif':
                $success = imagegif($image, $destination);
                break;
            case 'webp':
                $success = imagewebp($image, $destination, $quality);
                break;
            default:
                $success = imagejpeg($image, $destination, $quality);
                break;
        }
        imagedestroy($image);
        return $success;
    }
}

