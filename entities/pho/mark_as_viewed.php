<?php
include '../../database/connection.php';

if (isset($_GET['incident_id'])) {
    $incident_id = $_GET['incident_id'];
    $updateQuery = "UPDATE tbl_notifications SET is_view = 1 WHERE incident_id = :incident_id AND is_view = 0";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bindParam(':incident_id', $incident_id);

    if ($stmt->execute()) {
        header("Location: view_pending_incident.php?incident_id=" . $incident_id);
        exit;
    } else {
        $_SESSION['error'] = 'Not found';
        header('Location: pho_dashboard.php');
        exit();
    }
} else {
    $_SESSION['error'] = 'Not found';
    header('Location: pho_dashboard.php');
    exit();
}
