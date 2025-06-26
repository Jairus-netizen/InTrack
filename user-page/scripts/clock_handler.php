<?php
session_start();
require_once '../database/dbconnection.php';

// Define standard working hours
define('STANDARD_CLOCK_IN', '08:00:00');
define('STANDARD_CLOCK_OUT', '17:00:00');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $userId = $_SESSION['user_id'] ?? null;

    if (!$userId) {
    echo json_encode(['success' => false, 'message' => 'Not logged in']);
    exit;
}

// Only proceed if it's a POST request with action parameter
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$action = $_POST['action'];
$userId = $_SESSION['user_id'];
$response = [];

try {
    $conn->begin_transaction();

    if ($action === 'clock_in') {
        // Check if already clocked in
        $check = $conn->prepare("SELECT id FROM time_entries WHERE user_id = ? AND date = CURDATE() AND clock_out IS NULL");
        $check->bind_param("i", $userId);
        $check->execute();
        
        if ($check->get_result()->num_rows > 0) {
            throw new Exception('You are already clocked in');
        }

            // Determine status based on current time
            $currentTime = date('H:i:s');
            $status = 'ontime';
            $isSuspicious = 'NULL';
            
            if ($currentTime < STANDARD_CLOCK_IN) {
                $status = 'early';
                $isSuspicious = 'early_time_in';
            } elseif ($currentTime > STANDARD_CLOCK_IN) {
                $status = 'late';
                $isSuspicious = 'late_time_in';
            }

        // Insert new clock in entry
        $stmt = $conn->prepare("INSERT INTO time_entries 
                                  (user_id, clock_in, date, status, is_suspicious) 
                                  VALUES (?, NOW(), CURDATE(), ?, ?)");
            $stmt->bind_param("iss", $userId, $status, $isSuspicious);
        $stmt->execute();
        
        $response = [
            'success' => true, 
            'action' => 'in', 
            'message' => 'Clocked in successfully',
                'status' => $status
        ];
    } 
    elseif ($action === 'clock_out') {
        // Get the current entry
        $stmt = $conn->prepare("SELECT * FROM time_entries 
                              WHERE user_id = ? AND date = CURDATE() AND clock_out IS NULL 
                              ORDER BY clock_in DESC LIMIT 1");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
            $entry = $stmt->get_result()->fetch_assoc();
        
        if (!$entry) {
            throw new Exception('No active clock in found');
        }

            // Determine if early clock-out
            $currentTime = date('H:i:s');
            $isSuspicious = $entry['is_suspicious']; // Keep existing value
            
            if ($currentTime < STANDARD_CLOCK_OUT) {
                // Only mark as early time out if not already marked for something else
                if ($isSuspicious === 'NULL') {
                    $isSuspicious = 'early_time_out';
                }
            }

        // Update the clock out entry
        $stmt = $conn->prepare("UPDATE time_entries 
                               SET clock_out = NOW(),
                                       is_suspicious = ?,
                                       hours_completed = IF(TIME(clock_out) >= TIME(clock_in) + INTERVAL 8 HOUR, 'complete', 'incomplete')
                               WHERE id = ?");
            $stmt->bind_param("si", $isSuspicious, $entry['id']);
        $stmt->execute();
        
        $response = [
            'success' => true, 
            'action' => 'out', 
            'message' => 'Clocked out successfully',
                'status' => $entry['status']
        ];
    } 
    else {
        throw new Exception('Invalid action');
    }

    $conn->commit();
} catch (Exception $e) {
    $conn->rollback();
        $response = ['success' => false, 'message' => $e->getMessage()];
}

header('Content-Type: application/json');
echo json_encode($response);
    exit;
}