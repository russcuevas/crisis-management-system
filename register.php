<!DOCTYPE html>
<html>

<head>
    <meta charset="UTF-8">
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <title>Crisis Management System</title>
    <!-- Favicon-->
    <link rel="icon" href="assets/favicon.ico" type="image/x-icon">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Roboto:400,700&subset=latin,cyrillic-ext" rel="stylesheet"
        type="text/css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet" type="text/css">

    <!-- Bootstrap Core Css -->
    <link href="assets/plugins/bootstrap/css/bootstrap.css" rel="stylesheet">

    <!-- Waves Effect Css -->
    <link href="assets/plugins/node-waves/waves.css" rel="stylesheet" />

    <!-- Animation Css -->
    <link href="assets/plugins/animate-css/animate.css" rel="stylesheet" />

    <!-- Custom Css -->
    <link href="assets/css/style.css" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif !important;
            background-color: #f5f5f5 !important;
        }
    </style>
</head>

<body class="signup-page">
    <div class="signup-box">
        <div class="logo">
            <a href="home.php" class="logo-link">
                <img src="assets/images/login/crisis.jpg" alt="Crisis Management System Logo" class="logo-img">
                <span class="logo-text" style="font-size: 50px; color: #bc1823;">CMS</span>
            </a>
        </div>

        <div class="card">
            <div class="body">
                <form id="sign_up" method="POST">
                    <div class="msg"><span style="font-size: 30px;">Register</span></div>

                    <!-- Start of the row for left and right sections -->
                    <div class="row">
                        <!-- Left Side - Personal Info -->
                        <div class="col-md-6 col-left">
                            <div class="input-group">
                                <span class="input-group-addon">
                                    <i class="material-icons">image</i>
                                </span>
                                <label for="">Profile Picture <span
                                        style="font-size: 20px; color: brown;">(Optional)</span>
                                </label>
                                <div class="form-line">
                                    <!-- Image upload input -->
                                    <input type="file" class="form-control" name="profile_picture" id="profile_picture"
                                        placeholder="Upload Profile Picture" accept="image/*"
                                        onchange="previewImage(event)">
                                </div>
                            </div>

                            <!-- Box to display the selected image -->
                            <div id="image_preview" style="display:none; margin-top: 10px;">
                                <label>Selected Image:</label><br>
                                <img id="preview_img" src="" alt="Selected Image"
                                    style="width: 100px; height: 100px; object-fit: cover; border: 1px solid #ccc; padding: 5px; background-color: #f9f9f9;">
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
                                    <i class="material-icons">location_city</i>
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
                                    <i class="material-icons">map</i>
                                </span>
                                <div class="form-line">
                                    <input type="text" class="form-control" name="municipality"
                                        placeholder="Municipality" required>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button class="btn btn-block btn-lg bg-red waves-effect" type="submit">SIGN UP</button>

                    <div class="m-t-25 m-b--5 align-center">
                        <a href="login.php">You already have an account? click here to login</a>
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
    <script src="assets/js/admin.js"></script>
    <script src="assets/js/pages/examples/sign-up.js"></script>
    <script>
        function previewImage(event) {
            var output = document.getElementById('preview_img');
            var imagePreviewBox = document.getElementById('image_preview');

            if (event.target.files && event.target.files[0]) {
                var reader = new FileReader();

                reader.onload = function(e) {
                    output.src = e.target.result;
                    imagePreviewBox.style.display = "block";
                };

                reader.readAsDataURL(event.target.files[0]);
            } else {
                imagePreviewBox.style.display = "none";
            }
        }
    </script>
</body>

</html>