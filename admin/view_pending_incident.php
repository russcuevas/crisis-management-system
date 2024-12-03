<?php
include '../database/connection.php';

session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_GET['incident_id'])) {
    $incident_id = $_GET['incident_id'];

    $sql = "SELECT tbl_incidents.*, tbl_users.fullname 
            FROM tbl_incidents 
            LEFT JOIN tbl_users ON tbl_incidents.user_id = tbl_users.id 
            WHERE tbl_incidents.incident_id = :incident_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':incident_id', $incident_id, PDO::PARAM_INT);
    $stmt->execute();
    $incident = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$incident) {
        $_SESSION['users_error'] = "Incidents Not Found";
        header('location:users.php');
        exit();
    }
} else {
    $_SESSION['users_error'] = "Incidents Not Found";
    header('location:users.php');
    exit();
}
$incident_proof = json_decode($incident['incident_proof'], true);
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=Edge">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Crisis Management</title>
    <!-- Favicon-->
    <link rel="icon" href="favicon.ico" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <!-- Bootstrap Core Css -->
    <link href="plugins/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Waves Effect Css -->
    <link href="plugins/node-waves/waves.css" rel="stylesheet" />

    <!-- Animation Css -->
    <link href="plugins/animate-css/animate.css" rel="stylesheet" />

    <!-- Morris Chart Css-->
    <link href="plugins/morrisjs/morris.css" rel="stylesheet" />
    <!-- JQuery DataTable Css -->
    <link href="plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css" rel="stylesheet">

    <!-- Custom Css -->
    <link href="css/style.css" rel="stylesheet">
    <link href="css/themes/all-themes.css" rel="stylesheet" />
    <!-- Include Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

    <!-- Light Gallery Plugin Css -->
    <link href="plugins/light-gallery/css/lightgallery.css" rel="stylesheet">

    <!-- Mapbox CSS -->
    <link href="https://api.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.css" rel="stylesheet" />

    <!-- Mapbox JS -->
    <script src="https://api.mapbox.com/mapbox-gl-js/v2.10.0/mapbox-gl.js"></script>

    <style>
        #map {
            position: relative;
            z-index: 1;
            margin-top: 20px;
            height: 400px;
            width: 100%;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }
    </style>
</head>

