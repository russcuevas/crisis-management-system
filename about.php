<?php
session_start();
include('database/connection.php');
$is_logged_in = isset($_SESSION['user_id']);

// feedback query
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $question = $_POST['question'];
    $email = $_POST['email'];
    $feedback = $_POST['feedback'];

    $stmt = $conn->prepare("INSERT INTO `tbl_feedback` (email, feedback, fullname, question) VALUES (?, ?, ?, ?)");
    $stmt->execute([$email, $feedback, $fullname, $question]);

    if ($stmt) {
        $_SESSION['success'] = 'Thank you for sending your feedback! your question will answer through email please check';
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
    <style>
        .content-text {
            color: white !important;
        }

        /* validation */
        #fullname-error {
            font-size: 12px;
            margin-top: 5px;
            font-weight: 900;
            color: red;
        }

        #question-error {
            font-size: 12px;
            margin-top: 5px;
            font-weight: 900;
            color: red;
        }

        #email-error {
            font-size: 12px;
            margin-top: 5px;
            font-weight: 900;
            color: red;
        }

        #feedback-error {
            font-size: 12px;
            margin-top: 5px;
            font-weight: 900;
            color: red;
        }

        .form-group .form-line.error {
            border: 2px solid red !important;
        }
    </style>
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
                    <div class="logo">
                        <img src="https://th.bing.com/th/id/OIP.no_UV27v6Gohe9mV3Ka6mwAAAA?rs=1&pid=ImgDetMain"
                            alt="Logo 1" class="img-fluid" style="max-width: 100px; border-radius: 50px;">
                    </div>
                    <div class="logo">
                        <img src="https://th.bing.com/th/id/OIP.no_UV27v6Gohe9mV3Ka6mwAAAA?rs=1&pid=ImgDetMain"
                            alt="Logo 2" class="img-fluid" style="max-width: 100px; border-radius: 50px;">
                    </div>
                    <div class="logo">
                        <img src="https://th.bing.com/th/id/OIP.no_UV27v6Gohe9mV3Ka6mwAAAA?rs=1&pid=ImgDetMain"
                            alt="Logo 3" class="img-fluid" style="max-width: 100px; border-radius: 50px;">
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

        <div class="mt-5">
            <h3 class="section-title text-center" style="color: whitesmoke !important;">Ask Question and make <br> a feedback here</h3>

            <div class="row">
                <div class="col-12 col-md-6 mb-4">
                    <div class="feedback-form">
                        <form id="form_advanced_validation" action="#" method="POST">

                            <div class="form-group form-float" style="margin-top: 10px !important;">
                                <label style="color: #212529; font-weight: 600;" class="form-label">Your Name</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="fullname" required>
                                </div>
                            </div>


                            <div class="form-group form-float" style="margin-top: 10px !important;">
                                <label style="color: #212529; font-weight: 600;" class="form-label">Email</label>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="email" required>
                                </div>
                            </div>


                            <div class="form-group form-float" style="margin-top: 10px !important;">
                                <label style="color: #212529; font-weight: 600;" class="form-label">Your feedback</label>
                                <div class="form-line">
                                    <textarea name="feedback" cols="30" rows="5" class="form-control no-resize" required="" aria-required="true" aria-invalid="true"></textarea>
                                </div>
                            </div>

                            <div class="text-end mt-5">
                                <button type="submit" class="btn btn-primary">Submit Feedback</button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Image Column (Right) -->
                <div class="col-12 col-md-6 mb-4">
                    <img src="assets/images/about-contact.png" alt="Contact Image" class="img-fluid rounded-3"
                        style="width: 100%;">

                    <p class="content-text text-left mt-4">Your feedback is important to us as it helps us improve our system
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

    <!-- JQUERY -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>
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

    <!-- JQUERY VALIDATION -->
    <script src="assets/plugins/jquery-validation/jquery.validate.js"></script>
    <script src="assets/js/pages/forms/form-validation.js"></script>
</body>

</html>