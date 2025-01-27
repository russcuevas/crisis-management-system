<?php
include '../database/connection.php';

session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}
// GET THE ADMIN
$get_total_admin = "SELECT COUNT(*) AS total_admin FROM `tbl_admin`";
$stmt_total_admin = $conn->prepare($get_total_admin);
$stmt_total_admin->execute();
$result_total_admin = $stmt_total_admin->fetch(PDO::FETCH_ASSOC);
$total_admin = $result_total_admin['total_admin'];
// END GET TOTAL ADMIN

// GET THE USERS
$get_total_users = "SELECT COUNT(*) AS total_users FROM `tbl_users`";
$stmt_total_users = $conn->prepare($get_total_users);
$stmt_total_users->execute();
$result_total_users = $stmt_total_users->fetch(PDO::FETCH_ASSOC);
$total_users = $result_total_users['total_users'];
// END GET TOTAL USERS

// GET THE TOTAL INCIDENTS PENDING
$get_total_incidents_pending = "SELECT COUNT(*) AS total_incidents_pending FROM `tbl_incidents` WHERE status = 'Pending'";
$stmt_total_incidents_pending = $conn->prepare($get_total_incidents_pending);
$stmt_total_incidents_pending->execute();
$restult_total_incidents_pending = $stmt_total_incidents_pending->fetch(PDO::FETCH_ASSOC);
$total_incidents_pending = $restult_total_incidents_pending['total_incidents_pending'];
// END GET TOTAL INCIDENTS PENDING

// GET PENDING COMPLAINTS
$sql = "SELECT tbl_incidents.*, tbl_users.fullname 
        FROM tbl_incidents 
        LEFT JOIN tbl_users ON tbl_incidents.user_id = tbl_users.id 
        WHERE tbl_incidents.status = 'pending'";
$complaints = $conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
// END GET PENDING COMPLAINTS


// Fetch fire incidents grouped by month
$query = "SELECT MONTH(created_at) AS month, COUNT(*) AS fire_incidents 
          FROM tbl_reports 
          WHERE incident_type = 'Fire'
          GROUP BY MONTH(created_at)
          ORDER BY MONTH(created_at)";

// Prepare and execute the query
$stmt = $conn->prepare($query);
$stmt->execute();


