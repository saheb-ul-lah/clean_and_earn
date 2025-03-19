<?php
//
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


function isLoggedIn() {
    return isset($_SESSION['user_id']);
}


function hasRole($role) {
    if (!isLoggedIn()) {
        return false;
    }
    
    if (is_array($role)) {
        return in_array($_SESSION['user_role'], $role);
    }
    
    return $_SESSION['user_role'] === $role;
}


function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: ../auth/login.php');
        exit;
    }
}


function requireRole($role) {
    requireLogin();
    
    if (!hasRole($role)) {
        header('Location: ../dashboard/unauthorized.php');
        exit;
    }
}


function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}


function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}


function getUserData($userId, $pdo) {
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch();
}

function formatDate($date) {
    return date('F j, Y', strtotime($date));
}

function formatDateTime($datetime) {
    return date('F j, Y, g:i a', strtotime($datetime));
}

function getWasteTypeName($wasteTypeId, $pdo) {
    $stmt = $pdo->prepare("SELECT name FROM waste_types WHERE id = ?");
    $stmt->execute([$wasteTypeId]);
    $result = $stmt->fetch();
    return $result ? $result['name'] : 'Unknown';
}

function getWasteSubtypeName($wasteSubtypeId, $pdo) {
    $stmt = $pdo->prepare("SELECT name FROM waste_subtypes WHERE id = ?");
    $stmt->execute([$wasteSubtypeId]);
    $result = $stmt->fetch();
    return $result ? $result['name'] : 'Unknown';
}

function calculatePoints($wasteTypeId, $weight, $pdo) {
    $stmt = $pdo->prepare("SELECT rate_per_kg FROM waste_types WHERE id = ?");
    $stmt->execute([$wasteTypeId]);
    $result = $stmt->fetch();
    
    if (!$result) {
        return 0;
    }
    
    return round($weight * $result['rate_per_kg']);
}

function addPointsToUser($userId, $points, $transactionType, $referenceId, $referenceType, $description, $pdo) {
    try {
        $pdo->beginTransaction();
        
        $stmt = $pdo->prepare("INSERT INTO points_transactions (user_id, points, transaction_type, reference_id, reference_type, description) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->execute([$userId, $points, $transactionType, $referenceId, $referenceType, $description]);
        
        $stmt = $pdo->prepare("UPDATE users SET total_points = total_points + ? WHERE id = ?");
        $stmt->execute([$points, $userId]);
        
        $pdo->commit();
        return true;
    } catch (Exception $e) {
        $pdo->rollBack();
        return false;
    }
}

function getStatusBadgeClass($status) {
    switch ($status) {
        case 'pending':
            return 'bg-yellow-100 text-yellow-800';
        case 'assigned':
            return 'bg-blue-100 text-blue-800';
        case 'collected':
            return 'bg-purple-100 text-purple-800';
        case 'completed':
            return 'bg-green-100 text-green-800';
        case 'cancelled':
            return 'bg-red-100 text-red-800';
        case 'in_progress':
            return 'bg-blue-100 text-blue-800';
        case 'delivered':
            return 'bg-green-100 text-green-800';
        case 'available':
            return 'bg-green-100 text-green-800';
        case 'reserved':
            return 'bg-purple-100 text-purple-800';
        case 'sold':
            return 'bg-gray-100 text-gray-800';
        case 'paid':
            return 'bg-green-100 text-green-800';
        default:
            return 'bg-gray-100 text-gray-800';
    }
}
?>

