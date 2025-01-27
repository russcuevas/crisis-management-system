<?php
include '../database/connection.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('location:admin_login.php');
    exit;
}

// DELETE ADMIN
if (isset($_POST['id'])) {
    $admin_id_to_delete = $_POST['id'];

    if ($admin_id_to_delete == $_SESSION['admin_id']) {
        $_SESSION['error'] = 'You cannot delete your own account.';
        header('location:manage_admin.php');
        exit;
    }

    $deleteQuery = "DELETE FROM tbl_admin WHERE id = :id";
    $stmt = $conn->prepare($deleteQuery);
    $stmt->bindParam(':id', $admin_id_to_delete, PDO::PARAM_INT);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Admin successfully deleted.';
    } else {
        $_SESSION['error'] = 'Failed to delete admin. Please try again.';
    }

    header('location:manage_admin.php');
    exit;
}
