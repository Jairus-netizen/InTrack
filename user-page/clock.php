<?php
session_start();
require_once './database/dbconnection.php';

$response = ['success' => false];

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_SESSION['user_id'])) {
    $userId = $_SESSION['user_id'];
    $action = $_POST['action'];
    
    try {
        if ($action === 'clock_in') {
            // Clock in
            $stmt = $conn->prepare("INSERT INTO time_entries 
                                  (user_id, clock_in, date, status, hours_completed) 
                                  VALUES 
                                  (?, NOW(), CURDATE(), 'ontime', 'incomplete')");
            $stmt->execute([$userId]);
            $response['success'] = true;
        } 
        elseif ($action === 'clock_out') {
            // Clock out - update most recent clock-in without clock-out
            $stmt = $conn->prepare("UPDATE time_entries 
                                  SET clock_out = NOW(),
                                      hours_completed = IF(TIMESTAMPDIFF(HOUR, clock_in, NOW()) >= 6, 'complete', 'incomplete'),
                                      status = CASE
                                          WHEN TIME(clock_in) < '08:00:00' THEN 'early'
                                          WHEN TIME(clock_in) > '09:00:00' THEN 'late'
                                          ELSE 'ontime'
                                      END
                                  WHERE user_id = ? AND date = CURDATE() AND clock_out IS NULL
                                  ORDER BY clock_in DESC LIMIT 1");
            $stmt->execute([$userId]);
            $response['success'] = true;
        }
        
        // Get updated time stats
        $response['stats'] = getTimeStats($userId, $conn);
        
    } catch(PDOException $e) {
        $response['error'] = $e->getMessage();
    }
}

header('Content-Type: application/json');
echo json_encode($response);
?>