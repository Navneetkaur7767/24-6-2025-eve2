<?php
session_start();
header('Content-Type : application/json');

$servername = "localhost";
$username = "locslhost";
$password = "NAVneet345@";
$dbname = "myForm";

$conn = new mysqli($servername ,$username, $password, $dbname);
if ($conn->connect_error) {
	http_response_code(500);
	echo json_encode(['success' => false ,'message' => 'DB connection failed']);
	exit;
}

$userEmail = $_SESSION['email']??'';
$eventId = (int)($_POST['event_id'] ?? 0);
$newStart = $_POST['new_start'] ?? '';
$newEnd = $_POST['new_end'] ?? '';

if (!$eventId || !$newStart || !$newEnd)
{
	echo json_encode(['success' => false ,'message' => 'MIssing input']);
	exit;
}

$safeEmail = $conn->real_escape_string($userEmail);
$newStart = $conn->real_escape_string($newStart);
$newEnd = $conn->real_escape_string($newEnd);

$query = "UPDATE events SET startdate='$newStart' ,enddate='$newEnd' ,editdate= NOW()
	WHERE id=$eventID AND user_email='$safeEmail'";
	
if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'message' => $conn->error]);
}
