<?php
include '../database/connection.php';

session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_GET['id'])) {
    $admin_id = $_GET['id'];
    $get_user = "SELECT * FROM `tbl_admin` WHERE `id` = :id";
    $stmt = $conn->prepare($get_user);
    $stmt->bindParam(':id', $admin_id);
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$user) {
        $_SESSION['error'] = "Admin ID Not Found";
        header('location:manage_admin.php');
        exit();
    }
} else {
    $_SESSION['error'] = "Admin ID Not Found";
    header('location:manage_admin.php');
    exit();
}

// update function
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email']);
    $password = trim($_POST['password']);

    if (!empty($password)) {
        $hashed_password = sha1($password);
        $update_admin = "UPDATE `tbl_admin` SET `email` = :email, `password` = :password WHERE `id` = :id";
        $stmt = $conn->prepare($update_admin);
        $stmt->bindParam(':password', $hashed_password);
    } else {
        $update_admin = "UPDATE `tbl_admin` SET `email` = :email WHERE `id` = :id";
        $stmt = $conn->prepare($update_admin);
    }

    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':id', $admin_id);

    if ($stmt->execute()) {
        $_SESSION['success'] = "Admin has been updated successfully!";
        header('Location: manage_admin.php');
        exit();
    } else {
        $_SESSION['error'] = "Failed to update admin information.";
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
    <!-- Bootstrap Select Css -->
    <link href="plugins/bootstrap-select/css/bootstrap-select.css" rel="stylesheet" />
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css"
        integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <style>
        .align-right {
            display: flex;
            justify-content: flex-end;
            gap: 10px;
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
                    <li>
                        <a href="dashboard.php">
                            <i class="material-icons">home</i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li class="active">
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
                <ol style="font-size: 15px;" class="breadcrumb breadcrumb-col-red">
                    <li><a href="users.php"><i style="font-size: 20px;" class="material-icons">admin_panel_settings</i>
                            Admin Management</a></li>
                    <li class="active"><i style="font-size: 20px;" class="material-icons">edit</i>
                        Update Admin
                    </li>
                </ol>
            </div>
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>Update Admin</h2>
                        </div>
                        <div class="body">
                            <form id="form_validation" method="POST">
                                <div class="form-group form-float">
                                    <label class="form-label">Email</label>
                                    <div class="form-line">
                                        <input type="email" class="form-control" name="email" value="<?php echo $user['email']; ?>" required>
                                    </div>
                                </div>

                                <div class="form-group form-float">
                                    <label style="color: #212529; font-weight: 600;" class="form-label">Password</label>
                                    <div class="form-line">
                                        <input type="password" class="form-control" name="password" maxlength="12" minlength="6">
                                    </div>
                                </div>

                                <div class="align-right">
                                    <button type="submit" class="btn bg-red waves-effect">Save Changes</button>
                                    <a href="manage_admin.php" class="btn btn-link waves-effect">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- #END# Basic Validation -->
    </section>

    <!-- Jquery Core Js -->
    <script src="plugins/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core Js -->
    <script src="plugins/bootstrap/js/bootstrap.js"></script>

    <!-- Select Plugin Js -->
    <script src="plugins/bootstrap-select/js/bootstrap-select.js"></script>

    <!-- Slimscroll Plugin Js -->
    <script src="plugins/jquery-slimscroll/jquery.slimscroll.js"></script>

    <!-- Jquery Validation Plugin Css -->
    <script src="plugins/jquery-validation/jquery.validate.js"></script>

    <!-- JQuery Steps Plugin Js -->
    <script src="plugins/jquery-steps/jquery.steps.js"></script>

    <!-- Sweet Alert Plugin Js -->
    <script src="plugins/sweetalert/sweetalert.min.js"></script>

    <!-- Waves Effect Plugin Js -->
    <script src="plugins/node-waves/waves.js"></script>

    <!-- Custom Js -->
    <script src="js/admin.js"></script>
    <script src="js/pages/forms/form-validation.js"></script>

    <!-- Demo Js -->
    <script src="js/demo.js"></script>
</body>

</html>