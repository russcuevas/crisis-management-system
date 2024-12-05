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

    // Get the user's profile picture before deleting
    $get_user_image = "SELECT `profile_picture` FROM `tbl_users` WHERE `id` = :id";
    $stmt = $conn->prepare($get_user_image);
    $stmt->bindParam(':id', $id, PDO::PARAM_INT);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Delete the user's profile picture from the server if it exists
        if (!empty($user['profile_picture'])) {
            $image_path = '../assets/images/profile/' . $user['profile_picture'];
            if (file_exists($image_path)) {
                unlink($image_path);
            }
        }

        // Begin Transaction for safe deletion
        $conn->beginTransaction();

        try {
            $delete_incidents = "DELETE FROM `tbl_incidents` WHERE `user_id` = :id";
            $stmt = $conn->prepare($delete_incidents);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $delete_notifications = "DELETE FROM `tbl_notifications` WHERE `user_id` = :id";
            $stmt = $conn->prepare($delete_notifications);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $delete_reports = "DELETE FROM `tbl_reports` WHERE `user_id` = :id";
            $stmt = $conn->prepare($delete_reports);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();

            $delete_user = "DELETE FROM `tbl_users` WHERE `id` = :id";
            $stmt = $conn->prepare($delete_user);
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $stmt->execute();
            $conn->commit();

            $_SESSION['users_success'] = 'User deleted successfully!';
            header('Location: users.php');
            exit();
        } catch (Exception $e) {
            $conn->rollBack();

            $_SESSION['users_error'] = 'Error occurred while deleting the user and related records!';
            header('Location: users.php');
            exit();
        }
    } else {
        $_SESSION['users_error'] = 'User ID not found!';
        header('Location: users.php');
        exit();
    }
} else {
    $_SESSION['users_error'] = 'User ID not found!';
    header('Location: users.php');
    exit();
}
// END DELETE USER
