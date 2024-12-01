<?php
session_start();
include 'database/connection.php';

if (isset($_GET['email']) && isset($_GET['code'])) {
    $email = $_GET['email'];
    $verification_code = $_GET['code'];

    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user['code'] == $verification_code) {
            $update_stmt = $conn->prepare("UPDATE tbl_users SET is_verified = '1', code = NULL WHERE email = :email");
            $update_stmt->bindParam(':email', $email, PDO::PARAM_STR);

            if ($update_stmt->execute()) {
                $_SESSION['success'] = 'Your account has been successfully verified!';
            } else {
                $_SESSION['error'] = 'There was an error verifying your account. Please try again.';
            }
        } else {
            $_SESSION['error'] = 'Invalid verification code.';
        }
    } else {
        $_SESSION['error'] = 'Account not found.';
    }
} else {
    $_SESSION['error'] = 'Invalid request.';
}

header('Location: verification_status.php');
exit();
