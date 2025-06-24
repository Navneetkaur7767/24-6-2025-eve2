<?php
session_start();
header('Content-Type: application/json');

$servername = "localhost";
$username = "localhost";
$password = "NAVneet345@";
$dbname = "myForm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    exit;
}

$userEmail = $_SESSION['email'] ?? '';
if (!$userEmail) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

$title = $conn->real_escape_string($_POST['event_title'] ?? '');
$start = $conn->real_escape_string($_POST['start_date'] ?? '');
$end = $conn->real_escape_string($_POST['end_date'] ?? '');

if (!$title || !$start || !$end) {
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    exit;
}

$sql = "INSERT INTO events (event_title, startdate, enddate, adddate, user_email )
        VALUES ('$title', '$start', '$end',NOW(), '$userEmail')";

if ($conn->query($sql) === TRUE) {
    echo json_encode([
        'success' => true,
        'event_id' => $conn->insert_id,
        'event_title' => $title,
        'start_date' => $start,
        'end_date' => $end
    ]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
?>