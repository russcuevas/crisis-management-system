<?php
include '../database/connection.php';
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('location:admin_login.php');
    exit;
}

if (isset($_GET['id'])) {
    $feedback_id = $_GET['id'];
    try {
        $delete_query = "DELETE FROM tbl_feedback WHERE id = :id";
        $stmt = $conn->prepare($delete_query);
        $stmt->bindParam(':id', $feedback_id, PDO::PARAM_INT);
        if ($stmt->execute()) {
            $_SESSION['feedback_deleted'] = 'Feedback deleted successfully!';
        } else {
            $_SESSION['feedback_error'] = 'There was an error deleting the feedback.';
        }
    } catch (Exception $e) {
        $_SESSION['feedback_error'] = 'Error: ' . $e->getMessage();
    }
    header('location:feedback.php');
    exit;
} else {
    header('location:feedback.php');
    exit;
}
