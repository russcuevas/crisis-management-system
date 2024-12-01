<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>User Profile - Crisis Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background-color: #f5f5f5;
            margin: 0;
            padding: 0;
            color: #333333;
        }

        .navbar {
            background-color: #bc1823 !important;
            padding: 15px;
        }

        .navbar .navbar-brand {
            font-weight: 600;
            font-size: 15px;
            color: #fff;
        }

        .navbar .navbar-nav .nav-link {
            color: #fff;
        }

        .navbar .navbar-nav .nav-link:hover {
            color: black;
        }

        .navbar-toggler-icon {
            color: white !important;
        }

        .profile-container {
            padding: 30px;
        }

        .profile-box-header {
            border: 2px dotted #bc1823;
            padding: 30px;
            font-size: 1.1rem;
            max-width: 500px;
            margin: 0 auto;
            border-radius: 15px;
            background-color: #f8f8f8;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        .profile-header {
            text-align: center;
            margin-bottom: 20px;
        }

        .profile-header img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            margin-bottom: 15px;
        }

        .profile-header h2 {
            font-size: 2.5rem;
            color: #bc1823;
            font-weight: 600;
            margin-top: 10px;
        }

        .profile-details {
            background-color: #fff;
            border: 1px solid #e0e0e0;
            padding: 20px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
            font-size: 1.1rem;
            max-width: 500px;
            width: 100%;
            margin: 0 auto;
            margin-top: 5px;
        }

        .profile-details p {
            font-size: 1.2rem;
            color: #666;
        }

        .update-btn {
            display: block;
            width: 200px;
            margin: 20px auto;
            font-size: 1.2rem;
            text-align: center;
        }

        footer {
            background-color: #bc1823;
            color: white;
            padding: 20px;
            text-align: center;
        }

        footer a {
            color: black;
            text-decoration: none;
        }

        footer a:hover {
            color: white;
        }
    </style>
</head>

<body>

    <nav class="navbar navbar-expand-lg navbar-light bg-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="home.php"><img style="height: 50px; width: 50px; border-radius: 50%;"
                    src="assets/images/login/crisis.jpg" alt=""> Crisis Management
                System</a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav"
                aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Home</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="about.php">About Us</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="reportsDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Reports
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="reportsDropdown">
                            <li><a class="dropdown-item" href="reports.php">Post Complain</a></li>
                            <li><a class="dropdown-item" href="history.php">View History</a></li>

                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Profile
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <li><a class="dropdown-item" href="profile.php">Change Details</a></li>
                            <li><a class="dropdown-item" href="#">Logout</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Profile Section -->
    <div class="profile-container">
        <div class="profile-box-header">
            <div class="profile-header">
                <img src="https://via.placeholder.com/150" alt="User Photo">
                <h2>Sample</h2>
            </div>
        </div>

        <div class="profile-details">
            <p><strong>Email:</strong> sample@example.com</p>
            <p><strong>Contact Number:</strong> +63 912 345 6789</p>
            <p><strong>Purok:</strong> 5</p>
            <p><strong>Barangay:</strong> San Isidro</p>
            <p><strong>Municipality:</strong> Tarlac City</p>
            <!-- Update Button -->
            <button class="btn btn-warning update-btn" data-bs-toggle="modal"
                data-bs-target="#updateProfileModal">Update Profile</button>
        </div>

        <!-- Update Form -->
        <div class="modal fade" id="updateProfileModal" tabindex="-1" aria-labelledby="updateProfileModalLabel"
            aria-hidden="true">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="updateProfileModalLabel">Update Profile</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form>
                            <!-- Left side fields (Email, Password) -->
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="email" class="form-label">Email Address</label>
                                        <input type="email" class="form-control" id="email" value="sample@gmail.com">
                                    </div>
                                    <div class="mb-3">
                                        <label for="password" class="form-label">New Password</label>
                                        <input type="password" class="form-control" id="password"
                                            placeholder="Enter new password">
                                    </div>
                                    <div class="mb-3">
                                        <label for="confirmPassword" class="form-label">Confirm Password</label>
                                        <input type="password" class="form-control" id="confirmPassword"
                                            placeholder="Confirm new password">
                                    </div>
                                    <div class="mb-3">
                                        <label for="contactNumber" class="form-label">Contact Number</label>
                                        <input type="tel" class="form-control" id="contactNumber"
                                            value="+63 912 345 6789">
                                    </div>
                                </div>

                                <!-- Right side fields (Purok, Barangay, Municipality) -->
                                <div class="col-md-6 ms-auto">
                                    <div class="mb-3">
                                        <label for="purok" class="form-label">Purok</label>
                                        <input type="text" class="form-control" id="purok" value="5">
                                    </div>
                                    <div class="mb-3">
                                        <label for="barangay" class="form-label">Barangay</label>
                                        <input type="text" class="form-control" id="barangay" value="San Isidro">
                                    </div>
                                    <div class="mb-3">
                                        <label for="municipality" class="form-label">Municipality</label>
                                        <input type="text" class="form-control" id="municipality" value="Tarlac City">
                                    </div>
                                </div>
                            </div>

                            <!-- Save Changes Button -->
                            <div class="col-md-12 text-end">
                                <button type="submit" class="btn btn-danger">Save Changes</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Crisis Management System - All Rights Reserved | <a href="#">Privacy Policy</a> | <a
                href="#">Terms</a></p>
    </footer>

    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

</body>

</html>