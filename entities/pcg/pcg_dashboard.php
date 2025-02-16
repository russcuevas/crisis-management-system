<?php
include '../../database/connection.php';

session_start();
$responder_id = $_SESSION['responder_id'] ?? null;
if (!$responder_id) {
    header('location:../../login.php');
    exit();
}

// Fetch responder type
$sql = "SELECT type FROM tbl_responders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$responder_id]);
$responder = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$responder) {
    die("Responder not found.");
}

$responder_type = $responder['type'];

// Fetch all responders with the same type
$sql = "SELECT id FROM tbl_responders WHERE type = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$responder_type]);
$similar_responders = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($similar_responders)) {
    die("No responders found for the given type.");
}

// Construct JSON_CONTAINS conditions for filtering incidents based on responder ID
$pnp_conditions = implode(' OR ', array_map(fn($id) => "JSON_CONTAINS(tbl_incidents.respondents_id, '\"$id\"')", $similar_responders));

// Get the total pending incidents assigned to responders of the same type
$sql_pending = "SELECT COUNT(*) AS total_incidents_pending 
                FROM `tbl_incidents` 
                WHERE status = 'Pending' 
                AND ($pnp_conditions)";

$stmt_pending = $conn->prepare($sql_pending);
$stmt_pending->execute();
$result_pending = $stmt_pending->fetch(PDO::FETCH_ASSOC);
$total_incidents_pending = $result_pending['total_incidents_pending'];

// Get the total approved incidents assigned to responders of the same type
$sql_approved = "SELECT COUNT(*) AS total_incidents_approved 
                 FROM `tbl_incidents` 
                 WHERE status = 'Approved' 
                 AND ($pnp_conditions)";

$stmt_approved = $conn->prepare($sql_approved);
$stmt_approved->execute();
$result_approved = $stmt_approved->fetch(PDO::FETCH_ASSOC);
$total_incidents_approved = $result_approved['total_incidents_approved'];

// Fetch pending complaints assigned to the responder type
$sql_complaints = "SELECT tbl_incidents.*, tbl_users.fullname 
                   FROM tbl_incidents 
                   LEFT JOIN tbl_users ON tbl_incidents.user_id = tbl_users.id 
                   WHERE tbl_incidents.status = 'Pending' 
                   AND ($pnp_conditions)";

$stmt_complaints = $conn->prepare($sql_complaints);
$stmt_complaints->execute();
$complaints = $stmt_complaints->fetchAll(PDO::FETCH_ASSOC);

// Fetch notifications only for incidents assigned to the logged-in responder type
$sql_notifications = "
    SELECT 
        tbl_notifications.id AS notification_id,
        tbl_notifications.incident_id,
        tbl_notifications.user_id AS notification_user_id,
        tbl_notifications.is_view,
        tbl_notifications.created_at AS notification_created_at,
        tbl_notifications.notification_description,
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
    WHERE tbl_notifications.is_view = 0  -- Only unread notifications
    AND EXISTS (
        SELECT 1 
        FROM tbl_incidents 
        WHERE tbl_incidents.incident_id = tbl_notifications.incident_id
        AND ($pnp_conditions)  -- Ensuring it is assigned to the responder's type
    )
    ORDER BY tbl_notifications.created_at DESC
";

$stmt_notifications = $conn->prepare($sql_notifications);
$stmt_notifications->execute();
$notifications_bells = $stmt_notifications->fetchAll(PDO::FETCH_ASSOC);

// Function to format time ago
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

// Count unread notifications for this responder type
$sql_count_notifications = "
    SELECT COUNT(*) AS unread_count 
    FROM tbl_notifications
    WHERE is_view = 0
    AND EXISTS (
        SELECT 1 
        FROM tbl_incidents 
        WHERE tbl_incidents.incident_id = tbl_notifications.incident_id
        AND ($pnp_conditions)
    )
";

$stmt_count_notifications = $conn->prepare($sql_count_notifications);
$stmt_count_notifications->execute();
$result_count_notifications = $stmt_count_notifications->fetch(PDO::FETCH_ASSOC);
$unread_count = $result_count_notifications['unread_count'];

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
                        <a href="pcg_dashboard.php">
                            <i class="material-icons">home</i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li>
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
                            <li>
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
                <h2 style="font-size: 25px; font-weight: 900; color: #bc1823 !important;">DASHBOARD</h2>
            </div>
            <!-- Widgets -->
            <div class="row clearfix">
                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12" onclick="window.location.href='pcg_pending.php'">
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

                <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12" onclick="window.location.href='pcg_approved.php'">
                    <div style="cursor: pointer;" class="info-box bg-red hover-expand-effect">
                        <div class="icon">
                            <i class="material-icons">check</i>
                        </div>
                        <div class="content">
                            <div class="text" style="color: white !important;">APPROVE COMPLAIN</div>
                            <div class="" style="font-size: 20px;"><?php echo $total_incidents_approved ?></div>
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
</body>

</html>