// applicable to all page
// fetching notifs
$sql_notifications = "
    SELECT 
        tbl_notifications.id AS notification_id,
        tbl_notifications.incident_id,
        tbl_notifications.user_id AS notification_user_id,
        tbl_notifications.is_view,
        tbl_notifications.created_at AS notification_created_at,
        tbl_notifications.notification_description,  -- Added notification_description
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
    WHERE tbl_notifications.is_view = 0  -- Get only unread notifications
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
    <style>

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

    <!-- TOP BAR -->
    <?php include('top_bar.php')  ?>
    <!-- END TOP BAR -->
    <section>
        <!-- Left Sidebar -->
        <aside id="leftsidebar" class="sidebar">
            <div class="menu">
                <ul class="list">
                    <li class="active">
                        <a href="dashboard.php">
                            <i class="material-icons">home</i>
                            <span>Dashboard</span>
                        </a>
                    </li>
                    <li>
                        <a href="manage_admin.php">
                            <i class="material-icons">admin_panel_settings</i>
                            <span>Admin</span>
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

                    <li>
                        <a href="javascript:void(0);" class="menu-toggle">
                            <i class="material-icons">crisis_alert</i>
                            <span>Posts Incedents</span>
                        </a>
                        <ul class="ml-menu">
                            <li>
                                <a href="pending_complain.php">
                                    <span>Pending</span>
                                </a>
                            </li>
                            <li>
                                <a href="approve_complain.php">
                                    <span>Approved</span>
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
                            <a href="change_details.php" style="margin-top: 15px; margin-left: -30px; display: inline-block; font-weight: 900; font-size: 15px; text-decoration: none; cursor: pointer; color: black"><i class="material-icons mr-2" style="font-size: 18px; vertical-align: middle;">lock</i> Change password</a>
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
        <!-- #END# Right Sidebar -->
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <h2 style="font-size: 25px; font-weight: 900; color: #bc1823 !important;">DASHBOARD</h2>
            </div>
            <!-- Widgets -->
            <div class="row clearfix">
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12" onclick="window.location.href='manage_admin.php'">
                    <div style="cursor: pointer;" class="info-box bg-red hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">admin_panel_settings</i>
                        </div>
                        <div class="content">
                            <div class="text" style="color: white !important;">TOTAL ADMIN</div>
                            <div class="" style="font-size: 20px;"><?php echo $total_admin ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12" onclick="window.location.href='users.php'">
                    <div style="cursor: pointer;" class="info-box bg-red hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">groups</i>
                        </div>
                        <div class="content">
                            <div class="text" style="color: white !important;">TOTAL USERS</div>
                            <div class="" style="font-size: 20px;"><?php echo $total_users ?></div>
                        </div>
                    </div>
                </div>
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12" onclick="window.location.href='pending_complain.php'">
                    <div style="cursor: pointer;" class="info-box bg-red hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">pending</i>
                        </div>
                        <div class="content">
                            <div class="text" style="color: white !important;">PENDING COMPLAIN</div>
                            <div class="" style="font-size: 20px;"><?php echo $total_incidents_pending ?></div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- #END# Widgets -->
            <div class="row clearfix">
                <!-- Bar Chart -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="font-size: 17px; font-weight: 900; color: #bc1823;">
                                FIRE INCIDENT YEARLY
                            </h2>
                        </div>
                        <div class="body">
                            <form action="">
                                <div class="form-group" style="display: flex; align-items: center;">
                                    <label for="year-select-gender" style="font-weight: 600; margin-right: 10px;">Year:</label>
                                    <div class="form-line" style="width: 100px">
                                        <select class="form-control show-tick" id="year-select-gender" style="border: none; box-shadow: none;">
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2027">2027</option>
                                            <option value="2028">2028</option>
                                            <option value="2029">2029</option>
                                            <option value="2030">2030</option>

                                        </select>
                                    </div>
                                </div>
                                <canvas id="fireIncident" height="200"></canvas>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="font-size: 17px; font-weight: 900; color: #bc1823;">
                                FLOOD INCIDENT YEARLY
                            </h2>
                        </div>
                        <div class="body">
                            <form action="">
                                <div class="form-group" style="display: flex; align-items: center;">
                                    <label for="year-select-flood" style="font-weight: 600; margin-right: 10px;">Year:</label>
                                    <div class="form-line" style="width: 100px">
                                        <select class="form-control show-tick" id="year-select-flood" style="border: none; box-shadow: none;">
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2027">2027</option>
                                            <option value="2028">2028</option>
                                            <option value="2029">2029</option>
                                            <option value="2030">2030</option>

                                        </select>
                                    </div>
                                </div>
                                <canvas id="floodIncident" height="200"></canvas>
                            </form>
                        </div>
                    </div>
                </div>
                <!-- #END# Bar Chart -->
            </div>

            <div class="row clearfix">
                <!-- Bar Chart -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="font-size: 17px; font-weight: 900; color: #bc1823;">
                                EARTHQUAKE INCIDENT YEARLY
                            </h2>
                        </div>
                        <div class="body">
                            <form action="">
                                <div class="form-group" style="display: flex; align-items: center;">
                                    <label for="year-select-earthquake" style="font-weight: 600; margin-right: 10px;">Year:</label>
                                    <div class="form-line" style="width: 100px">
                                        <select class="form-control show-tick" id="year-select-earthquake" style="border: none; box-shadow: none;">
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2027">2027</option>
                                            <option value="2028">2028</option>
                                            <option value="2029">2029</option>
                                            <option value="2030">2030</option>

                                        </select>
                                    </div>
                                </div>
                                <canvas id="earthquakeIncident" height="200"></canvas>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="font-size: 17px; font-weight: 900; color: #bc1823;">
                                ACCIDENT INCIDENT YEARLY
                            </h2>
                        </div>
                        <div class="body">
                            <form action="">
                                <div class="form-group" style="display: flex; align-items: center;">
                                    <label for="year-select-accident" style="font-weight: 600; margin-right: 10px;">Year:</label>
                                    <div class="form-line" style="width: 100px">
                                        <select class="form-control show-tick" id="year-select-accident" style="border: none; box-shadow: none;">
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2027">2027</option>
                                            <option value="2028">2028</option>
                                            <option value="2029">2029</option>
                                            <option value="2030">2030</option>

                                        </select>
                                    </div>
                                </div>
                                <canvas id="accidentIncident" height="200"></canvas>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Bar Chart -->

            <div class="row clearfix">
                <!-- Bar Chart -->
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="font-size: 17px; font-weight: 900; color: #bc1823;">
                                THEFT INCIDENT YEARLY
                            </h2>
                        </div>
                        <div class="body">
                            <form action="">
                                <div class="form-group" style="display: flex; align-items: center;">
                                    <label for="year-select-theft" style="font-weight: 600; margin-right: 10px;">Year:</label>
                                    <div class="form-line" style="width: 100px">
                                        <select class="form-control show-tick" id="year-select-theft" style="border: none; box-shadow: none;">
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2027">2027</option>
                                            <option value="2028">2028</option>
                                            <option value="2029">2029</option>
                                            <option value="2030">2030</option>

                                        </select>
                                    </div>
                                </div>
                                <canvas id="theftIncident" height="200"></canvas>
                            </form>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2 style="font-size: 17px; font-weight: 900; color: #bc1823;">
                                OTHER CASE INCIDENT YEARLY
                            </h2>
                        </div>
                        <div class="body">
                            <form action="">
                                <div class="form-group" style="display: flex; align-items: center;">
                                    <label for="year-select-other" style="font-weight: 600; margin-right: 10px;">Year:</label>
                                    <div class="form-line" style="width: 100px">
                                        <select class="form-control show-tick" id="year-select-other" style="border: none; box-shadow: none;">
                                            <option value="2024">2024</option>
                                            <option value="2025">2025</option>
                                            <option value="2026">2026</option>
                                            <option value="2027">2027</option>
                                            <option value="2028">2028</option>
                                            <option value="2029">2029</option>
                                            <option value="2030">2030</option>

                                        </select>
                                    </div>
                                </div>
                                <canvas id="otherIncidents" height="200"></canvas>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Bar Chart -->
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
    <script>
        function createChartAndFetchData(canvasId, selectId, incidentType) {
            var incidentChart;
            var currentYear = new Date().getFullYear();
            var chartOptions = {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5
                        }
                    }
                }
            };

            function createChart(data) {
                var ctx = document.getElementById(canvasId).getContext('2d');
                incidentChart = new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: chartOptions
                });
            }

            function fetchDataForYear(year) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', `fetch_incidents.php?year=${year}&incidentType=${incidentType}`, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var responseData = JSON.parse(xhr.responseText);
                            if (responseData.data && responseData.labels) {
                                incidentChart.data.labels = responseData.labels;
                                incidentChart.data.datasets[0].data = responseData.data;
                                incidentChart.data.datasets[0].label = `${incidentType} Incidents in ${year}`;
                                incidentChart.update();
                            } else {
                                console.error('Invalid data received:', responseData);
                            }
                        } catch (error) {
                            console.error('Error parsing response:', error);
                        }
                    } else {
                        console.error('Failed to fetch data:', xhr.status);
                    }
                };
                xhr.send();
            }

            var defaultData = {
                labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                datasets: [{
                    label: `${incidentType} Incidents in ${currentYear}`,
                    data: Array(12).fill(0),
                    backgroundColor: '#bc1823',
                    borderColor: '#bc1823',
                    borderWidth: 1
                }]
            };

            createChart(defaultData);
            fetchDataForYear(currentYear);
            document.getElementById(selectId).addEventListener('change', function() {
                var selectedYear = this.value;
                fetchDataForYear(selectedYear);
            });
        }

        createChartAndFetchData('fireIncident', 'year-select-gender', 'Fire');
        createChartAndFetchData('floodIncident', 'year-select-flood', 'Flood');
        createChartAndFetchData('earthquakeIncident', 'year-select-earthquake', 'Earthquake');
        createChartAndFetchData('accidentIncident', 'year-select-accident', 'Accident');
        createChartAndFetchData('theftIncident', 'year-select-theft', 'Theft');
    </script>

    <script>
        function createOtherIncidentsChart(canvasId, selectId) {
            var incidentChart;
            var currentYear = new Date().getFullYear();
            var chartOptions = {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 5
                        }
                    }
                }
            };

            function createChart(data) {
                var ctx = document.getElementById(canvasId).getContext('2d');
                incidentChart = new Chart(ctx, {
                    type: 'bar',
                    data: data,
                    options: chartOptions
                });
            }

            function fetchDataForYear(year) {
                var xhr = new XMLHttpRequest();
                xhr.open('GET', `fetch_other_incidents.php?year=${year}`, true);
                xhr.onload = function() {
                    if (xhr.status === 200) {
                        try {
                            var responseData = JSON.parse(xhr.responseText);
                            if (responseData.data && responseData.labels) {
                                incidentChart.data.labels = responseData.labels;
                                incidentChart.data.datasets[0].data = responseData.data;
                                incidentChart.data.datasets[0].label = `Other Incidents in ${year}`;
                                incidentChart.update();
                            } else {
                                console.error('Invalid data received:', responseData);
                            }
                        } catch (error) {
                            console.error('Error parsing response:', error);
                        }
                    } else {
                        console.error('Failed to fetch data:', xhr.status);
                    }
                };
                xhr.send();
            }

            var defaultData = {
                labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                datasets: [{
                    label: `Other Incidents in ${currentYear}`,
                    data: Array(12).fill(0),
                    backgroundColor: '#bc1823',
                    borderColor: '#bc1823',
                    borderWidth: 1
                }]
            };
            createChart(defaultData);
            fetchDataForYear(currentYear);
            document.getElementById(selectId).addEventListener('change', function() {
                var selectedYear = this.value;
                fetchDataForYear(selectedYear);
            });
        }
        createOtherIncidentsChart('otherIncidents', 'year-select-other');
    </script>
</body>

</html>