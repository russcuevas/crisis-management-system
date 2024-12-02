<?php
session_start();
include('database/connection.php');

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

if ($is_logged_in) {
    $query = "SELECT i.*, u.fullname FROM tbl_incidents i
              LEFT JOIN tbl_users u ON i.user_id = u.id
              WHERE i.status = 'Approved' 
              ORDER BY i.created_at DESC";
    $stmt = $conn->prepare($query);
} else {
    $query = "SELECT i.*, u.fullname FROM tbl_incidents i
              LEFT JOIN tbl_users u ON i.user_id = u.id
              WHERE i.status = 'Approved'
              ORDER BY i.created_at DESC";
    $stmt = $conn->prepare($query);
}

$stmt->execute();
$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);

function timeAgo($timestamp)
{
    $created_at = new DateTime($timestamp);
    $now = new DateTime();
    $interval = $now->diff($created_at);

    if ($interval->y > 0) {
        return $interval->y . " year" . ($interval->y > 1 ? "s" : "") . " ago";
    } elseif ($interval->m > 0) {
        return $interval->m . " month" . ($interval->m > 1 ? "s" : "") . " ago";
    } elseif ($interval->d > 0) {
        return $interval->d . " day" . ($interval->d > 1 ? "s" : "") . " ago";
    } elseif ($interval->h > 0) {
        return $interval->h . " hour" . ($interval->h > 1 ? "s" : "") . " ago";
    } elseif ($interval->i > 0) {
        return $interval->i . " minute" . ($interval->i > 1 ? "s" : "") . " ago";
    } else {
        return "Just now";
    }
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crisis Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css"
        integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/home.css">

    <!-- Swiper CSS -->
    <link href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" rel="stylesheet">

    <style>
        .swiper-container {
            width: 100%;
            max-width: 1000px;
            height: auto;
            border-radius: 8px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .swiper-slide img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 8px;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php"><img style="height: 50px; width: 50px; border-radius: 50%;"
                    src="assets/images/login/crisis.jpg" alt=""> Crisis Management System</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="home.php">Home</a>
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

    <!-- Main Content -->
    <div class="container main-content">
        <h2>Home / News Feed</h2>

        <!-- Post Cards -->
        <div class="row">
            <?php foreach ($incidents as $incident): ?>
                <div class="col-12 col-md-6 col-lg-4 mb-4">
                    <div class="card news-feed">

                        <?php if ($is_logged_in && $incident['user_id'] == $user_id): ?>
                            <div class="badge badge-primary" style="position: absolute; top: 10px; right: 10px; z-index: 10; background-color: #007bff; color: white; padding: 5px 10px; font-size: 14px;">
                                My Post
                            </div>
                        <?php endif; ?>

                        <div class="card-header">
                            <i class="fa-solid fa-user"></i> <?php echo htmlspecialchars($incident['fullname']); ?>
                        </div>

                        <div class="card-body">
                            <h5>TYPE OF INCIDENT - <strong><?php echo htmlspecialchars($incident['incident_type']); ?></strong></h5>
                            <div class="swiper-container">
                                <div class="swiper-wrapper">
                                    <?php
                                    $proofs = json_decode($incident['incident_proof'], true);
                                    if ($proofs && is_array($proofs)) {
                                        foreach ($proofs as $proof) {
                                            $proof_image_path = 'assets/images/proofs/' . $proof;
                                            echo '<div class="swiper-slide"><img src="' . $proof_image_path . '" alt="Proof Image" /></div>';
                                        }
                                    } else {
                                        echo "<div class='swiper-slide'><p>No proofs available.</p></div>";
                                    }
                                    ?>
                                </div>
                            </div>

                            <p style="margin-top: 20px !important;"><strong>Location - </strong> <?php echo htmlspecialchars($incident['incident_location_map']); ?></p>
                            <p><strong>Landmark - </strong> <?php echo htmlspecialchars($incident['incident_landmark']); ?></p>
                            <p><strong>Date and Time - </strong> <?php echo htmlspecialchars($incident['incident_datetime']); ?></p>

                            <a href="view_full_information.php?id=<?php echo $incident['incident_id']; ?>" class="btn btn-primary">VIEW FULL INFORMATION</a>
                        </div>

                        <div class="card-footer">
                            <span>Posted: </span>
                            <span><?php echo date('m/d/Y', strtotime($incident['created_at'])); ?> </span><br>
                            <span class="time-ago" data-time="<?php echo $incident['created_at']; ?>">
                                <?php echo timeAgo($incident['created_at']); ?>
                            </span>
                        </div>

                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="text-center mt-4">
            <a href="#" class="btn btn-primary">View More Posts</a>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Crisis Management System - All Rights Reserved | <a href="#">Privacy Policy</a> | <a href="#">Terms</a></p>
    </footer>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>
    <script src="assets/js/home.js"></script>

</body>

</html>