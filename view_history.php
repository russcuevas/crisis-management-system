<?php
session_start();
include('database/connection.php');

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;

if (!$is_logged_in) {
    header('Location: login.php');
    exit();
}

$incident_id = $_GET['id'] ?? null;

if ($incident_id) {
    $query = "SELECT * FROM tbl_incidents WHERE incident_id = :incident_id AND user_id = :user_id";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':incident_id', $incident_id, PDO::PARAM_INT);
    $stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
    $stmt->execute();
    $incident = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$incident) {
        header('Location: history.php');
        exit();
    }

    // Decode respondents_id (JSON)
    $respondent_types = [];
    $respondents_ids = json_decode($incident['respondents_id'], true);

    if (!empty($respondents_ids) && is_array($respondents_ids)) {
        // Fetch corresponding type values from tbl_responders
        $placeholders = implode(',', array_fill(0, count($respondents_ids), '?'));
        $sql = "SELECT type FROM tbl_responders WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($respondents_ids);
        $respondent_types = $stmt->fetchAll(PDO::FETCH_COLUMN);
    }

    // Store formatted responders in the incident array
    $incident['responders'] = !empty($respondent_types) ? implode('<br>', $respondent_types) : 'No Responders';
} else {
    header('Location: history.php');
    exit();
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>View Incident History</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Swiper CSS -->
    <link href="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.css" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />


    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #000000, #3c0f12) !important;
            margin: 0;
            padding: 0;
            color: #333333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        .navbar {
            background-color: #bc1823 !important;
            padding: 15px;
        }

        .navbar .navbar-brand {
            font-weight: 600;
            font-size: 15px;
            color: #fff;
        }

        .navbar .navbar-nav .nav-link {
            color: #fff;
        }

        .navbar .navbar-nav .nav-link:hover {
            color: black;
        }

        .navbar-toggler-icon {
            color: white !important;
        }

        .nav-link.active {
            color: black !important;
            font-weight: 900;
        }

        #map {
            height: 400px;
            width: 100%;
            margin-bottom: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .container {
            max-width: 1000px;
            margin-top: 30px;
        }

        .card {
            margin-bottom: 20px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .incident-details h3 {
            font-weight: 600;
        }

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

        .incident-details p {
            font-size: 1rem;
            margin-bottom: 10px;
        }

        .nav-item .dropdown-item.active {
            background-color: #bc1823 !important;
            color: #fff !important;
        }

        .nav-item .dropdown-item:hover {
            background-color: #bc1823;
            color: #fff;
        }

        .swiper-pagination {
            bottom: 10px !important;
        }

        .swiper-button-next,
        .swiper-button-prev {
            background-color: rgba(0, 0, 0, 0.5);
            border-radius: 50%;
        }

        .swiper-button-next,
        .swiper-button-prev {
            color: white;
            top: 50%;
            transform: translateY(-50%);
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
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link active dropdown-toggle" href="#" id="reportsDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Reports
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="reportsDropdown">
                            <li><a class="dropdown-item" href="reports.php">Post Complain</a></li>
                            <li><a class="dropdown-item active" href="history.php">View History</a></li>

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

    <div class="container">
        <h3 style="color: whitesmoke;">Incident History</h3>
        <h6 style="color: whitesmoke;"><?php echo $incident['incident_location_map'] ?></h6>

        <div id="map"></div>

        <div class="card p-4 mt-5">
            <div class="swiper-container">
                <h3>Proofs</h3>
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
            <div class="incident-details mt-5">
                <p><strong>Responders:</strong><br> <span style="color: red;"><?php echo $incident['responders']; ?></span></p>
                <p><strong>Type:</strong> <?php echo $incident['incident_type']; ?></p>
                <p><strong>Description:</strong> <?php echo $incident['incident_description']; ?></p>
                <p><strong>Location:</strong> <?php echo $incident['incident_location_map']; ?></p>
                <p><strong>Landmark:</strong> <?php echo $incident['incident_landmark']; ?></p>
                <p><strong>Date & Time:</strong> <?php echo $incident['incident_datetime']; ?></p>
                <p><strong>Status:</strong> <span style="color: green;"><?php echo $incident['status']; ?></span></p>
                <p><strong>Date Posted:</strong> <?php echo $incident['created_at']; ?></p>
            </div>
            <div class="col-md-12 text-end mt-5">
                <?php if ($incident['status'] === 'Approved'): ?>

                <?php else: ?>
                    <a href="edit_request.php?id=<?php echo $incident['incident_id']; ?>"
                        class="btn btn-warning">
                        EDIT REQUEST
                    </a>
                    <a href="#"
                        class="btn btn-danger"
                        data-bs-toggle="modal"
                        data-bs-target="#deleteModal"
                        onclick="setIncidentId(<?php echo $incident['incident_id']; ?>)">
                        DELETE REQUEST
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- DELETE MODAL -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">Confirm Deletion</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    Are you sure you want to delete this incident? <br> This action cannot be undone.
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <a href="#" id="confirmDelete" class="btn btn-danger">Delete</a>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

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

    <!-- SWIPER AND MAP -->
    <script>
        const swiper = new Swiper('.swiper-container', {
            loop: true,
            slidesPerView: 1,
            spaceBetween: 10,
            pagination: {
                el: '.swiper-pagination',
                clickable: true,
            },
            navigation: {
                nextEl: '.swiper-button-next',
                prevEl: '.swiper-button-prev',
            },
            autoplay: {
                delay: 3000,
                disableOnInteraction: false,
            },
        });

        var map = L.map('map').setView([13.41, 122.56], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        map.setMaxBounds([
            [4.6, 116.9],
            [21.4, 126.6]
        ]);

        var latitude = <?php echo json_encode($incident['latitude']); ?>;
        var longitude = <?php echo json_encode($incident['longitude']); ?>;

        if (latitude && longitude) {
            var marker = L.marker([latitude, longitude]).addTo(map)
                .bindPopup('<b><?php echo $incident['incident_type']; ?></b><br>' +
                    'Location: <?php echo $incident['incident_location_map']; ?><br>' +
                    'Status: <?php echo $incident['status']; ?>');
            map.setView([latitude, longitude], 18);
        } else {
            console.error("Latitude or Longitude data is missing");
        }
    </script>

    <!-- CONFIRM DELETION -->
    <script>
        function setIncidentId(incidentId) {
            const confirmDeleteButton = document.getElementById('confirmDelete');
            confirmDeleteButton.setAttribute('href', 'delete_incident.php?id=' + incidentId);
        }
    </script>
</body>

</html>