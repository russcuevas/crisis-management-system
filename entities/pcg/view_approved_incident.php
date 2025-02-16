<?php
include '../../database/connection.php';

session_start();
$responder_id = $_SESSION['responder_id'];
if (!isset($responder_id)) {
    header('location:../../login.php');
}




//view incidents
if (isset($_GET['incident_id'])) {
    $incident_id = $_GET['incident_id'];

    // Fetch the incident details
    $sql = "SELECT tbl_incidents.*, tbl_users.fullname 
        FROM tbl_incidents 
        LEFT JOIN tbl_users ON tbl_incidents.user_id = tbl_users.id 
        WHERE tbl_incidents.incident_id = :incident_id";

    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':incident_id', $incident_id, PDO::PARAM_INT);
    $stmt->execute();
    $incident = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$incident) {
        $_SESSION['approved_errors'] = "Incident Not Found";
        header('location:pcg_approved.php');
        exit();
    }

    if ($incident['status'] === 'Pending') {
        $_SESSION['approved_errors'] = "This incident has already been approved";
        header('location:pcg_approved.php');
        exit();
    }

    // Decode respondents_id (assuming it's stored as JSON)
    $respondent_ids = json_decode($incident['respondents_id'], true);

    if (!empty($respondent_ids)) {
        // Convert array to comma-separated values for SQL query
        $placeholders = implode(',', array_fill(0, count($respondent_ids), '?'));

        // Fetch corresponding responder types
        $sql = "SELECT type FROM tbl_responders WHERE id IN ($placeholders)";
        $stmt = $conn->prepare($sql);
        $stmt->execute($respondent_ids);
        $respondent_types = $stmt->fetchAll(PDO::FETCH_COLUMN);
    } else {
        $respondent_types = [];
    }
}

//decode the image from json format
$incident_proof = json_decode($incident['incident_proof'], true);


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Delete the incidents
    if (isset($_POST['delete'])) {
        $incident_proof = json_decode($incident['incident_proof'], true);

        $sql = "DELETE FROM tbl_incidents WHERE incident_id = :incident_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':incident_id', $incident_id, PDO::PARAM_INT);
        $stmt->execute();

        if ($incident_proof && is_array($incident_proof)) {
            foreach ($incident_proof as $image) {
                $image_path = "../../assets/images/proofs/" . htmlspecialchars($image);
                if (file_exists($image_path)) {
                    unlink($image_path);
                }
            }
        }

        $_SESSION['approved_success'] = 'Incident deleted successfully.';
        header("Location: pcg_approved.php");
        exit();
    }
}

// applicable to all page
// fetching notifs
$sql_notifications = "
SELECT
tbl_notifications.id AS notification_id,
tbl_notifications.incident_id,
tbl_notifications.user_id AS notification_user_id,
tbl_notifications.is_view,
tbl_notifications.created_at AS notification_created_at,
tbl_notifications.notification_description, -- Added notification_description
tbl_incidents.incident_type,
tbl_incidents.incident_description AS incident_description,
tbl_incidents.status AS incident_status,
tbl_users.id AS user_id,
tbl_users.fullname AS user_fullname,
tbl_users.email AS user_email,
tbl_users.profile_picture AS user_profile_picture
FROM tbl_notifications
LEFT JOIN tbl_incidents ON tbl_notifications.incident_id = tbl_incidents.incident_id
LEFT JOIN tbl_users ON tbl_notifications.user_id = tbl_users.id
WHERE tbl_notifications.is_view = 0 -- Get only unread notifications
ORDER BY tbl_notifications.created_at DESC
";

$notifications_bells = $conn->query($sql_notifications)->fetchAll(PDO::FETCH_ASSOC);


// function for time notifs
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

