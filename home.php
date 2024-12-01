<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Crisis Management System</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.1/css/all.min.css"
        integrity="sha512-5Hs3dF2AEPkpNAR7UiOHba+lRSJNeM2ECkwxUIxC1Q/FLycGTbNapWXB4tP889k5T5Ju8fs4b1P5z/iB4nMfSQ=="
        crossorigin="anonymous" referrerpolicy="no-referrer" />
    <link rel="stylesheet" href="assets/css/home.css">

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
                        <a class="nav-link active" href="home.php">Home</a>
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

    <!-- Main Content -->
    <div class="container main-content">
        <h2>Home</h2>

        <!-- Post Cards -->
        <div class="row">
            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card news-feed">
                    <!-- Status Banner -->
                    <div class="badge bg-warning text-dark position-absolute" style="top: 10px; right: 10px;">Pending
                        Request</div>

                    <!-- Card Header with User -->
                    <div class="card-header">
                        <i class="fa-solid fa-user"></i> Russel Vincent Cuevas
                    </div>

                    <!-- Card Body with Incident Details -->
                    <div class="card-body">
                        <h5>TYPE OF INCIDENT - <strong>FIRE</strong></h5>
                        <p><strong>Incident Proof - </strong> <img src="https://via.placeholder.com/600x400"
                                alt="Incident Proof" class="post-image"></p>
                        <p><strong>Location - </strong> Sample Location</p>
                        <p><strong>Landmark - </strong> Sample Landmark</p>
                        <p><strong>Date and Time - </strong> 2024-11-30 14:30</p>

                        <a href="#" class="btn btn-primary">VIEW PINNED MAP</a>
                    </div>

                    <!-- Card Footer with Date and Time -->
                    <div class="card-footer">
                        <span>Posted:</span>
                        <span>December 01 2024<br>2 mins ago</span>
                    </div>
                </div>
            </div>


            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card news-feed">
                    <!-- Status Banner -->
                    <div class="badge bg-warning text-dark position-absolute" style="top: 10px; right: 10px;">Pending
                        Request</div>

                    <!-- Card Header with User -->
                    <div class="card-header">
                        <i class="fa-solid fa-user"></i> Russel Vincent Cuevas
                    </div>

                    <!-- Card Body with Incident Details -->
                    <div class="card-body">
                        <h5>TYPE OF INCIDENT - <strong>FIRE</strong></h5>
                        <p><strong>Incident Proof - </strong> <img src="https://via.placeholder.com/600x400"
                                alt="Incident Proof" class="post-image"></p>
                        <p><strong>Location - </strong> Sample Location</p>
                        <p><strong>Landmark - </strong> Sample Landmark</p>
                        <p><strong>Date and Time - </strong> 2024-11-30 14:30</p>

                        <a href="#" class="btn btn-primary">VIEW PINNED MAP</a>
                    </div>

                    <!-- Card Footer with Date and Time -->
                    <div class="card-footer">
                        <span>Posted:</span>
                        <span>December 01 2024<br>2 mins ago</span>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-6 col-lg-4 mb-4">
                <div class="card news-feed">
                    <!-- Status Banner -->
                    <div class="badge bg-warning text-dark position-absolute" style="top: 10px; right: 10px;">Pending
                        Request</div>

                    <!-- Card Header with User -->
                    <div class="card-header">
                        <i class="fa-solid fa-user"></i> Russel Vincent Cuevas
                    </div>

                    <!-- Card Body with Incident Details -->
                    <div class="card-body">
                        <h5>TYPE OF INCIDENT - <strong>FIRE</strong></h5>
                        <p><strong>Incident Proof - </strong> <img src="https://via.placeholder.com/600x400"
                                alt="Incident Proof" class="post-image"></p>
                        <p><strong>Location - </strong> Sample Location</p>
                        <p><strong>Landmark - </strong> Sample Landmark</p>
                        <p><strong>Date and Time - </strong> 2024-11-30 14:30</p>

                        <a href="#" class="btn btn-primary">VIEW PINNED MAP</a>
                    </div>

                    <!-- Card Footer with Date and Time -->
                    <div class="card-footer">
                        <span>Posted:</span>
                        <span>December 01 2024<br>2 mins ago</span>
                    </div>
                </div>
            </div>

        </div>

        <div class="text-center mt-4">
            <a href="#" class="btn btn-primary">View More Posts</a>
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