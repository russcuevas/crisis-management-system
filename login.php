<?php
session_start();
include('database/connection.php');

if (isset($_SESSION['user_id'])) {
    header('Location: home.php');
    exit();
}

if (isset($_SESSION['admin_id'])) {
    header('Location: admin/dashboard.php');
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    $userType = $_POST['user_type'];

    if ($userType == 'user') {
        $query = "SELECT * FROM tbl_users WHERE email = :email";
    } elseif ($userType == 'admin') {
        $query = "SELECT * FROM tbl_admin WHERE email = :email";
    } else {
        $query = "SELECT * FROM tbl_responders WHERE email = :email AND type = :userType";
    }

    $stmt = $conn->prepare($query);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);

    if ($userType !== 'user' && $userType !== 'admin') {
        $stmt->bindParam(':userType', $userType, PDO::PARAM_STR);
    }

    $stmt->execute();

    if ($stmt->rowCount() == 1) {
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (sha1($password) === $user['password']) {
            if ($userType == 'user') {
                if ($user['is_verified'] == 1) {
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['email'] = $user['email'];
                    header('Location: home.php');
                    exit();
                } else {
                    $_SESSION['error_message'] = "Your account is not verified! Check your email.";
                    header('Location: login.php');
                    exit();
                }
            } elseif ($userType == 'admin') {
                $_SESSION['admin_id'] = $user['id'];
                header('Location: admin/dashboard.php');
                exit();
            } else {
                $_SESSION['responder_id'] = $user['id'];
                $_SESSION['responder_type'] = $user['type'];

                switch ($user['type']) {
                    case 'Philippine Coast Guard':
                        header('Location: entities/pcg/pcg_dashboard.php');
                        break;
                    case 'Philippine National Police':
                        header('Location: entities/pnp/pnp_dashboard.php');
                        break;
                    case 'Bureau of Fire':
                        header('Location: entities/bfp/bfp_dashboard.php');
                        break;
                    case 'Provincial Health Office':
                        header('Location: entities/pho/pho_dashboard.php');
                        break;
                    default:
                        header('Location: login.php');
                        exit();
                }
                exit();
            }
        }
    }

    $_SESSION['error_message'] = "Invalid email or password!";
    header('Location: login.php');
    exit();
}
?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Login - Crisis Management System</title>
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">
    <link href="https://fonts.googleapis.com/css?family=Poppins:400,700" rel="stylesheet">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="assets/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
            background: linear-gradient(to right, #000000, #3c0f12);
        }
    </style>
</head>

<body class="login-page">
    <div class="login-box">
        <div class="logo">
            <a href="login.php">
                <img src="assets/images/login/crisis.jpg" alt="CMS Logo" class="logo-img">
                <span class="logo-text" style="font-size: 50px; color: whitesmoke; font-weight: 600;">CMS</span>
            </a>
        </div>

        <div class="card">
            <div class="body">
                <form id="sign_in" method="POST">
                    <div class="msg"><span style="font-size: 30px;">Login</span></div>

                    <?php if (isset($_SESSION['error_message'])): ?>
                        <div class="alert alert-danger">
                            <?php echo $_SESSION['error_message'];
                            unset($_SESSION['error_message']); ?>
                        </div>
                    <?php endif; ?>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">person</i>
                        </span>
                        <div class="form-line">
                            <select name="user_type" class="form-control" required>
                                <option value="" disabled selected>Select Type</option>
                                <option value="admin">Admin / PDRRMO</option>
                                <option value="user">User</option>
                                <option value="Philippine Coast Guard">Philippine Coast Guard - PCG</option>
                                <option value="Philippine National Police">Philippine National Police - PNP</option>
                                <option value="Bureau of fire">Bureau Of Fire Protection - BFP</option>
                                <option value="Provincial Health Office">Provincial Health Office - PHO</option>
                            </select>
                        </div>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">email</i>
                        </span>
                        <div class="form-line">
                            <input type="text" class="form-control" name="email" placeholder="Email" required autofocus>
                        </div>
                    </div>

                    <div class="input-group">
                        <span class="input-group-addon">
                            <i class="material-icons">lock</i>
                        </span>
                        <div class="form-line">
                            <input type="password" class="form-control" name="password" placeholder="Password" required>
                        </div>
                    </div>

                    <button class="btn btn-block btn-lg bg-red waves-effect" type="submit">LOGIN</button>

                    <div class="m-t-25 m-b--5 align-center">
                        <a href="register.php">If you don't have an account, click here</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="assets/plugins/jquery/jquery.min.js"></script>
    <script src="assets/plugins/bootstrap/js/bootstrap.js"></script>
    <script src="assets/plugins/node-waves/waves.js"></script>
    <script src="assets/plugins/jquery-validation/jquery.validate.js"></script>
    <script src="assets/js/admin.js"></script>
    <script src="assets/js/pages/examples/sign-in.js"></script>
</body>

</html>