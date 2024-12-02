<?php
session_start();
include('database/connection.php');

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'] ?? null;


// Post report query
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Other fields (existing code)
    $incidentType = $_POST['incident_type'] ?? '';
    $otherIncident = $_POST['otherIncident'] ?? '';
    $description = $_POST['incident_description'] ?? '';
    $location = $_POST['incident_location'] ?? '';
    $landmark = $_POST['incident_landmark'] ?? '';
    $dateTime = $_POST['incident_datetime'] ?? '';
    $mapLocation = $_POST['incident_location_map'] ?? '';
    
    // Get the latitude and longitude from the form
    $latitude = $_POST['incident_latitude'] ?? null;
    $longitude = $_POST['incident_longitude'] ?? null;

    if ($incidentType === 'Others' && !empty($otherIncident)) {
        $incidentType = $otherIncident;
    }

    // Handle file uploads (existing code)
    $incidentProof = [];
    if (isset($_FILES['incident_proof']) && !empty($_FILES['incident_proof']['name'][0])) {
        $uploadedFiles = $_FILES['incident_proof'];
        $maxFiles = 3;
        $fileNames = [];

        for ($i = 0; $i < min(count($uploadedFiles['name']), $maxFiles); $i++) {
            $fileTmpPath = $uploadedFiles['tmp_name'][$i];
            $fileName = $uploadedFiles['name'][$i];
            $fileSize = $uploadedFiles['size'][$i];
            $fileType = $uploadedFiles['type'][$i];
            $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'application/pdf'];

            if (in_array($fileType, $allowedTypes)) {
                $uniqueFileName = time() . '_' . $fileName;
                $uploadDirectory = 'assets/images/proofs/';
                $filePath = $uploadDirectory . basename($uniqueFileName);
                if (move_uploaded_file($fileTmpPath, $filePath)) {
                    $fileNames[] = $uniqueFileName;
                }
            }
        }

        if (!empty($fileNames)) {
            $incidentProof = json_encode($fileNames);
        }
    }

    // Update SQL query to insert latitude and longitude
    $query = "INSERT INTO tbl_incidents 
              (user_id, incident_type, incident_description, incident_proof, incident_location, 
               incident_landmark, incident_datetime, incident_location_map, latitude, longitude, status, created_at, updated_at) 
              VALUES 
              (:user_id, :incident_type, :incident_description, :incident_proof, :incident_location, 
               :incident_landmark, :incident_datetime, :incident_location_map, :latitude, :longitude, :status, NOW(), NOW())";

    $stmt = $conn->prepare($query);
    $status = 'Pending';
    $stmt->bindParam(':user_id', $user_id);
    $stmt->bindParam(':incident_type', $incidentType);
    $stmt->bindParam(':incident_description', $description);
    $stmt->bindParam(':incident_proof', $incidentProof);
    $stmt->bindParam(':incident_location', $location);
    $stmt->bindParam(':incident_landmark', $landmark);
    $stmt->bindParam(':incident_datetime', $dateTime);
    $stmt->bindParam(':incident_location_map', $mapLocation);
    $stmt->bindParam(':latitude', $latitude);
    $stmt->bindParam(':longitude', $longitude);
    $stmt->bindParam(':status', $status);

    if ($stmt->execute()) {
        $_SESSION['success'] = 'Reports successfully posted. Please wait for the approval of the admin';
        header('Location: reports.php');
        exit();
    } else {
        $_SESSION['error'] = 'Reports failed to post';
        header('Location: reports.php');
        exit();
    }
}

?>





