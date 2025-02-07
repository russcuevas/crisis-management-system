<?php
include '../database/connection.php';

// Start session and check for admin login
session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

// Get user ID from the URL
$user_id = $_GET['id'];

// Update the user's is_verified status to 1 (approved)
$sql = "UPDATE tbl_users SET is_verified = 1 WHERE id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

if ($stmt->execute()) {
    $_SESSION['users_success'] = "User approved successfully.";
    header('location: users.php'); // Redirect back to users page
} else {
    $_SESSION['users_error'] = "Failed to approve user.";
    header('location: users.php');
}
