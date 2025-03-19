<?php
class Response {
    public function send($data, $statusCode = 200) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'data' => $data
        ]);
        exit();
    }
    
    public function sendError($title, $message, $statusCode = 400) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'error' => [
                'title' => $title,
                'message' => $message
            ]
        ]);
        exit();
    }
    
    public function sendValidationError($errors, $statusCode = 422) {
        http_response_code($statusCode);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'error' => [
                'title' => 'Validation Error',
                'message' => 'The given data was invalid.',
                'errors' => $errors
            ]
        ]);
        exit();
    }
}
?>