<?php
session_start();
include('database/connection.php');
$is_logged_in = isset($_SESSION['user_id']);

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$user_id = $_SESSION['user_id'];
$get_user = "SELECT fullname, email, contact, province, purok, barangay, municipality, profile_picture FROM tbl_users WHERE id = ?";
$stmt = $conn->prepare($get_user);
$stmt->bindParam(1, $user_id, PDO::PARAM_INT);
$stmt->execute();
$user = $stmt->fetch(PDO::FETCH_ASSOC);


if ($user) {
    $fullname = $user['fullname'];
    $email = $user['email'];
    $contact_number = $user['contact'];
    $province = $user['province'];
    $purok = $user['purok'];
    $barangay = $user['barangay'];
    $municipality = $user['municipality'];
    $profile_picture = $user['profile_picture'];
} else {
    echo "User not found.";
    exit();
}

//update profile
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];
    $contact = $_POST['contact'];
    $province = $_POST['province'];
    $purok = $_POST['purok'];
    $barangay = $_POST['barangay'];
    $municipality = $_POST['municipality'];
    $new_password = $_POST['password'];
    $confirm_password = $_POST['password_confirmation'];

    if (!empty($new_password) && $new_password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match!';
        header('Location: profile.php');
        exit();
    }

    if (!empty($new_password)) {
        $hashed_password = sha1($new_password);

        $query = "UPDATE tbl_users SET password = :password, contact = :contact, province = :province, 
                  purok = :purok, barangay = :barangay, municipality = :municipality WHERE id = :user_id";
        $stmt = $conn->prepare($query);
        $stmt->bindParam(':password', $hashed_password);
    } else {
        $query = "UPDATE tbl_users SET contact = :contact, province = :province, purok = :purok, 
                  barangay = :barangay, municipality = :municipality WHERE id = :user_id";
        $stmt = $conn->prepare($query);
    }

    $stmt->bindParam(':contact', $contact);
    $stmt->bindParam(':province', $province);
    $stmt->bindParam(':purok', $purok);
    $stmt->bindParam(':barangay', $barangay);
    $stmt->bindParam(':municipality', $municipality);
    $stmt->bindParam(':user_id', $user_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Profile updated successfully!';
        header('Location: profile.php');
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update profile!';
        header('Location: profile.php');
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crisis Management System</title>
    <!-- CUSTOM CSS AND BOOTSTRAP -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/profile.css">
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php"><img style="height: 50px; width: 50px; border-radius: 50%;"
                    src="assets/images/login/crisis.jpg" alt=""> Crisis Management
                System</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Reports
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="reportsDropdown">
                            <?php if ($is_logged_in): ?>
                                <li><a class="dropdown-item" href="reports.php">Post Complain</a></li>
                                <li><a class="dropdown-item" href="history.php">View History</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="reports.php">Post Complain</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Profile
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <?php if ($is_logged_in): ?>
                                <li><a class="dropdown-item" href="profile.php">Change Details</a></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="login.php">Login</a></li>
                                <li><a class="dropdown-item" href="register.php">Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="profile-container">
        <div class="profile-box-header">
            <div class="profile-header">
                <?php if ($profile_picture): ?>
                    <img src="assets/images/profile/<?php echo htmlspecialchars($profile_picture); ?>" alt="Profile Picture" />
                <?php else: ?>
                    <p>No profile picture available.</p>
                <?php endif; ?>
                <h2><?php echo htmlspecialchars($fullname); ?></h2>
            </div>
        </div>

        <div class="profile-details">
            <p><strong>Email:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Contact Number:</strong> <?php echo htmlspecialchars($contact_number); ?></p>
            <p><strong>Province:</strong> <?php echo htmlspecialchars($province); ?></p>
            <p><strong>Purok:</strong> <?php echo htmlspecialchars($purok); ?></p>
            <p><strong>Barangay:</strong> <?php echo htmlspecialchars($barangay); ?></p>
            <p><strong>Municipality:</strong> <?php echo htmlspecialchars($municipality); ?></p>
            <button class="btn btn-warning update-btn" data-bs-toggle="modal" data-bs-target="#updateProfileModal">Update Profile</button>
        </div>

        <div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateProfileModalLabel">Update Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="form_advanced_validation" method="POST" action="">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group form-float">
                                        <label style="color: #212529; font-weight: 600;" class="form-label">Email <span style="color: green; font-size: 15px;">(Verified)</span></label>
                                        <div class="form-line">
                                            <input style="background-color: lightgray !important;" type="email" class="form-control" name="email" value="<?php echo htmlspecialchars($email); ?>" readonly>
                                        </div>
                                    </div>

                                    <div class="form-group form-float" style="margin-top: 10px !important;">
                                        <label style="color: #212529; font-weight: 600;" class="form-label">New Password</label>
                                        <div class="form-line">
                                            <input type="password" class="form-control" name="password" maxlength="12" minlength="6">
                                        </div>
                                    </div>

                                    <div class="form-group form-float" style="margin-top: 10px !important;">
                                        <label style="color: #212529; font-weight: 600;" class="form-label">Confirm Password</label>
                                        <div class="form-line">
                                            <input type="password" class="form-control" name="password_confirmation" maxlength="12" minlength="6">
                                        </div>
                                    </div>


                                    <div class="form-group form-float" style="margin-top: 10px !important;">
                                        <label style="color: #212529; font-weight: 600;" class="form-label">Contact Number</label>
                                        <div class="form-line">
                                            <input type="tel" class="form-control" name="contact" value="<?php echo htmlspecialchars($contact_number); ?>" maxlength="12" minlength="6" required>
                                        </div>
                                    </div>

                                </div>

                                <div class="col-md-6 ms-auto">

                                    <div class="form-group form-float">
                                        <label style="color: #212529; font-weight: 600;" class="form-label">Province</label>
                                        <div class="form-line">
                                            <input type="text" class="form-control" name="province" value="<?php echo htmlspecialchars($province); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group form-float" style="margin-top: 10px !important;">
                                        <label style="color: #212529; font-weight: 600;" class="form-label">Purok</label>
                                        <div class="form-line">
                                            <input type="text" class="form-control" name="purok" value="<?php echo htmlspecialchars($purok); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group form-float" style="margin-top: 10px !important;">
                                        <label style="color: #212529; font-weight: 600;" class="form-label">Barangay</label>
                                        <div class="form-line">
                                            <input type="text" class="form-control" name="barangay" value="<?php echo htmlspecialchars($barangay); ?>" required>
                                        </div>
                                    </div>

                                    <div class="form-group form-float" style="margin-top: 10px !important;">
                                        <label style="color: #212529; font-weight: 600;" class="form-label">Municipality</label>
                                        <div class="form-line">
                                            <input type="text" class="form-control" name="municipality" value="<?php echo htmlspecialchars($municipality); ?>" required>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-12 text-end mt-5">
                                <button type="submit" class="btn btn-danger">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <footer>
        <p>&copy; 2024 Crisis Management System - All Rights Reserved | <a href="#">Privacy Policy</a> | <a
                href="#">Terms</a></p>
    </footer>

    <!-- JQUERY -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- SWEETALERT UPDATE PROFILE -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $_SESSION['success']; ?>'
            }).then(() => {
                window.location.href = 'profile.php';
            });
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo $_SESSION['error']; ?>'
            }).then(() => {
                window.location.href = 'profile.php';
            });
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- BOOTSTRAP 5 -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

    <!-- JQUERY VALIDATION -->
    <script src="assets/plugins/jquery-validation/jquery.validate.js"></script>
    <script src="assets/js/pages/forms/form-validation.js"></script>

</body>

</html>