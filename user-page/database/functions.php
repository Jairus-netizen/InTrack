<?php
function getTimeStats($userId, $conn) {
    $stats = [
        'today' => '0h 0m',
        'week' => '0h 0m',
        'month' => '0h 0m'
    ];

    // Today's time
    $todayQuery = "SELECT SUM(TIMESTAMPDIFF(MINUTE, clock_in, COALESCE(clock_out, NOW()))) as minutes 
                   FROM time_entries 
                   WHERE user_id = ? AND date = CURDATE()";
    $stmt = $conn->prepare($todayQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['today'] = formatMinutes($row['minutes']);
    }

    // This week's time
    $weekQuery = "SELECT SUM(TIMESTAMPDIFF(MINUTE, clock_in, COALESCE(clock_out, NOW()))) as minutes 
                  FROM time_entries 
                  WHERE user_id = ? AND YEARWEEK(date, 1) = YEARWEEK(CURDATE(), 1)";
    $stmt = $conn->prepare($weekQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['week'] = formatMinutes($row['minutes']);
    }

    // This month's time
    $monthQuery = "SELECT SUM(TIMESTAMPDIFF(MINUTE, clock_in, COALESCE(clock_out, NOW()))) as minutes 
                   FROM time_entries 
                   WHERE user_id = ? AND YEAR(date) = YEAR(CURDATE()) AND MONTH(date) = MONTH(CURDATE())";
    $stmt = $conn->prepare($monthQuery);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $stats['month'] = formatMinutes($row['minutes']);
    }

    return $stats;
}

function formatMinutes($minutes) {
    if (!$minutes) return '0h 0m';
    
    $hours = floor($minutes / 60);
    $mins = $minutes % 60;
    return $hours . 'h ' . $mins . 'm';
}

function getProjects($userId, $conn) {
    $projects = [];
    $query = "SELECT * FROM projects WHERE user_id = ? ORDER BY due_date LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $projects[] = $row;
    }
    
    return $projects;
}

function getNotifications($userId, $conn) {
    $notifications = [];
    $query = "SELECT * FROM notifications WHERE user_id = ? AND archived = 0 ORDER BY created_at DESC LIMIT 5";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    return $notifications;
}

function timeAgo($datetime) {
    $time = strtotime($datetime);
    $time = time() - $time;
    
    $units = array (
        31536000 => 'year',
        2592000 => 'month',
        604800 => 'week',
        86400 => 'day',
        3600 => 'hour',
        60 => 'minute',
        1 => 'second'
    );
    
    foreach ($units as $unit => $val) {
        if ($time < $unit) continue;
        $numberOfUnits = floor($time / $unit);
        return ($val == 'second') ? 'just now' : 
               (($numberOfUnits>1) ? $numberOfUnits.' '.$val.'s ago' : $numberOfUnits.' '.$val.' ago');
    }
}
?>