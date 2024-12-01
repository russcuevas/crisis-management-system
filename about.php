<?php
session_start();
include('database/connection.php');
$is_logged_in = isset($_SESSION['user_id']);

// feedback query
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $feedback = $_POST['feedback'];

    $stmt = $conn->prepare("INSERT INTO `tbl_feedback` (email, feedback, fullname) VALUES (?, ?, ?)");
    $stmt->execute([$email, $feedback, $fullname]);

    if ($stmt) {
        $_SESSION['success'] = 'Thank you for sending your feedback!';
        header('Location: about.php');
        exit;
    } else {
        $_SESSION['error'] = 'Feedback not sent successfully';
        header('Location: about.php');
        exit;
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - Crisis Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/home.css">

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
                        <a class="nav-link active" href="about.php">About Us</a>
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

    <!-- Main Content -->
    <div class="main-content">
        <h2 class="text-center mb-5">About Us</h2>

        <div class="row">
            <!-- Description of the System -->
            <div class="col-12 col-md-6 mb-4">
                <h3 class="section-title">Crisis Management System</h3>
                <p class="content-text">The Crisis Management System (CMS) is designed to help manage and respond to
                    emergencies and crises effectively. Our system allows for quick access to crucial information,
                    coordination among responders, and real-time updates during critical situations. It aims to
                    streamline communication and improve decision-making during crises to save lives and minimize
                    damage.</p>

                <!-- Logos Section -->
                <div class="d-flex" style="gap: 15px;">
                    <!-- Logo 1 -->
                    <div class="logo">
                        <img src="https://th.bing.com/th/id/OIP.no_UV27v6Gohe9mV3Ka6mwAAAA?rs=1&pid=ImgDetMain"
                            alt="Logo 1" class="img-fluid" style="max-width: 100px;">
                    </div>
                    <!-- Logo 2 -->
                    <div class="logo">
                        <img src="https://th.bing.com/th/id/OIP.no_UV27v6Gohe9mV3Ka6mwAAAA?rs=1&pid=ImgDetMain"
                            alt="Logo 2" class="img-fluid" style="max-width: 100px;">
                    </div>
                    <!-- Logo 3 -->
                    <div class="logo">
                        <img src="https://th.bing.com/th/id/OIP.no_UV27v6Gohe9mV3Ka6mwAAAA?rs=1&pid=ImgDetMain"
                            alt="Logo 3" class="img-fluid" style="max-width: 100px;">
                    </div>
                </div>
            </div>


            <!-- Image Section on the Right -->
            <div class="col-12 col-md-6 mb-4">
                <img style="width: 100%;" src="assets/images/about-banner.jpg" alt="Crisis Management"
                    class="img-fluid rounded-3">
            </div>
        </div>

        <hr>

        <!-- User Feedback Section -->
        <div class="mt-5">
            <h3 class="section-title text-center">We Value Your Feedback</h3>

            <div class="row">
                <!-- Feedback Form Column (Left) -->
                <div class="col-12 col-md-6 mb-4">
                    <div class="feedback-form">
                        <form action="#" method="POST">
                            <div class="mb-3">
                                <label for="fullname" class="form-label">Your Name</label>
                                <input type="text" class="form-control" id="fullname" name="fullname" required>
                            </div>
                            <div class="mb-3">
                                <label for="email" class="form-label">Your Email</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="feedback" class="form-label">Your Feedback</label>
                                <textarea class="form-control" id="feedback" name="feedback" rows="4"
                                    required></textarea>
                            </div>
                            <div class="text-end">
                                <button type="submit" class="btn btn-primary">Submit Feedback</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Image Column (Right) -->
                <div class="col-12 col-md-6 mb-4">
                    <img src="assets/images/about-contact.png" alt="Contact Image" class="img-fluid rounded-3"
                        style="width: 100%;">

                    <p class="content-text text-left">Your feedback is important to us as it helps us improve our system
                        and
                        services.
                        Please share your thoughts, suggestions, or any issues you have encountered using the Crisis
                        Management
                        System.
                    </p>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Crisis Management System - All Rights Reserved | <a href="#">Privacy Policy</a> | <a
                href="#">Terms</a></p>
    </footer>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>
    <!-- Include SweetAlert2 library -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Check if the session has success or error message
        <?php if (isset($_SESSION['success'])): ?>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $_SESSION['success']; ?>',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['success']); ?>
        <?php elseif (isset($_SESSION['error'])): ?>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo $_SESSION['error']; ?>',
                confirmButtonText: 'OK'
            });
            <?php unset($_SESSION['error']); ?>
        <?php endif; ?>
    </script>

</body>

</html>