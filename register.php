<?php
session_start();
include 'database/connection.php';

require 'vendor/autoload.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// send verification gmail
function sendVerificationEmail($email, $code)
{
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com';
        $mail->SMTPAuth = true;
        $mail->Username = 'crisismanagement001@gmail.com';
        $mail->Password = 'esbtdkbkszzputyq';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port = 587;

        $mail->setFrom('your-email@gmail.com', 'Crisis Management System');
        $mail->addAddress($email);

        $mail->isHTML(true);
        $mail->Subject = 'Email Verification Code';

        $verification_link = "http://localhost/crisis-management/verify_account.php?email=" . urlencode($email) . "&code=" . $code;

        $mail->Body    = "Please click the following link to verify your email: <a href='$verification_link'>$verification_link</a>";

        $mail->send();
        return true;
    } catch (Exception $e) {
        return false;
    }
}

// register function
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $contact = $_POST['contact'];
    $province = $_POST['province'];
    $purok = $_POST['purok'];
    $barangay = $_POST['barangay'];
    $municipality = $_POST['municipality'];
    $confirm_password = $_POST['confirm'];

    $profile_picture = null;
    if (isset($_FILES['profile_picture']) && $_FILES['profile_picture']['error'] == 0) {
        $target_dir = "assets/images/profile/";
        $target_file = $target_dir . basename($_FILES["profile_picture"]["name"]);

        $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
        $allowed_types = ['jpg', 'jpeg', 'png', 'gif'];

        if (in_array($imageFileType, $allowed_types)) {
            if (move_uploaded_file($_FILES["profile_picture"]["tmp_name"], $target_file)) {
                $profile_picture = basename($target_file);
            } else {
                $_SESSION['error'] = 'Sorry, there was an error uploading your file.';
                header('Location: register.php');
                exit();
            }
        } else {
            $_SESSION['error'] = 'Only image files (jpg, jpeg, png, gif) are allowed.';
            header('Location: register.php');
            exit();
        }
    }

    if ($password !== $confirm_password) {
        $_SESSION['error'] = 'Passwords do not match!';
        header('Location: register.php');
        exit();
    }

    $hashed_password = sha1($password);
    $verification_code = rand(100000, 999999);

    $stmt = $conn->prepare("SELECT * FROM tbl_users WHERE email = :email");
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        $_SESSION['error'] = 'Email is already registered!';
        header('Location: register.php');
        exit();
    }

    $query = "INSERT INTO tbl_users (email, password, fullname, contact, province, purok, barangay, municipality, profile_picture, code) 
              VALUES (:email, :password, :fullname, :contact, :province, :purok, :barangay, :municipality, :profile_picture, :code)";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email);
    $stmt->bindParam(':password', $hashed_password);
    $stmt->bindParam(':fullname', $fullname);
    $stmt->bindParam(':contact', $contact);
    $stmt->bindParam(':province', $province);
    $stmt->bindParam(':purok', $purok);
    $stmt->bindParam(':barangay', $barangay);
    $stmt->bindParam(':municipality', $municipality);
    $stmt->bindParam(':profile_picture', $profile_picture);
    $stmt->bindParam(':code', $verification_code);

    if ($stmt->execute()) {
        if (sendVerificationEmail($email, $verification_code)) {
            $_SESSION['success'] = 'Registration successful! Please check your email for verification.';
            header('Location: register.php');
        } else {
            $_SESSION['error'] = 'Failed to send verification email!';
            header('Location: register.php');
        }
    } else {
        $_SESSION['error'] = 'Registration failed!';
        header('Location: register.php');
    }
    exit();
}
?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Crisis Management System</title>
    <!-- Favicon-->
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <!-- Bootstrap Core Css -->
    <link href="assets/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Waves Effect Css -->
    <link href="assets/plugins/node-waves/waves.css" rel="stylesheet" />

    <!-- Custom Css -->
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="assets/css/HoldOn.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
            background: linear-gradient(to right, #000000, #3c0f12) !important;
        }
    </style>
</head>

