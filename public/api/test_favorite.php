<?php
// Force display of errors for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type to JSON
header('Content-Type: application/json');

try {
    // Test if we can include initialize.php
    $initialize_path = '../../private/core/initialize.php';
    if (file_exists($initialize_path)) {
        require_once($initialize_path);
        
        // Test if RecipeFavorite class is available
        if (class_exists('RecipeFavorite')) {
            echo json_encode([
                'success' => true,
                'message' => 'RecipeFavorite class exists',
                'initialize_path' => realpath($initialize_path),
                'session_active' => isset($session) && is_object($session),
                'logged_in' => isset($session) && is_object($session) && $session->is_logged_in(),
                'user_id' => isset($session) && is_object($session) ? $session->get_user_id() : null
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'RecipeFavorite class does not exist',
                'available_classes' => get_declared_classes()
            ]);
        }
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Initialize.php does not exist',
            'path' => $initialize_path,
            'current_dir' => __DIR__
        ]);
    }
} catch (Exception $e) {
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage(),
        'file' => $e->getFile(),
        'line' => $e->getLine()
    ]);
}
?>
