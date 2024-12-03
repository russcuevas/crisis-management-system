<?php
include '../database/connection.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('location:admin_login.php');
    exit;
}

// DELETE USER
if (isset($_POST['id'])) {
    $id = $_POST['id'];

    $delete_user = "DELETE FROM `tbl_users` WHERE `id` = :id";
    $stmt = $conn->prepare($delete_user);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    if ($stmt->execute()) {
        $_SESSION['users_success'] = 'User deleted successfully!';
        header('Location: users.php');
        exit();
    } else {
        $_SESSION['users_error'] = 'User not deleted successfully!';
        header('Location: users.php');
        exit();
    }
} else {
    $_SESSION['users_error'] = 'User ID Not Found';
    header('Location: users.php');
    exit();
}
// END DELETE USER