<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Report Incident - Crisis Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <!-- Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />
    <link rel="stylesheet" href="assets/css/home.css">
    <style>
        /* validation */
        #incident_description-error {
            font-size: 15px;
            margin-top: 5px;
            font-weight: 900;
            color: red;
        }

        #incident_proof-error {
            font-size: 15px;
            margin-top: 5px;
            font-weight: 900;
            color: red;
        }

        #incident_location-error {
            font-size: 15px;
            margin-top: 5px;
            font-weight: 900;
            color: red;
        }

        #incident_datetime-error {
            font-size: 15px;
            margin-top: 5px;
            font-weight: 900;
            color: red;
        }

        #mapLocation-error{
            font-size: 15px;
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
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link active dropdown-toggle" href="#" id="reportsDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Reports
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="reportsDropdown">
                            <li><a class="dropdown-item active" href="reports.php">Post Complain</a></li>
                            <li><a class="dropdown-item" href="history.php">View History</a></li>

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
        <div class="row">
            <!-- Header Section -->
            <div class="col-lg-8 col-md-8">
                <h2>Report an Incident</h2>
            </div>
            <div class="col-lg-4 col-md-4 text-end mb-2">
                <span id="currentDateTime"></span>
            </div>
        </div>

        <!-- Incident Report Form and Map -->
        <div class="row">
            <!-- Form Section (Responsive Column) -->
            <div class="col-lg-8 col-md-12 form-container">
                <div class="report-form">
                    <form id="form_advanced_validation" action="#" method="POST" enctype="multipart/form-data">
                        <?php if (isset($_SESSION['user_id'])): ?>
                            <input type="hidden" name="user_id" value="<?php echo $_SESSION['user_id']; ?>">
                        <?php endif; ?>
                            <div class="form-group form-float">
                            <label for="incident_type" style="color: #212529; font-weight: 600;">Type of Incident</label>
                            <div class="form-line">
                                <select class="form-select" id="incident_type" name="incident_type" required>
                                    <option value="Fire">Fire</option>
                                    <option value="Flood">Flood</option>
                                    <option value="Earthquake">Earthquake</option>
                                    <option value="Accident">Accident</option>
                                    <option value="Theft">Theft</option>
                                    <option value="Others">Others (Specify)</option>
                                </select>                          
                            </div>
                        </div>

                        <div class="form-group form-float" id="otherIncidentInput" style="display: none;">
                            <label for="otherIncident" style="color: #212529; font-weight: 600;" class="form-label">Please Specify the Incident</label>
                            <div class="form-line">
                                <input type="text" id="otherIncident" class="form-control" name="otherIncident">
                            </div>
                        </div>

                            <div class="form-group form-float" style="margin-top: 30px !important;">
                            <label for="incident_description" style="color: #212529; font-weight: 600;" class="form-label">Description</label>
                            <div class="form-line">
                            <textarea name="incident_description" cols="30" rows="5" class="form-control no-resize" required="" aria-required="true" aria-invalid="true"></textarea>
                            </div>
                        </div>

                            <div class="form-group form-float" style="margin-top: 30px !important;">
                            <label style="color: #212529; font-weight: 600;" class="form-label">Upload Supporting Picture</label>
                            <div class="form-line">
                                <input class="form-control" type="file" id="incident_proof" name="incident_proof[]" multiple required>
                            </div>
                        </div>


                        <div id="filePreview" class="row mb-3 mt-3" style="display: none;">
                            <label class="form-label">Selected Files:</label>
                        </div>

                            <div class="form-group form-float" style="margin-top: 30px !important;">
                            <label style="color: #212529; font-weight: 600;" class="form-label">Location</label>
                            <div class="form-line">
                                <input type="text" class="form-control" id="incident_location" name="incident_location" required>
                            </div>
                        </div>

                            <div class="form-group form-float" style="margin-top: 30px !important;">
                            <label style="color: #212529; font-weight: 600;" class="form-label">Landmark (Optional)</label>
                            <div class="form-line">
                            <input type="text" class="form-control" id="incident_landmark" name="incident_landmark">
                            </div>
                        </div>

                            <div class="form-group form-float" style="margin-top: 30px !important;">
                            <label style="color: #212529; font-weight: 600;" class="form-label">Date and Time</label>
                            <div class="form-line">
                            <input type="datetime-local" class="form-control" id="incident_datetime" name="incident_datetime" required>
                            </div>
                        </div>
                        <input type="hidden" id="incident_latitude" name="incident_latitude" value="">
                        <input type="hidden" id="incident_longitude" name="incident_longitude" value="">
                        <div class="form-group form-float" style="margin-top: 30px !important;">
                            <label style="color: #212529; font-weight: 600;" class="form-label">Location on Map <span style="color: red;">(USE MAP TO PIN)</span></label>
                            <div class="form-line">
                                <input style="background-color: lightgrey;" type="text" class="form-control" id="mapLocation" name="incident_location_map" placeholder="Pin the location" readonly required>
                            </div>
                        </div>

                        <!-- Post Button -->
                        <div class="text-end mt-5">
                            <button type="submit" class="btn btn-primary">Post Report</button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Map Section (Responsive Column) -->
            <div class="col-lg-4 col-md-12 map-container">
                <div id="map"></div>
                <div class="text-end">
                    <button id="fullscreenBtn" class="btn btn-success">FULLSCREEN MAP</button>
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/screenfull.js/5.1.0/screenfull.min.js"></script>

    <!-- JQUERY VALIDATION -->
    <script src="assets/plugins/jquery-validation/jquery.validate.js"></script>
    <script src="assets/js/pages/forms/form-validation.js"></script>

    <!-- SWEETALERT POST REPORTS -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $_SESSION['success']; ?>'
            }).then(() => {
                window.location.href = 'reports.php';
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
                window.location.href = 'reports.php';
            });
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <!-- MAP AND DATE TIME -->
    <script>
