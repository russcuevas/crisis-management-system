<?php
include '../database/connection.php';

session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

// READ USER
$get_users = "SELECT * FROM `tbl_users`";
$get_stmt = $conn->query($get_users);
$users = $get_stmt->fetchAll(PDO::FETCH_ASSOC);
// END READ USER

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
                    <li>
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
                    <li class="active">
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
                    <li><a href="dashboard.php"><i style="font-size: 20px;" class="material-icons">home</i>
                            Dashboard</a></li>
                    <li class="active"><i style="font-size: 20px;" class="material-icons">groups</i>
                        Users Management
                    </li>
                </ol>
            </div>

            <!-- CPU Usage -->
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2 class="m-0" style="font-size: 25px; font-weight: 900; color: #bc1823;">
                                USERS MANAGEMENT
                            </h2>
                        </div>
                        <div class="body">

                            <!-- ALERTS -->
                            <?php if (isset($_SESSION['users_success'])): ?>
                                <div class="alert alert-success">
                                    <?php echo $_SESSION['users_success']; ?>
                                    <?php unset($_SESSION['users_success']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <?php if (isset($_SESSION['users_error'])): ?>
                                <div class="alert alert-danger">
                                    <?php echo $_SESSION['users_error']; ?>
                                    <?php unset($_SESSION['users_error']);
                                    ?>
                                </div>
                            <?php endif; ?>

                            <div class="table-responsive">
                                <table class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                    <thead>
                                        <tr>
                                            <th>Fullname</th>
                                            <th>Email</th>
                                            <th>Contact</th>
                                            <th>Address</th>
                                            <th>Verified</th>
                                            <th>Created At</th>
                                            <th>Updated At</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($users as $user) : ?>
                                            <tr>
                                                <td><?php echo $user['fullname'] ?></td>
                                                <td><?php echo $user['email'] ?></td>
                                                <td><?php echo $user['contact'] ?></td>
                                                <td>
                                                    <?php echo $user['purok'] ?>
                                                    <?php echo $user['barangay'] ?>
                                                    <?php echo $user['municipality'] ?>
                                                    <?php echo $user['province'] ?>
                                                </td>
                                                <td><?php echo $user['is_verified'] ?></td>
                                                <td><?php echo $user['created_at'] ?></td>
                                                <td><?php echo $user['updated_at'] ?></td>
                                                <td>
                                                    <div style="display: flex !important; justify-content: end; gap: 10px;">
                                                        <a class="btn btn-warning" href="update_users.php?id=<?php echo $user['id']; ?>">UPDATE <i class="fa fa-edit"></i></a>
                                                        <a class="btn btn-danger" href="javascript:void(0);" data-toggle="modal" data-target="#deleteUserModal" onclick="setUserId(<?php echo $user['id']; ?>)">
                                                            DELETE <i class="fa fa-trash"></i>
                                                        </a>
                                                    </div>



                                                    <!-- DELETE USER MODAL -->
                                                    <div class="modal fade" id="deleteUserModal" tabindex="-1" role="dialog" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
                                                        <div class="modal-dialog" role="document">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <h5 class="modal-title" id="deleteUserModalLabel">Confirm Deletion</h5>
                                                                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                                        <span aria-hidden="true">&times;</span>
                                                                    </button>
                                                                </div>
                                                                <div class="modal-body">
                                                                    Are you sure you want to delete this user? all records will also deleted
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <form id="deleteUserForm" method="POST" action="delete_users.php">
                                                                        <input type="hidden" name="id" id="user_id_delete">
                                                                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
                                                                        <button type="submit" class="btn btn-danger">Delete</button>
                                                                    </form>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
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

    <!-- DELETE USERS -->
    <script>
        function setUserId(userId) {
            document.getElementById('user_id_delete').value = userId;
        }
    </script>


</body>

</html>