<body class="signup-page">
    <div class="signup-box">
        <div class="logo">
            <a href="home.php" class="logo-link">
                <img src="assets/images/login/crisis.jpg" alt="Crisis Management System Logo" class="logo-img">
                <span class="logo-text" style="font-size: 50px; color: whitesmoke;">CMS</span>
            </a>
        </div>

        <div class="card">
            <div class="body">
                <form id="sign_up" method="POST" enctype="multipart/form-data">
                    <div class="msg"><span style="font-size: 30px;">Register</span></div>

                    <?php
                    if (isset($_SESSION['error'])) {
                        echo '<div class="alert alert-danger">' . $_SESSION['error'] . '</div>';
                        unset($_SESSION['error']);
                    }
                    if (isset($_SESSION['success'])) {
                        echo '<div class="alert alert-success">' . $_SESSION['success'] . '</div>';
                        unset($_SESSION['success']);
                    }
                    ?>

                    <div class="row">
                        <div class="col-md-6 col-left">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">image</i>
                                </span>
                                <label style="font-weight: 200 !important" for="">Profile Picture <span
                                        style="font-size: 10px; color: brown;">(Optional)</span>
                                </label>
                                <div class="form-line">
                                    <input type="file" class="form-control" name="profile_picture" id="profile_picture"
                                        placeholder="Upload Profile Picture" accept="image/*"
                                        onchange="previewImage(event)">
                                </div>
                            </div>

                            <div id="image_preview" style="display:none;">
                                <img id="preview_img" src="" alt="Selected Image"
                                    style="margin-left: 40px; width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc; padding: 5px; background-color: #f9f9f9;">
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">person</i>
                                </span>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="fullname" placeholder="Full Name"
                                        required autofocus>
                                </div>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">mail</i>
                                </span>
                                <div class="form-line">
                                    <input type="email" class="form-control" name="email" placeholder="Email Address"
                                        required>
                                </div>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">lock</i>
                                </span>
                                <div class="form-line">
                                    <input type="password" class="form-control" name="password" minlength="6"
                                        placeholder="Password" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">lock</i>
                                </span>
                                <div class="form-line">
                                    <input type="password" class="form-control" name="confirm" minlength="6"
                                        placeholder="Confirm Password" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">phone</i>
                                </span>
                                <div class="form-line">
                                    <input type="tel" class="form-control" name="contact" placeholder="Contact Number"
                                        required>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side - Address Info -->
                        <div class="col-md-6 col-right">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">place</i>
                                </span>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="province" placeholder="Province" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">place</i>
                                </span>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="purok" placeholder="Purok" required>
                                </div>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">place</i>
                                </span>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="barangay" placeholder="Barangay"
                                        required>
                                </div>
                            </div>

                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">place</i>
                                </span>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="municipality"
                                        placeholder="Municipality" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn btn-block btn-lg bg-red waves-effect" type="submit">SIGN UP</button>

                    <div class="m-t-25 m-b--5 align-center">
                        <a href="login.php">If you already have an account click to login</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Jquery Core Js -->
    <script src="assets/plugins/jquery/jquery.min.js"></script>

    <!-- Bootstrap Core Js -->
    <script src="assets/plugins/bootstrap/js/bootstrap.js"></script>

    <!-- Waves Effect Plugin Js -->
    <script src="assets/plugins/node-waves/waves.js"></script>

    <!-- Validation Plugin Js -->
    <script src="assets/plugins/jquery-validation/jquery.validate.js"></script>

    <!-- Custom Js -->
    <script src="assets/js/HoldOn.js"></script>
    <script src="assets/js/admin.js"></script>
    <script src="assets/js/pages/examples/sign-up.js"></script>

    <!-- HOLD ON FUNCTIONS -->
    <script>
        function previewImage(event) {
            var file = event.target.files[0];
            var reader = new FileReader();

            reader.onload = function(e) {
                var imagePreview = document.getElementById('image_preview');
                var previewImg = document.getElementById('preview_img');
                imagePreview.style.display = 'block';
                previewImg.src = e.target.result;
            };

            if (file) {
                reader.readAsDataURL(file);
            }
        }

        document.addEventListener("DOMContentLoaded", function() {
            if (<?php echo isset($_SESSION['success']) || isset($_SESSION['error']) ? 'true' : 'false'; ?>) {
                HoldOn.open({
                    theme: "sk-bounce",
                    message: "Processing your request...",
                    backgroundColor: "rgba(0, 0, 0, 0.7)",
                    textColor: "white",
                    spinnerColor: "#fff"
                });

                setTimeout(function() {
                    HoldOn.close();
                }, 2000);
            }
        });

        document.getElementById('sign_up').addEventListener('submit', function(event) {
            var requiredFields = document.querySelectorAll('[required]');
            var formValid = true;

            requiredFields.forEach(function(field) {
                if (field.value.trim() === '') {
                    formValid = false;
                    field.style.borderColor = 'red';
                } else {
                    field.style.borderColor = '';
                }
            });

            if (formValid) {
                HoldOn.open({
                    theme: "sk-bounce",
                    message: "Submitting your registration...",
                    backgroundColor: "rgba(0, 0, 0, 0.7)",
                    textColor: "white",
                    spinnerColor: "#fff"
                });
            } else {
                event.preventDefault();
            }
        });
    </script>

</body>

</html>