var map = L.map('map').setView([13.41, 122.56], 6);
L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
    attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
}).addTo(map);

map.setMaxBounds([
    [4.6, 116.9],
    [21.4, 126.6]
]);

var marker = L.marker([13.41, 122.56], {
    draggable: true
}).addTo(map);

// Capture latitude and longitude when the map is clicked
map.on('click', function (e) {
    var lat = e.latlng.lat;
    var lon = e.latlng.lng;

    // Set the marker at the clicked location
    marker.setLatLng([lat, lon]);

    // Populate hidden input fields with latitude and longitude
    document.getElementById('incident_latitude').value = lat;
    document.getElementById('incident_longitude').value = lon;

    // Optionally, use reverse geocoding to get the place name
    var url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            var placeName = data.display_name || "Location not found";
            document.getElementById('mapLocation').value = placeName; // Set place name in the input field
        })
        .catch(error => {
            console.error("Error fetching place name:", error);
            document.getElementById('mapLocation').value = "Location not found";
        });
});

// Enable dragging of the marker
marker.dragging.enable();

// Add a listener for marker drag end to update location
marker.on('dragend', function (e) {
    var lat = e.target.getLatLng().lat;
    var lon = e.target.getLatLng().lng;

    // Update input fields with latitude and longitude
    document.getElementById('incident_latitude').value = lat;
    document.getElementById('incident_longitude').value = lon;

    // Use reverse geocoding to get the place name
    var url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lon}&format=json`;

    fetch(url)
        .then(response => response.json())
        .then(data => {
            var placeName = data.display_name || "Location not found";
            document.getElementById('mapLocation').value = placeName; // Set place name in the input field
        })
        .catch(error => {
            console.error("Error fetching place name:", error);
            document.getElementById('mapLocation').value = "Location not found";
        });
});


        function updateDateTime() {
            const options = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                timeZone: 'Asia/Manila'
            };

            const date = new Date().toLocaleString('en-US', options);
            document.getElementById('currentDateTime').textContent = "Today: " + date;
        }

        setInterval(updateDateTime, 1000);

        const fullscreenBtn = document.getElementById('fullscreenBtn');

        fullscreenBtn.addEventListener('click', function() {
            if (screenfull.isEnabled) {
                screenfull.toggle(document.getElementById('map'));
            }
        });
    </script>

    <!-- DISPLAYING IMAGE -->
    <script>
        document.getElementById('incident_proof').addEventListener('change', function(event) {
            const fileInput = event.target;
            const filePreview = document.getElementById('filePreview');
            filePreview.innerHTML = '';

            if (fileInput.files.length > 0) {
                filePreview.style.display = 'block';
                Array.from(fileInput.files).forEach(file => {
                    const fileType = file.type.split('/')[0];

                    const fileWrapper = document.createElement('div');
                    fileWrapper.classList.add('col-6', 'col-sm-4', 'mb-2');

                    if (fileType === 'image') {
                        const img = document.createElement('img');
                        img.classList.add('img-fluid', 'rounded');
                        img.style.maxHeight = '100px';
                        img.src = URL.createObjectURL(file);
                        fileWrapper.appendChild(img);
                    } else {
                        const icon = document.createElement('i');
                        icon.classList.add('fas', 'fa-file-alt', 'fa-3x');
                        fileWrapper.appendChild(icon);
                    }

                    filePreview.appendChild(fileWrapper);
                });
            }
        });
    </script>

    <!-- OTHERS SPECIFY -->
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
    </script>

</body>

</html>