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
                            <li><a class="dropdown-item" href="profile.php">Change Details</a></li>
                            <li><a class="dropdown-item" href="#">Logout</a></li>
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
                    <form action="#" method="POST" enctype="multipart/form-data">
                        <!-- Incident Type Dropdown -->
                        <div class="mb-3">
                            <label for="incidentType" class="form-label">Type of Incident</label>
                            <select class="form-select" id="incidentType" name="incidentType" required>
                                <option value="Fire">Fire</option>
                                <option value="Flood">Flood</option>
                                <option value="Earthquake">Earthquake</option>
                                <option value="Accident">Accident</option>
                                <option value="Theft">Theft</option>
                                <option value="Others">Others (Specify)</option>
                            </select>
                        </div>

                        <!-- Other Incident Type Input -->
                        <div class="mb-3" id="otherIncidentInput" style="display: none;">
                            <label for="otherIncident" class="form-label">Please Specify the Incident</label>
                            <input type="text" class="form-control" id="otherIncident" name="otherIncident">
                        </div>

                        <!-- File Upload (Image or Document) -->
                        <div class="mb-3">
                            <label for="fileUpload" class="form-label">Upload Supporting File</label>
                            <input class="form-control" type="file" id="fileUpload" name="fileUpload">
                        </div>

                        <!-- Location with Landmark -->
                        <div class="mb-3">
                            <label for="location" class="form-label">Location</label>
                            <input type="text" class="form-control" id="location" name="location" required>
                        </div>

                        <div class="mb-3">
                            <label for="landmark" class="form-label">Landmark (Optional)</label>
                            <input type="text" class="form-control" id="landmark" name="landmark">
                        </div>

                        <!-- Date and Time -->
                        <div class="mb-3">
                            <label for="dateTime" class="form-label">Date and Time</label>
                            <input type="datetime-local" class="form-control" id="dateTime" name="dateTime" required>
                        </div>

                        <!-- Map Location (Pin Location) -->
                        <div class="mb-3">
                            <label for="mapLocation" class="form-label">Location on Map</label>
                            <input type="text" class="form-control" id="mapLocation" name="mapLocation"
                                placeholder="Pin the location" readonly>
                        </div>

                        <!-- Post Button -->
                        <div class="text-end">
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

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

    <!-- Leaflet JS -->
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/screenfull.js/5.1.0/screenfull.min.js"></script>


    <script>
        // Initialize the map
        var map = L.map('map').setView([13.41, 122.56], 6);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        map.setMaxBounds([
            [4.6, 116.9],
            [21.4, 126.6]
        ]);

        // Create a marker
        var marker = L.marker([13.41, 122.56]).addTo(map);

        // Map click event to update marker position and location name
        map.on('click', function(e) {
            var lat = e.latlng.lat;
            var lng = e.latlng.lng;

            marker.setLatLng([lat, lng]);

            var url = `https://nominatim.openstreetmap.org/reverse?lat=${lat}&lon=${lng}&format=json`;

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    var placeName = data.display_name || "Location not found";
                    document.getElementById('mapLocation').value = placeName;
                })
                .catch(error => {
                    console.error("Error fetching place name:", error);
                    document.getElementById('mapLocation').value = "Location not found";
                });
        });

        // Enable marker dragging
        marker.dragging.enable();

        // Date and time update function
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

        // Fullscreen toggle functionality
        const fullscreenBtn = document.getElementById('fullscreenBtn');

        // When the fullscreen button is clicked
        fullscreenBtn.addEventListener('click', function() {
            if (screenfull.isEnabled) {
                screenfull.toggle(document.getElementById('map')); // Toggle fullscreen for the map div
            }
        });
    </script>

</body>

</html>