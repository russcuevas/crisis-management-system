<?php
include '../../database/connection.php';

session_start();
$responder_id = $_SESSION['responder_id'] ?? null;
$responder_type = $_SESSION['responder_type'] ?? null;

if (!$responder_id || $responder_type !== 'Provincial Health Office') {
    header('Location: ../../login.php');
    exit();
}

// query change password
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['change-password'])) {
        $old_password = $_POST['old_password'];
        $new_password = $_POST['password'];
        $confirm_password = $_POST['password_confirmation'];

        $hashed_old_password = sha1($old_password);
        $hashed_new_password = sha1($new_password);
        $hashed_confirm_password = sha1($confirm_password);

        if ($hashed_new_password !== $hashed_confirm_password) {
            $_SESSION['change_errors'] = 'New password and confirm password do not match.';
            header('Location: change_details.php');
            exit();
        }

        $sql = "SELECT password FROM tbl_responders WHERE id = :id";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':id', $responder_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($result === false) {
            $_SESSION['change_errors'] = 'Error updating password.';
            header('Location: change_details.php');
            exit();
        }

        if ($hashed_old_password !== $result['password']) {
            $_SESSION['change_errors'] = 'Old password is incorrect';
            header('Location: change_details.php');
            exit();
        }

        $update_sql = "UPDATE tbl_responders SET password = :new_password WHERE id = :id";
        $update_stmt = $conn->prepare($update_sql);
        $update_stmt->bindParam(':new_password', $hashed_new_password, PDO::PARAM_STR);
        $update_stmt->bindParam(':id', $responder_id, PDO::PARAM_INT);

        if ($update_stmt->execute()) {
            $_SESSION['change_success'] = 'Password updated successfully.';
            header('Location: change_details.php');
            exit();
        } else {
            $_SESSION['change_errors'] = 'Error updating password.';
            header('Location: change_details.php');
            exit();
        }
    }
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css"
        integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
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
                        <a href="pho_dashboard.php">
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
                                <a href="pho_pending.php">
                                    <span>Pending</span>
                                </a>
                            </li>
                            <li>
                                <a href="pho_approved.php">
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
    </section>

    <section class="content">
        <div class="container-fluid">
            <div class="block-header">
                <ol style="font-size: 15px;" class="breadcrumb breadcrumb-col-red">
                    <li><a href="pho_dashboard.php"><i style="font-size: 20px;" class="material-icons">groups</i>
                            Dashboard</a></li>
                    <li class="active"><i style="font-size: 20px;" class="material-icons">edit</i>
                        Change details
                    </li>
                </ol>
            </div>
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>Update password</h2>
                        </div>
                        <div class="body">

                            <!-- ALERTS -->
                            <?php if (isset($_SESSION['change_success'])): ?>
                                <div class="alert alert-success">
                                    <?php echo $_SESSION['change_success']; ?>
                                    <?php unset($_SESSION['change_success']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['change_errors'])): ?>
                                <div class="alert alert-danger">
                                    <?php echo $_SESSION['change_errors']; ?>
                                    <?php unset($_SESSION['change_errors']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <form id="form_validation" method="POST" action="">
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

                                <div class="align-right">
                                    <button type="submit" name="change-password" class="btn bg-red waves-effect">Save Changes</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Basic Validation -->
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