<body class="theme-red">
    <!-- Page Loader -->
    <div class="page-loader-wrapper">
        <div class="loader">
            <div class="preloader">
                <div class="spinner-layer pl-red">
                    <div class="circle-clipper left">
                        <div class="circle"></div>
                    </div>
                    <div class="circle-clipper right">
                        <div class="circle"></div>
                    </div>
                </div>
            </div>
            <p>Please wait...</p>
        </div>
    </div>
    <!-- #END# Page Loader -->
    <!-- Overlay For Sidebars -->
    <div class="overlay"></div>
    <!-- #END# Overlay For Sidebars -->
    <!-- Top Bar -->
    <nav class="navbar">
        <div class="container-fluid">
            <div class="navbar-header">
                <a href="javascript:void(0);" class="navbar-toggle collapsed" data-toggle="collapse" data-target="#navbar-collapse" aria-expanded="false"></a>
                <a href="javascript:void(0);" class="bars"></a>
                <a id="app-title" style="display:flex;align-items:center" class="navbar-brand" href="dashboard.php">
                    <img id="bcas-logo" style="width:45px;display:inline;margin-right:10px; border-radius: 50px;" src="images/admin/crisis.jpg" />
                    <span>CRISIS MANAGEMENT SYSTEM</span>
                </a>
            </div>
            <div class="collapse navbar-collapse" id="navbar-collapse">
                <ul class="nav navbar-nav navbar-right">
                    <!-- Notifications -->
                    <li class="dropdown">
                        <a href="javascript:void(0);" class="dropdown-toggle" data-toggle="dropdown" role="button">
                            <i class="material-icons">notifications</i>
                            <span class="label-count">1</span>
                        </a>
                        <ul class="dropdown-menu">
                            <li class="header">NOTIFICATIONS</li>
                            <li class="body">
                                <ul class="menu">
                                    <li>
                                        <a href="javascript:void(0);">
                                            <div class="icon-circle bg-light-green">
                                                <i class="material-icons">pending</i>
                                            </div>
                                            <div class="menu-info">
                                                <h4>12 new members joined</h4>
                                                <p>
                                                    <i class="material-icons">access_time</i> 14 mins ago
                                                </p>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                            <li class="footer">
                                <a href="javascript:void(0);">View All Notifications</a>
                            </li>
                        </ul>
                    </li>
                    <!-- #END# Notifications -->
                    <li class="pull-right"><a href="javascript:void(0);" class="js-right-sidebar" data-close="true"><i class="material-icons">account_circle</i></a></li>
                </ul>
            </div>
        </div>
    </nav>
    <!-- #Top Bar -->
    <section>
        <!-- Left Sidebar -->
        <aside id="leftsidebar" class="sidebar">
            <div class="menu">
                <ul class="list">
                    <li>
                        <a href="dashboard.php">
                            <i class="material-icons">home</i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="users.php">
                            <i class="material-icons">groups</i>
                            <span>Users</span>
                        </a>
                    </li>

                    <li>
                        <a href="feedback.php">
                            <i class="material-icons">feedback</i>
                            <span>Feedback</span>
                        </a>
                    </li>

                    <li class="active">
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">crisis_alert</i>
                            <span>Posts Incedents</span>
                        </a>
                        <ul class="ml-menu">
                            <li class="active">
                                <a href="pending_complain.php">
                                    <span>Pending</span>
                                </a>
                            </li>
                            <li>
                                <a href="approve_complain.php">
                                    <span>Approve</span>
                                </a>
                            </li>
                        </ul>
                    </li>

                    <li>
                        <a href="reports.php">
                            <i class="material-icons">report</i>
                            <span>Reports</span>
                        </a>
                    </li>

                </ul>
            </div>
            <!-- #Menu -->
            <!-- Footer -->
            <div class="legal">
                <!-- <div class="copyright">
                    &copy; <a href="javascript:void(0);"></a>.
                </div>
                <div class="version">
                    <b>Version: </b> 1.0.5
                </div> -->
            </div>
            <!-- #Footer -->
        </aside>
        <!-- #END# Left Sidebar -->
        <!-- Right Sidebar -->
        <aside id="rightsidebar" class="right-sidebar">
            <ul class="nav nav-tabs tab-nav-right" role="tablist">
                <li role="presentation" class="active"><a href="#skins" data-toggle="tab">ACCOUNT</a></li>
            </ul>
            <div class="tab-content">
                <div role="tabpanel" id="skins">
                    <ul style="list-style-type: none;">
                        <li>
                            <a href="" data-toggle="modal" data-target="#changePasswordModal" style="margin-top: 15px; margin-left: -30px; display: inline-block; font-weight: 900; font-size: 15px; text-decoration: none; cursor: pointer; color: black"><i class="material-icons mr-2" style="font-size: 18px; vertical-align: middle;">lock</i> Change password</a>
                        </li>
                    </ul>
                    <ul style="list-style-type: none;">
                        <li>
                            <a href="admin_logout.php" style="margin-top: 15px; margin-left: -30px; font-weight: 900; font-size: 15px; text-decoration: none; cursor: pointer; color: black"><i class=" material-icons mr-2" style="font-size: 18px; vertical-align: middle;">exit_to_app</i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>


        <!-- CHANGE PASSWORD MODAL -->
        <div class="modal fade" id="changePasswordModal" tabindex="-1" role="dialog">
            <div class="modal-dialog modal-md" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h4 class="modal-title" id="largeModalLabel">Change password</h4>
                        <hr style="background-color: #bc1823; height: 2px; border: none;">
                    </div>
                    <div class="modal-body">
                        <div id="errorMessages" class="alert alert-danger" style="display: none;"></div>
                        <form id="form_advanced_validation" class="changePasswordForm" method="POST" action="">
                            <div class="form-group form-float">
                                <label style="color: #212529; font-weight: 600;" class="form-label">Old Password</label>
                                <div class="form-line">
                                    <input type="password" class="form-control" name="old_password" required>
                                </div>
                                <div id="error-old_password" class="error-message" style="font-size:12px; margin-top:5px; font-weight:900; color: red;"></div>
                            </div>

                            <div class="form-group form-float">
                                <label style="color: #212529; font-weight: 600;" class="form-label">New Password</label>
                                <div class="form-line">
                                    <input type="password" class="form-control" name="password" maxlength="12" minlength="6" required>
                                </div>
                                <div id="error-password" class="error-message" style="font-size:12px; margin-top:5px; font-weight:900; color: red;"></div>
                            </div>

                            <div class="form-group form-float">
                                <label style="color: #212529; font-weight: 600;" class="form-label">Confirm Password</label>
                                <div class="form-line">
                                    <input type="password" class="form-control" name="password_confirmation" maxlength="12" minlength="6" required>
                                </div>
                                <div id="error-password_confirmation" class="error-message" style="font-size:12px; margin-top:5px; font-weight:900; color: red;"></div>
                            </div>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn bg-red waves-effect">SAVE CHANGES</button>
                        <button type="button" class="btn btn-link waves-effect" data-dismiss="modal">CLOSE</button>
                    </div>
                    </form>
                </div>
            </div>
        </div>
        </div>
        <!-- #END# Right Sidebar -->
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <ol style="font-size: 15px;" class="breadcrumb breadcrumb-col-red">
                    <li><a href="dashboard.php"><i style="font-size: 20px;" class="material-icons">home</i> Dashboard</a></li>
                    <li><a href="pending_complain.php"><i style="font-size: 20px;" class="material-icons">crisis_alert</i> Pending Complaints</a></li>
                    <li class="active"><i style="font-size: 20px;" class="material-icons">visibility</i> View Incident</li>
                </ol>
            </div>

            <!-- Incident Details -->
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2 class="m-0" style="font-size: 25px; font-weight: 900; color: #bc1823;">
                                INCIDENT DETAILS
                            </h2>
                        </div>
                        <div class="body">
                            <div id="map"></div>

                            <div class="table-responsive" style="margin-top: 10px; margin-bottom: 10px;">
                                <table class="table table-bordered">
                                    <tr>
                                        <th>Incident Picture</th>
                                        <td>
                                            <div id="aniimated-thumbnials" class="list-unstyled row clearfix">
                                                <?php
                                                if ($incident_proof && is_array($incident_proof)) {
                                                    foreach ($incident_proof as $image) {
                                                        $full_image_path = "../assets/images/proofs/" . htmlspecialchars($image);
                                                        $thumb_image_path = "../assets/images/proofs/" . htmlspecialchars($image);
                                                ?>
                                                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12" style="margin-right: -20px;">
                                                            <a href="<?php echo $full_image_path; ?>" data-sub-html="Incident Proof Image">
                                                                <img style="height: 100px;" class="img-responsive thumbnail" src="<?php echo $thumb_image_path; ?>" alt="Incident Picture">
                                                            </a>
                                                        </div>
                                                <?php
                                                    }
                                                } else {
                                                    echo "No images available";
                                                }
                                                ?>
                                            </div>

                                        </td>
                                    </tr>
                                    <tr>
                                        <th>Complainant</th>
                                        <td><?php echo htmlspecialchars($incident['fullname']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Incident Type</th>
                                        <td><?php echo htmlspecialchars($incident['incident_type']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Incident Description</th>
                                        <td><?php echo nl2br(htmlspecialchars($incident['incident_description'])); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Location</th>
                                        <td><?php echo htmlspecialchars($incident['incident_location']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Landmark</th>
                                        <td><?php echo htmlspecialchars($incident['incident_landmark']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date/Time</th>
                                        <td><?php echo htmlspecialchars($incident['incident_datetime']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td style="color: orange;"><?php echo htmlspecialchars($incident['status']); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div style="display: flex !important; justify-content: end; gap: 10px;">
                                <button type="submit" class="btn btn-success">Approved</button>
                                <button type="submit" class="btn btn-danger">Delete</button>
                                <a href="pending_complain.php" class="btn btn-primary">Go back</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>

    <!-- Jquery Core Js -->
    <script src="plugins/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core Js -->
    <script src="plugins/bootstrap/js/bootstrap.js"></script>

    <!-- Slimscroll Plugin Js -->
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>

    <!-- Jquery Validation Plugin Css -->
    <script src="plugins/jquery-validation/jquery.validate.js"></script>
    <script src="js/pages/forms/form-validation.js"></script>

    <!-- Waves Effect Plugin Js -->
    <script src="plugins/node-waves/waves.js"></script>

    <!-- Light Gallery Plugin Js -->
    <script src="plugins/light-gallery/js/lightgallery-all.js"></script>
    <script src="js/pages/medias/image-gallery.js"></script>

    <!-- Jquery CountTo Plugin Js -->
    <script src="plugins/jquery-countto/jquery.countTo.js"></script>

    <!-- Morris Plugin Js -->
    <script src="plugins/raphael/raphael.min.js"></script>
    <script src="plugins/morrisjs/morris.js"></script>

    <!-- ChartJs -->
    <script src="plugins/chartjs/Chart.bundle.js"></script>

    <!-- Flot Charts Plugin Js -->
    <script src="plugins/flot-charts/jquery.flot.js"></script>
    <script src="plugins/flot-charts/jquery.flot.resize.js"></script>
    <script src="plugins/flot-charts/jquery.flot.pie.js"></script>
    <script src="plugins/flot-charts/jquery.flot.categories.js"></script>
    <script src="plugins/flot-charts/jquery.flot.time.js"></script>

    <!-- Sparkline Chart Plugin Js -->
    <script src="plugins/jquery-sparkline/jquery.sparkline.js"></script>

    <!-- Datatable -->
    <script src="plugins/jquery-datatable/jquery.dataTables.js"></script>
    <script src="plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.js"></script>
    <script src="plugins/jquery-datatable/extensions/export/dataTables.buttons.min.js"></script>
    <script src="plugins/jquery-datatable/extensions/export/buttons.flash.min.js"></script>
    <script src="plugins/jquery-datatable/extensions/export/jszip.min.js"></script>
    <script src="plugins/jquery-datatable/extensions/export/pdfmake.min.js"></script>
    <script src="plugins/jquery-datatable/extensions/export/vfs_fonts.js"></script>
    <script src="plugins/jquery-datatable/extensions/export/buttons.html5.min.js"></script>
    <script src="plugins/jquery-datatable/extensions/export/buttons.print.min.js"></script>
    <script src="js/pages/tables/jquery-datatable.js"></script>
    <!-- Custom Js -->
    <script src="js/admin.js"></script>
    <script src="js/pages/index.js"></script>

    <!-- Demo Js -->
    <script src="js/demo.js"></script>
    <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

    <!-- MAPPING -->
    <script>
        mapboxgl.accessToken = 'pk.eyJ1IjoiaHR0cHJ1c3MiLCJhIjoiY200NGtidzU0MGw1MTJscXhoazc0dDFyMiJ9.fmNrzF3Oa_TcSlcfF8nfCw';
        var map = L.map('map').setView([<?php echo $incident['latitude']; ?>, <?php echo $incident['longitude']; ?>], 13);

        // Use OpenStreetMap tiles as the base layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Max bounds for the map
        map.setMaxBounds([
            [4.6, 116.9],
            [21.4, 126.6]
        ]);

        var latitude = <?php echo json_encode($incident['latitude']); ?>;
        var longitude = <?php echo json_encode($incident['longitude']); ?>;
        var userLatitude, userLongitude;
        var userMarker, incidentMarker;

        // Create a marker for the incident
        incidentMarker = L.marker([latitude, longitude]).addTo(map)
            .bindPopup('<b><?php echo $incident['incident_type']; ?></b><br>' +
                'Location: <?php echo $incident['incident_location_map']; ?><br>');

        // Geolocation handling
        if (navigator.geolocation) {
            navigator.geolocation.getCurrentPosition(function(position) {
                userLatitude = position.coords.latitude;
                userLongitude = position.coords.longitude;

                // Add the user's location marker to the map
                if (!userMarker) {
                    userMarker = L.marker([userLatitude, userLongitude]).addTo(map)
                        .bindPopup('Your Location')
                        .openPopup();
                } else {
                    userMarker.setLatLng([userLatitude, userLongitude]);
                }

                // Zoom to user's location and fetch directions
                map.setView([userLatitude, userLongitude], 14);
                getDirections(userLongitude, userLatitude, longitude, latitude);
            }, function() {
                // If user denies location access, zoom to incident location
                console.log("User denied geolocation access. Showing incident location only.");
                map.setView([latitude, longitude], 18);
            }, {
                enableHighAccuracy: true,
                maximumAge: 0,
                timeout: 5000
            });
        } else {
            // If geolocation is not supported, show incident location
            console.log("Geolocation is not supported by this browser. Showing incident location only.");
            map.setView([latitude, longitude], 18);
        }

        // Function to get directions from the user's location to the incident
        function getDirections(userLongitude, userLatitude, incidentLongitude, incidentLatitude) {
            var directionsUrl = `https://api.mapbox.com/directions/v5/mapbox/driving/${userLongitude},${userLatitude};${incidentLongitude},${incidentLatitude}?geometries=geojson&access_token=${mapboxgl.accessToken}`;

            fetch(directionsUrl)
                .then(response => response.json())
                .then(data => {
                    var duration = data.routes[0].duration;
                    var distance = data.routes[0].distance;
                    var travelTime = (duration / 60).toFixed(2);
                    var travelDistance = (distance / 1000).toFixed(2);

                    var travelInfo = `
                Estimated Travel Time: ${travelTime} minutes<br>
                Estimated Distance: ${travelDistance} km
            `;

                    // Bind the route information to the incident marker
                    incidentMarker.bindPopup(`<b><?php echo $incident['incident_type']; ?></b><br>` +
                        `Location: <?php echo $incident['incident_location_map']; ?><br>` +
                        `${travelInfo}`).openPopup();

                    // Add the route to the map
                    var routeGeoJSON = data.routes[0].geometry;
                    L.geoJSON(routeGeoJSON, {
                        style: {
                            color: "#FF5733",
                            weight: 5,
                            opacity: 0.7
                        }
                    }).addTo(map);
                })
                .catch(error => console.log("Error fetching route data:", error));
        }
    </script>
</body>

</html>