<?php

session_start();
header('Content-Type: application/json');

//print_r($_SESSION);
$servername = "localhost";
$username = "localhost";
$password = "NAVneet345@";
$dbname = "myForm";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {

    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'DB connection failed']);
    //echo "Connection failed: " . $conn->connect_error;
    exit;
}

//Ensure user is logged in
$userEmail=$_SESSION['email']??'';

if(!$userEmail)
{   http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    // echo "Unauthorized";
    exit();
}

// if user is logged in we will now get the event id 
$eventId=$_POST['event_id'] ?? '';
$eventId=(int)$eventId;    //to make sure the event id is int

// now check if event id exist or not
if(!$eventId)
{  
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Missing data']);
    //echo "invalid data or missing ID";
    exit();
}

// to protect the id 
$safeEmail = $conn->real_escape_string($userEmail);

//delete event only if it belongs to logged in user
$deleteEventQuery="DELETE FROM events WHERE id=$eventId AND user_email='$safeEmail'";

if ($conn->query($deleteEventQuery) === TRUE) {
    echo json_encode(['success' => true, 'message' => 'Event deleted']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $conn->error]);
}
$conn->close();

?>