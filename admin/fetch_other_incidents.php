<?php
include('../database/connection.php');

$year = $_GET['year'];

$validIncidentTypes = ['Fire', 'Flood', 'Earthquake', 'Accident', 'Theft'];
$query = "SELECT MONTH(created_at) AS month, COUNT(*) AS total FROM tbl_reports 
          WHERE incident_type NOT IN ('Fire', 'Flood', 'Earthquake', 'Accident', 'Theft') 
          AND YEAR(created_at) = :year
          GROUP BY MONTH(created_at) ORDER BY MONTH(created_at)";

$stmt = $conn->prepare($query);
$stmt->execute(['year' => $year]);

$monthlyData = array_fill(0, 12, 0);
$labels = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $monthlyData[$row['month'] - 1] = $row['total'];
}

echo json_encode([
    'labels' => $labels,
    'data' => $monthlyData
]);
