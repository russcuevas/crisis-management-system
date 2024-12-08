<?php
session_start();
include('database/connection.php');

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

$is_logged_in = isset($_SESSION['user_id']);
$user_id = $_SESSION['user_id'];

$query = "SELECT * FROM tbl_incidents WHERE user_id = :user_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':user_id', $user_id, PDO::PARAM_INT);
$stmt->execute();
$incidents = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>History - Crisis Management System</title>

    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- DataTables CSS -->
    <link href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css" rel="stylesheet">

    <!-- DataTables Responsive CSS -->
    <link href="https://cdn.datatables.net/responsive/2.4.0/css/responsive.dataTables.min.css" rel="stylesheet">

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #000000, #3c0f12);
            margin: 0;
            padding: 0;
            color: #333333;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
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

        .nav-link.active {
            color: black !important;
            font-weight: 900;
        }

        .main-content {
            padding: 30px;
            flex: 1;
        }

        h2 {
            font-size: 2.5rem;
            font-weight: 600;
            color: whitesmoke;
            margin-bottom: 30px;
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

        .table-container {
            background-color: #fff;
            border: 1px solid black;
            padding: 50px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
            border-radius: 10px;
        }

        .action-buttons .btn {
            margin-right: 5px;
        }

        .nav-item .dropdown-item.active {
            background-color: #bc1823 !important;
            color: #fff !important;
        }

        .nav-item .dropdown-item:hover {
            background-color: #bc1823;
            color: #fff;
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
                        <a class="nav-link active dropdown-toggle" href="#" id="reportsDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Reports
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="reportsDropdown">
                            <li><a class="dropdown-item" href="reports.php">Post Complain</a></li>
                            <li><a class="dropdown-item active" href="history.php">View History</a></li>

                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button"
                            data-bs-toggle="dropdown" aria-expanded="false">
                            Profile
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="profileDropdown">
                            <?php if ($is_logged_in): ?>
                                <li><a class="dropdown-item" href="profile.php">Change Details</a></li>
                                <li><a class="dropdown-item" href="logout.php">Logout</a></li>
                            <?php else: ?>
                                <li><a class="dropdown-item" href="login.php">Login</a></li>
                                <li><a class="dropdown-item" href="register.php">Register</a></li>
                            <?php endif; ?>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <h2>History of My Reports</h2>

        <div class="row mb-3">
            <div class="col-md-12">
                <button class="btn btn-secondary btn-sm" id="allStatus">All status</button>
                <button class="btn btn-secondary btn-sm" id="filterPending">Pending</button>
                <button class="btn btn-secondary btn-sm" id="filterApproved">Approved</button>
                <!-- <button class="btn btn-secondary btn-sm" id="filterCancelled">Cancelled</button> -->
            </div>
        </div>

        <!-- Report History Table -->
        <div class="table-container">
            <div class="table-responsive">
                <table id="historyTable" class="table table-striped">
                    <thead>
                        <tr>
                            <th scope="col">#</th>
                            <th scope="col">Incident</th>
                            <th scope="col">Location</th>
                            <th scope="col">Status</th>
                            <th scope="col">Date & Time</th>
                            <th scope="col">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $count = 1; ?>
                        <?php foreach ($incidents as $incident): ?>
                            <tr data-status="<?php echo htmlspecialchars($incident['status']); ?>">
                                <th scope="row"><?php echo $count++; ?></th>
                                <td><?php echo htmlspecialchars($incident['incident_type']); ?></td>
                                <td><?php echo htmlspecialchars($incident['incident_location_map']); ?></td>
                                <td class="<?php echo htmlspecialchars($incident['status']) === 'Approved' ? 'text-success' : (htmlspecialchars($incident['status']) === 'Pending' ? 'text-warning' : ''); ?>">
                                    <?php echo htmlspecialchars($incident['status']); ?>
                                </td>
                                <td><?php echo htmlspecialchars($incident['incident_datetime']); ?></td>
                                <td class="action-buttons">
                                    <a href="view_history.php?id=<?php echo $incident['incident_id']; ?>" class="btn btn-warning btn-sm">View Information</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <footer>
        <p>&copy; 2024 Crisis Management System - All Rights Reserved | <a href="#">Privacy Policy</a> | <a href="#">Terms</a></p>
    </footer>


    <!-- Bootstrap 5 JS and dependencies -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-YvpcrYf0tY3lHB60NNkmXc5s9fDVZLESaAA55NDzOxhy9GkcIdslK1eN7N6jIeHz"
        crossorigin="anonymous"></script>

    <!-- jQuery (required by DataTables) -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- DataTables JS -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.4.0/js/dataTables.responsive.min.js"></script>

    <!-- SWEETALERT UPDATE PROFILE -->
    <?php if (isset($_SESSION['success'])): ?>
        <script>
            Swal.fire({
                icon: 'success',
                title: 'Success!',
                text: '<?php echo $_SESSION['success']; ?>'
            }).then(() => {
                window.location.href = 'history.php';
            });
        </script>
        <?php unset($_SESSION['success']); ?>
    <?php elseif (isset($_SESSION['error'])): ?>
        <script>
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: '<?php echo $_SESSION['error']; ?>'
            }).then(() => {
                window.location.href = 'history.php';
            });
        </script>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>

    <script>
        $(document).ready(function() {
            $('#historyTable').DataTable({
                responsive: true
            });

            $('#filterPending').on('click', function() {
                filterTable('Pending');
            });

            $('#filterApproved').on('click', function() {
                filterTable('Approved');
            });

            // $('#filterCancelled').on('click', function() {
            //     filterTable('Cancelled');
            // });

            $('#allStatus').on('click', function() {
                filterTable('');
            });

            function filterTable(status) {
                $('#historyTable tbody tr').each(function() {
                    const rowStatus = $(this).data('status');
                    if (status === '' || rowStatus === status) {
                        $(this).show();
                    } else {
                        $(this).hide();
                    }
                });
            }
        });
    </script>

</body>

</html>