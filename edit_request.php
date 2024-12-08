<?php
session_start();
include('database/connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$incidentId = $_GET['id'] ?? null;
if (!$incidentId) {
    header('Location: history.php');
    exit();
}

// fetch incident
$query = "SELECT * FROM tbl_incidents WHERE incident_id = :incident_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':incident_id', $incidentId);
$stmt->execute();
$incident = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$incident) {
    header('Location: history.php');
    exit();
}

// edit incident request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $incidentType = $_POST['incident_type'];
    $otherIncident = $_POST['otherIncident'] ?? '';
    $description = $_POST['incident_description'];
    $location = $_POST['incident_location'];
    $landmark = $_POST['incident_landmark'];
    $dateTime = $_POST['incident_datetime'];
    $mapLocation = $_POST['incident_location_map'];
    $latitude = $_POST['incident_latitude'];
    $longitude = $_POST['incident_longitude'];

    if ($incidentType === 'Others' && !empty($otherIncident)) {
        $incidentType = $otherIncident;
    }

    $updateQuery = "UPDATE tbl_incidents 
                    SET incident_type = :incident_type, incident_description = :incident_description,
                        incident_location = :incident_location, incident_landmark = :incident_landmark,
                        incident_datetime = :incident_datetime, incident_location_map = :incident_location_map,
                        latitude = :latitude, longitude = :longitude, updated_at = NOW()
                    WHERE incident_id = :incident_id";

    $stmt = $conn->prepare($updateQuery);
    $stmt->bindParam(':incident_type', $incidentType);
    $stmt->bindParam(':incident_description', $description);
    $stmt->bindParam(':incident_location', $location);
    $stmt->bindParam(':incident_landmark', $landmark);
    $stmt->bindParam(':incident_datetime', $dateTime);
    $stmt->bindParam(':incident_location_map', $mapLocation);
    $stmt->bindParam(':latitude', $latitude);
    $stmt->bindParam(':longitude', $longitude);
    $stmt->bindParam(':incident_id', $incidentId);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Incident updated successfully';
        header('Location: view_history.php?id=' . $incidentId);
        exit();
    } else {
        $_SESSION['error'] = 'Failed to update the incident';
        header('Location: edit_request.php?id=' . $incidentId);
        exit();
    }
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

    <!-- Mapbox CSS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.css" rel="stylesheet" />

    <!-- Mapbox JS -->
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.js"></script>

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
                        <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button"
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

    <div class="container mt-5">
        <h2 style="color: whitesmoke;">Edit Incident Request</h2>
        <div id="map" style="height: 400px;"></div>
        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label style="color: whitesmoke;" for="mapLocation">Location on Map <span style="color: red;">(USE MAP TO PIN)</span></label>
                <input style="background-color: #333333; color: white;" type="text" class="form-control" id="mapLocation" name="incident_location_map" value="<?= htmlspecialchars($incident['incident_location_map']); ?>" readonly required>
            </div>


            <div class="form-group">
                <label style="color: whitesmoke;" for="incident_type">Type of Incident</label>
                <select class="form-select" id="incident_type" name="incident_type" required>
                    <option value="Fire" <?= $incident['incident_type'] == 'Fire' ? 'selected' : ''; ?>>Fire</option>
                    <option value="Flood" <?= $incident['incident_type'] == 'Flood' ? 'selected' : ''; ?>>Flood</option>
                    <option value="Earthquake" <?= $incident['incident_type'] == 'Earthquake' ? 'selected' : ''; ?>>Earthquake</option>
                    <option value="Accident" <?= $incident['incident_type'] == 'Accident' ? 'selected' : ''; ?>>Accident</option>
                    <option value="Theft" <?= $incident['incident_type'] == 'Theft' ? 'selected' : ''; ?>>Theft</option>
                    <option value="Others" <?= $incident['incident_type'] == 'Others' ? 'selected' : ''; ?>>Others (Specify)</option>
                </select>
            </div>

            <div style="color: whitesmoke;" class="form-group" id="otherIncidentInput" style="display: none;">
                <label for="otherIncident">Specify Incident</label>
                <input type="text" class="form-control" id="otherIncident" name="otherIncident" value="<?= htmlspecialchars($incident['incident_type']); ?>">
            </div>

            <div style="color: whitesmoke;" class="form-group">
                <label for="incident_description">Description</label>
                <textarea name="incident_description" class="form-control"><?= htmlspecialchars($incident['incident_description']); ?></textarea>
            </div>

            <div style="color: whitesmoke;" class="form-group">
                <label for="incident_location">Location</label>
                <input type="text" class="form-control" id="incident_location" name="incident_location" value="<?= htmlspecialchars($incident['incident_location']); ?>" required>
            </div>

            <div style="color: whitesmoke;" class="form-group">
                <label for="incident_landmark">Landmark (Optional)</label>
                <input type="text" class="form-control" id="incident_landmark" name="incident_landmark" value="<?= htmlspecialchars($incident['incident_landmark']); ?>">
            </div>

            <div style="color: whitesmoke;" class="form-group">
                <label for="incident_datetime">Date and Time</label>
                <input type="datetime-local" class="form-control" id="incident_datetime" name="incident_datetime" value="<?= date('Y-m-d\TH:i', strtotime($incident['incident_datetime'])); ?>" required>
            </div>

            <input type="hidden" id="incident_latitude" name="incident_latitude" value="<?= $incident['latitude']; ?>">
            <input type="hidden" id="incident_longitude" name="incident_longitude" value="<?= $incident['longitude']; ?>">

            <div class="col-md-12 text-end mt-2 mb-2">
                <button type="submit" class="btn btn-primary">Update Report</button>
                <a href="view_history.php?id=<?= $incidentId; ?>" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz" crossorigin="anonymous"></script>

    <!-- Swiper JS -->
    <script src="https://cdn.jsdelivr.net/npm/swiper@9/swiper-bundle.min.js"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <!-- Sweetalert JS -->
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

    <script>
        var map = L.map('map').setView([<?= $incident['latitude']; ?>, <?= $incident['longitude']; ?>], 18); // Changed zoom level to 18
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        var marker = L.marker([<?= $incident['latitude']; ?>, <?= $incident['longitude']; ?>], {
            draggable: true
        }).addTo(map);

        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lon = e.latlng.lng;

            marker.setLatLng([lat, lon]);

            document.getElementById('incident_latitude').value = lat;
            document.getElementById('incident_longitude').value = lon;

            var url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('mapLocation').value = data.display_name;
                });
        });

        marker.on('dragend', function(e) {
            var lat = e.target.getLatLng().lat;
            var lon = e.target.getLatLng().lng;

            document.getElementById('incident_latitude').value = lat;
            document.getElementById('incident_longitude').value = lon;

            var url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('mapLocation').value = data.display_name;
                });
        });
    </script>


    <script>
        document.getElementById('incident_type').addEventListener('change', function() {
            var otherIncidentInput = document.getElementById('otherIncidentInput');
            var incidentType = this.value;

            if (incidentType === 'Others') {
                otherIncidentInput.style.display = 'block';
            } else {
                otherIncidentInput.style.display = 'none';
            }
        });

        document.addEventListener('DOMContentLoaded', function() {
            var incidentType = document.getElementById('incident_type').value;
            if (incidentType === 'Others') {
                document.getElementById('otherIncidentInput').style.display = 'block';
            } else {
                document.getElementById('otherIncidentInput').style.display = 'none';
            }
        });
    </script>

</body>

</html>