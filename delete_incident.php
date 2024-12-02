<?php
session_start();
include('database/connection.php');

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

if (!$is_logged_in) {
    header('Location: login.php');
    exit();
}

if (isset($_GET['id'])) {
    $incident_id = $_GET['id'];
    $query = "SELECT * FROM tbl_incidents WHERE incident_id = :incident_id AND user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':incident_id', $incident_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $incident = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($incident) {
        $proofs = json_decode($incident['incident_proof'], true);
        if ($proofs && is_array($proofs)) {
            foreach ($proofs as $proof) {
                $proof_image_path = 'assets/images/proofs/' . $proof;
                if (file_exists($proof_image_path)) {
                    unlink($proof_image_path);
                }
            }
        }

        $deleteQuery = "DELETE FROM tbl_incidents WHERE incident_id = :incident_id";
        $deleteStmt = $conn->prepare($deleteQuery);
        $deleteStmt->bindParam(':incident_id', $incident_id, PDO::PARAM_INT);
        $deleteStmt->execute();

        $_SESSION['success'] = 'Incident request deleted successfully!';
        header('Location: history.php');
        exit();
    } else {
        $_SESSION['error'] = 'Not found';
        header('Location: history.php');
        exit();
    }
} else {
    $_SESSION['error'] = 'Not found';
    header('Location: history.php');
    exit();
}
?>
