<?php
include('../database/connection.php');

$year = $_GET['year'];
$incidentType = $_GET['incidentType'];

$validIncidentTypes = ['Fire', 'Flood', 'Earthquake', 'Accident', 'Theft'];

if (!in_array($incidentType, $validIncidentTypes)) {
    echo json_encode(['error' => 'Invalid incident type']);
    exit;
}

$query = "SELECT MONTH(created_at) AS month, COUNT(*) AS total FROM tbl_reports 
          WHERE incident_type = :incidentType AND YEAR(created_at) = :year
          GROUP BY MONTH(created_at) ORDER BY MONTH(created_at)";

$stmt = $conn->prepare($query);
$stmt->execute(['incidentType' => $incidentType, 'year' => $year]);

$monthlyData = array_fill(0, 12, 0);
$labels = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];

while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
    $monthlyData[$row['month'] - 1] = $row['total'];
}

echo json_encode([
    'labels' => $labels,
    'data' => $monthlyData
]);
