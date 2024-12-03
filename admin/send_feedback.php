<?php
include '../database/connection.php';
require '../vendor/autoload.php';
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

session_start();
$admin_id = $_SESSION['admin_id'];
if (!isset($admin_id)) {
    header('location:admin_login.php');
}

if (isset($_GET['id'])) {
    $feedback_id = $_GET['id'];
    
    $get_feedback_query = "SELECT * FROM `tbl_feedback` WHERE id = :feedback_id";
    $stmt = $conn->prepare($get_feedback_query);
    $stmt->bindParam(':feedback_id', $feedback_id);
    $stmt->execute();
    $feedback = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($feedback) {
        $sender = $feedback['fullname'];
        $email = $feedback['email'];
        $question = $feedback['question'];
        $feedback_text = $feedback['feedback'];
    } else {
        echo "Feedback not found.";
    }
} else {
    echo "No feedback ID provided.";
}

function sendResponseEmail($recipientEmail, $adminResponse, $question, $feedback_text) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'crisismanagement001@gmail.com';
        $mail->Password = 'esbtdkbkszzputyq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;
        $mail->setFrom('crisismanagement001@gmail.com', 'Crisis Management System');
        $mail->addAddress($recipientEmail);

        $mail->isHTML(true);
        $mail->Subject = 'Response to Your Feedback';

        $mail->Body = "Dear User,<br><br>" .
                      "Thank you for your feedback!<br><br>" .
                      "We have reviewed your question: <b>$question</b><br>" .
                      "Your feedback: <i>$feedback_text</i><br><br>" .
                      "Our response: <p>$adminResponse</p><br><br>" .
                      "Best regards,<br>" .
                      "Crisis Management Team";
        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}


// sending email response
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['response'])) {
    $response = $_POST['response'];
    if (sendResponseEmail($email, $response, $question, $feedback_text)) {
        $_SESSION['feedback_success'] = 'Response sent successfully!';
        header('Location: feedback.php');
        exit();
    } else {
        $_SESSION['feedback_error'] = 'Failed to send the response.';
        header('Location: feedback.php');
        exit();
    }    
}
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
    <!-- Hold ON -->
    <link href="css/HoldOn.css" rel="stylesheet">
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
                    <li><a href="feedback.php"><i style="font-size: 20px;" class="material-icons">feedback</i>
                            Feedback</a></li>
                    <li class="active"><i style="font-size: 20px;" class="material-icons">email</i>
                        Send an email response
                    </li>
                </ol>
            </div>
            <div class="row clearfix">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="card">
                        <div class="header">
                            <h2>Send Response</h2>
                        </div>
                        <div class="body">
                        <form id="form_validation" method="POST">
                        <div class="form-group form-float">
                                    <label class="form-label">Sender</label>
                                    <input style="background-color: gray; color: whitesmoke" type="text" class="form-control" value="<?php echo htmlspecialchars($sender); ?>" readonly>
                                </div>
                                <div class="form-group form-float">
                                    <label class="form-label">Email</label>
                                    <input style="background-color: gray; color: whitesmoke" type="email" class="form-control" value="<?php echo htmlspecialchars($email); ?>" readonly>
                                </div>

                                <div class="form-group form-float">
                                    <label class="form-label">Question</label>
                                    <textarea style="background-color: gray; color: whitesmoke" class="form-control" rows="3" readonly><?php echo htmlspecialchars($question); ?></textarea>
                                </div>

                                <div class="form-group form-float">
                                    <label class="form-label">Feedback</label>
                                    <textarea style="background-color: gray; color: whitesmoke" class="form-control" rows="3" readonly><?php echo htmlspecialchars($feedback_text); ?></textarea>
                                </div>

                                <div class="form-group form-float">
                                    <label class="form-label">Your Response</label>
                                    <div class="form-line">
                                        <textarea class="form-control" name="response" rows="4" required></textarea>
                                    </div>
                                </div>

                                <div class="align-right">
                                    <button type="submit" class="btn bg-red waves-effect">Send Response</button>
                                    <a href="feedback.php" class="btn btn-link waves-effect">Cancel</a>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
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

    <!-- Hold On -->
    <script src="js/HoldOn.js"></script>

    <!-- EMAIL RESPONSE -->
    <script type="text/javascript">
        document.addEventListener("DOMContentLoaded", function() {
            const form = document.querySelector('form#form_validation');
            form.addEventListener('submit', function(event) {
                const response = document.querySelector('[name="response"]').value.trim();
                
                if (!response) {
                    event.preventDefault();
                    return;
                }
                HoldOn.open({
                    theme: "sk-bounce",
                    message: "Sending your response...",
                    backgroundColor: "rgba(0, 0, 0, 0.7)",
                    textColor: "white",
                    spinnerColor: "#fff"
                });
            });

            <?php if (isset($_SESSION['feedback_success']) || isset($_SESSION['feedback_error'])): ?>
                setTimeout(function() {
                    HoldOn.close();
                }, 2000);
            <?php endif; ?>
        });
    </script>
    <!-- END EMAIL RESPONSE -->
    <!-- Demo Js -->
    <script src="js/demo.js"></script>
</body>
</html>