// query to notifications that is unread
$sql_count_notifications = "SELECT COUNT(*) AS unread_count FROM tbl_notifications WHERE is_view = 0";
$stmt_count_notifications = $conn->prepare($sql_count_notifications);
$stmt_count_notifications->execute();
$result_count_notifications = $stmt_count_notifications->fetch(PDO::FETCH_ASSOC);
$unread_count = $result_count_notifications['unread_count'];
//end applicable to all page
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
    <link href="../plugins/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Waves Effect Css -->
    <link href="../plugins/node-waves/waves.css" rel="stylesheet" />

    <!-- Animation Css -->
    <link href="../plugins/animate-css/animate.css" rel="stylesheet" />

    <!-- Morris Chart Css-->
    <link href="../plugins/morrisjs/morris.css" rel="stylesheet" />
    <!-- JQuery DataTable Css -->
    <link href="../plugins/jquery-datatable/skin/bootstrap/css/dataTables.bootstrap.css" rel="stylesheet">

    <!-- Custom Css -->
    <link href="../css/style.css" rel="stylesheet">
    <link href="../css/themes/all-themes.css" rel="stylesheet" />
    <!-- Include Leaflet CSS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

    <!-- Light Gallery Plugin Css -->
    <link href="../plugins/light-gallery/css/lightgallery.css" rel="stylesheet">

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
    <!-- TOP BAR -->
    <?php include('top_bar.php')  ?>
    <!-- END TOP BAR -->
    <section>
        <!-- Left Sidebar -->
        <aside id="leftsidebar" class="sidebar">
            <div class="menu">
                <ul class="list">
                    <li class="">
                        <a href="pcg_dashboard.php">
                            <i class="material-icons">home</i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="active">
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">crisis_alert</i>
                            <span>Posts Incedents</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="pcg_pending.php">
                                    <span>Pending</span>
                                </a>
                            </li>
                            <li class="active">
                                <a href="pcg_approved.php">
                                    <span>Approved</span>
                                </a>
                            </li>
                        </ul>
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
                            <a href="change_details.php" style="margin-top: 15px; margin-left: -30px; display: inline-block; font-weight: 900; font-size: 15px; text-decoration: none; cursor: pointer; color: black"><i class="material-icons mr-2" style="font-size: 18px; vertical-align: middle;">lock</i> Change password</a>
                        </li>
                    </ul>
                    <ul style="list-style-type: none;">
                        <li>
                            <a href="../logout.php" style="margin-top: 15px; margin-left: -30px; font-weight: 900; font-size: 15px; text-decoration: none; cursor: pointer; color: black"><i class=" material-icons mr-2" style="font-size: 18px; vertical-align: middle;">exit_to_app</i> Logout</a>
                        </li>
                    </ul>
                </div>
            </div>
        </aside>



        <!-- #END# Right Sidebar -->
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <ol style="font-size: 15px;" class="breadcrumb breadcrumb-col-red">
                    <li><a href="pcg_dashboard.php"><i style="font-size: 20px;" class="material-icons">home</i> Dashboard</a></li>
                    <li><a href="pcg_approved.php"><i style="font-size: 20px;" class="material-icons">check</i> Approved Complaints</a></li>
                    <li class="active"><i style="font-size: 20px;" class="material-icons">visibility</i> View Incident</li>
                </ol>
            </div>

            <div class="modal fade" id="confirmDeleteModal" tabindex="-1" role="dialog" aria-labelledby="confirmDeleteModalLabel" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="confirmDeleteModalLabel">Confirm Deletion</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            Are you sure you want to delete this incident?
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                            <form method="POST" id="deleteForm" style="display:inline;">
                                <button type="submit" name="delete" class="btn btn-danger">Delete</button>
                            </form>
                        </div>
                    </div>
                </div>
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
                                                        $full_image_path = "../../assets/images/proofs/" . htmlspecialchars($image);
                                                        $thumb_image_path = "../../assets/images/proofs/" . htmlspecialchars($image);
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
                                        <th>Respondents</th>
                                        <td><?php echo !empty($respondent_types) ? htmlspecialchars(implode(', ', $respondent_types)) : 'No Respondents'; ?></td>
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
                                        <th>Landmark</th>
                                        <td><?php echo htmlspecialchars($incident['incident_landmark']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Date/Time</th>
                                        <td><?php echo htmlspecialchars($incident['incident_datetime']); ?></td>
                                    </tr>
                                    <tr>
                                        <th>Status</th>
                                        <td style="color: green; font-weight: 900;"><?php echo htmlspecialchars($incident['status']); ?></td>
                                    </tr>
                                </table>
                            </div>
                            <div style="display: flex !important; justify-content: end; gap: 10px;">
                                <form method="POST">
                                    <button type="button" class="btn btn-danger" data-toggle="modal" data-target="#confirmDeleteModal">Delete</button>
                                    <a href="pcg_approved.php" class="btn btn-primary">Go back</a>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </section>


    <!-- Jquery Core Js -->
    <script src="../plugins/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core Js -->
    <script src="../plugins/bootstrap/js/bootstrap.js"></script>

    <!-- Slimscroll Plugin Js -->
    <script src="../plugins/jquery-slimscroll/jquery.slimscroll.js"></script>

    <!-- Jquery Validation Plugin Css -->
    <script src="../plugins/jquery-validation/jquery.validate.js"></script>
    <script src="../js/pages/forms/form-validation.js"></script>

    <!-- Waves Effect Plugin Js -->
    <script src="../plugins/node-waves/waves.js"></script>

    <!-- Light Gallery Plugin Js -->
    <script src="../plugins/light-gallery/js/lightgallery-all.js"></script>
    <script src="../js/pages/medias/image-gallery.js"></script>

    <!-- Jquery CountTo Plugin Js -->
    <script src="../plugins/jquery-countto/jquery.countTo.js"></script>

    <!-- Morris Plugin Js -->
    <script src="../plugins/raphael/raphael.min.js"></script>
    <script src="../plugins/morrisjs/morris.js"></script>

    <!-- ChartJs -->
    <script src="../plugins/chartjs/Chart.bundle.js"></script>

    <!-- Flot Charts Plugin Js -->
    <script src="../plugins/flot-charts/jquery.flot.js"></script>
    <script src="../plugins/flot-charts/jquery.flot.resize.js"></script>
    <script src="../plugins/flot-charts/jquery.flot.pie.js"></script>
    <script src="../plugins/flot-charts/jquery.flot.categories.js"></script>
    <script src="../plugins/flot-charts/jquery.flot.time.js"></script>

    <!-- Sparkline Chart Plugin Js -->
    <script src="../plugins/jquery-sparkline/jquery.sparkline.js"></script>

    <!-- Datatable -->
    <script src="../plugins/jquery-datatable/jquery.dataTables.js"></script>
    <script src="../plugins/jquery-datatable/skin/bootstrap/js/dataTables.bootstrap.js"></script>
    <script src="../plugins/jquery-datatable/extensions/export/dataTables.buttons.min.js"></script>
    <script src="../plugins/jquery-datatable/extensions/export/buttons.flash.min.js"></script>
    <script src="../plugins/jquery-datatable/extensions/export/jszip.min.js"></script>
    <script src="../plugins/jquery-datatable/extensions/export/pdfmake.min.js"></script>
    <script src="../plugins/jquery-datatable/extensions/export/vfs_fonts.js"></script>
    <script src="../plugins/jquery-datatable/extensions/export/buttons.html5.min.js"></script>
    <script src="../plugins/jquery-datatable/extensions/export/buttons.print.min.js"></script>
    <script src="../js/pages/tables/jquery-datatable.js"></script>
    <!-- Custom Js -->
    <script src="../js/admin.js"></script>
    <script src="../js/pages/index.js"></script>

    <!-- Demo Js -->
    <script src="../js/demo.js"></script>
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