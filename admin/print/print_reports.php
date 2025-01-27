<?php
include '../../database/connection.php';

$month = isset($_GET['month']) ? $_GET['month'] : '';
$year = isset($_GET['year']) ? $_GET['year'] : '';

$sql = "SELECT tbl_reports.*, tbl_users.fullname 
        FROM tbl_reports 
        LEFT JOIN tbl_users ON tbl_reports.user_id = tbl_users.id";

if ($month && $year) {
    $sql .= " WHERE MONTH(tbl_reports.incident_datetime) = :month AND YEAR(tbl_reports.incident_datetime) = :year";
}

$stmt = $conn->prepare($sql);
if ($month && $year) {
    $stmt->bindParam(':month', $month, PDO::PARAM_INT);
    $stmt->bindParam(':year', $year, PDO::PARAM_INT);
}

$stmt->execute();
$complaints = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Print Reports</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            color: #333;
            background-color: #f4f4f4;
        }

        .container {
            max-width: 800px;
            margin: 20px auto;
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .header {
            text-align: center;
            margin-bottom: 20px;
        }

        .header img {
            width: 50px;
            height: 50px;
            margin-bottom: 10px;
        }

        .header h1 {
            font-size: 24px;
            font-weight: 900;
            color: #bc1823;
            margin: 0;
        }

        .report-title {
            font-size: 18px;
            color: #333;
            margin: 10px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }

        th,
        td {
            padding: 12px;
            border: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #bc1823;
            color: #fff;
            font-size: 14px;
        }

        td {
            font-size: 14px;
        }

        @media print {
            body {
                background-color: #fff;
                margin: 0;
                padding: 0;
            }

            .container {
                width: 100%;
                padding: 10px;
                box-shadow: none;
            }

            .header img {
                width: 30px;
                height: 30px;
            }

            .header h1 {
                font-size: 20px;
            }

            table {
                margin-top: 15px;
            }

            .report-title {
                font-size: 16px;
            }
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="header">
            <img src="../images/admin/crisis.jpg" alt="Logo">
            <h1>Crisis Management System</h1>
        </div>

        <table>
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
                        <td><?php echo htmlspecialchars($complaint['fullname']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['incident_type']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['incident_description']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['incident_location_map']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['incident_landmark']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['incident_datetime']); ?></td>
                        <td><?php echo htmlspecialchars($complaint['status']); ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <script>
        window.onload = function() {
            window.print();
        };
    </script>
</body>

</html>