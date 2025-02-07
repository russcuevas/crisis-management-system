<?php
include '../database/connection.php';

session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

$user_id = $_GET['id'];

$sql = "UPDATE tbl_users SET is_verified = 1 WHERE id = :user_id";
$stmt = $conn->prepare($sql);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);

if ($stmt->execute()) {
    $_SESSION['users_success'] = "User approved successfully.";
    header('location: users.php');
} else {
    $_SESSION['users_error'] = "Failed to approve user.";
    header('location: users.php');
}
