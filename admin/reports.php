<?php
include '../database/connection.php';

session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

// Build the SQL query
$sql = "SELECT tbl_reports.*, tbl_users.fullname 
        FROM tbl_reports 
        LEFT JOIN tbl_users ON tbl_reports.user_id = tbl_users.id";

// Apply filtering if month or year is selected
if ($month && $year) {
    $sql .= " WHERE MONTH(tbl_reports.incident_datetime) = :month AND YEAR(tbl_reports.incident_datetime) = :year";
}

// Execute the query
$stmt = $conn->prepare($sql);

// Bind the parameters if filtering
if ($month && $year) {
    $stmt->bindParam(':month', $month, PDO::PARAM_STR);
    $stmt->bindParam(':year', $year, PDO::PARAM_STR);
}

$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);

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

                    <li class="active">
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
                    <li class="active"><i style="font-size: 20px;" class="material-icons">report</i>
                        Reports
                    </li>
                </ol>
            </div>

            <!-- CPU Usage -->
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <div style="display: flex; justify-content: space-between; align-items: center; width: 100%;">
                                <h2 class="m-0" style="font-size: 25px; font-weight: 900; color: #bc1823;">
                                    REPORTS SUMMARY
                                </h2>
                                <div id="print-container">
                                    <button type="submit" class="btn bg-red waves-effect btn-sm">
                                        <i class="material-icons">print</i>
                                        <span>DOWNLOAD FOR PRINT</span>
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="body">
                            <!-- Filtering Form -->
                            <form method="GET" action="reports.php">
                                <div class="row">
                                    <div class="col-md-4">
                                        <select name="month" class="form-control" required>
                                            <option value="">Select Month</option>
                                            <option value="01" <?php if (isset($_GET['month']) && $_GET['month'] == '01') echo 'selected'; ?>>January</option>
                                            <option value="02" <?php if (isset($_GET['month']) && $_GET['month'] == '02') echo 'selected'; ?>>February</option>
                                            <option value="03" <?php if (isset($_GET['month']) && $_GET['month'] == '03') echo 'selected'; ?>>March</option>
                                            <option value="04" <?php if (isset($_GET['month']) && $_GET['month'] == '04') echo 'selected'; ?>>April</option>
                                            <option value="05" <?php if (isset($_GET['month']) && $_GET['month'] == '05') echo 'selected'; ?>>May</option>
                                            <option value="06" <?php if (isset($_GET['month']) && $_GET['month'] == '06') echo 'selected'; ?>>June</option>
                                            <option value="07" <?php if (isset($_GET['month']) && $_GET['month'] == '07') echo 'selected'; ?>>July</option>
                                            <option value="08" <?php if (isset($_GET['month']) && $_GET['month'] == '08') echo 'selected'; ?>>August</option>
                                            <option value="09" <?php if (isset($_GET['month']) && $_GET['month'] == '09') echo 'selected'; ?>>September</option>
                                            <option value="10" <?php if (isset($_GET['month']) && $_GET['month'] == '10') echo 'selected'; ?>>October</option>
                                            <option value="11" <?php if (isset($_GET['month']) && $_GET['month'] == '11') echo 'selected'; ?>>November</option>
                                            <option value="12" <?php if (isset($_GET['month']) && $_GET['month'] == '12') echo 'selected'; ?>>December</option>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <select name="year" class="form-control" required>
                                            <option value="">Select Year</option>
                                            <?php
                                            $currentYear = date('Y');
                                            for ($i = 2024; $i <= $currentYear; $i++) {
                                                echo "<option value='$i'" . (isset($_GET['year']) && $_GET['year'] == $i ? ' selected' : '') . ">$i</option>";
                                            }
                                            ?>
                                        </select>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="d-flex justify-content-between">
                                            <button type="submit" class="btn bg-red waves-effect btn-sm" style="width:48%;">FILTER
                                                <i style="font-size: 15px;" class="material-icons">filter_alt</i>
                                            </button>
                                            <a href="reports.php" class="btn bg-black waves-effect btn-sm" style="width:48%; text-align:center;">RESET
                                                <i style="font-size: 15px;" class="material-icons">restart_alt</i>
                                            </a>
                                        </div>
                                    </div>

                                </div>
                            </form>


                            <div class="table-responsive">
                                <table id="reportTable" class="table table-bordered table-striped table-hover js-basic-example dataTable">
                                    <thead>
                                        <tr>
                                            <th>Complainant</th>
                                            <th>Type</th>
                                            <th>Description</th>
                                            <th>Location</th>
                                            <th>Landmark</th>
                                            <th>Date/Time</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($complaints as $complaint): ?>
                                            <tr>
                                                <td><?php echo $complaint['fullname'] ?></td>
                                                <td><?php echo $complaint['incident_type'] ?></td>
                                                <td><?php echo $complaint['incident_description'] ?></td>
                                                <td><?php echo $complaint['incident_location_map'] ?></td>
                                                <td><?php echo $complaint['incident_landmark'] ?></td>
                                                <td><?php echo $complaint['incident_datetime'] ?></td>
                                                <td style="color:green; font-weight: 900;"><?php echo $complaint['status'] ?></td>
                                            </tr>
                                        <?php endforeach ?>
                                    </tbody>
                                </table>
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

    <!-- PRINT DATA IN THE SELECTED YEAR AND MONTH -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf-autotable/3.5.26/jspdf.plugin.autotable.min.js"></script>
    <script>
        document.getElementById('print-container').addEventListener('click', function() {
            const {
                jsPDF
            } = window.jspdf;
            const doc = new jsPDF();

            const selectedMonth = document.querySelector('[name="month"]').value;
            const selectedYear = document.querySelector('[name="year"]').value;

            const months = [
                "", "January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"
            ];

            let reportText = '';
            if (selectedMonth && selectedYear) {
                const monthName = months[selectedMonth];
                reportText = `Report for ${monthName} ${selectedYear}`;
            } else {
                reportText = 'Report for All Years and All Months';
            }

            const logoWidth = 25;
            const logoHeight = 25;
            const logoX = (doc.internal.pageSize.width - logoWidth) / 2;
            const logoY = 10;
            doc.addImage('images/admin/crisis.jpg', 'JPEG', logoX, logoY, logoWidth, logoHeight);

            doc.setFontSize(20);
            const title = "Crisis Management System";
            const titleWidth = doc.getTextWidth(title);
            const titleX = (doc.internal.pageSize.width - titleWidth) / 2;
            doc.text(title, titleX, logoY + logoHeight + 5);

            doc.setFontSize(16);
            const reportTextWidth = doc.getTextWidth(reportText);
            const reportTextX = (doc.internal.pageSize.width - reportTextWidth) / 2;
            doc.text(reportText, reportTextX, logoY + logoHeight + 15);

            const reportTextBottomMargin = 10;
            const spaceBeforeTable = logoY + logoHeight + 25 + reportTextBottomMargin;

            const table = document.getElementById('reportTable');
            doc.autoTable({
                html: table,
                startY: spaceBeforeTable,
                theme: 'grid',
                headStyles: {
                    fillColor: '#bc1823',
                    textColor: '#ffffff',
                    fontSize: 10,
                },
                styles: {
                    fontSize: 10,
                }
            });

            doc.save('report-summary.pdf');
        });
    </script>










</body>

</html>