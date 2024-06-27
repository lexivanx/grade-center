<?php
require '../classes/DB.php';
require '../classes/User.php';

session_start();

## Check if user is logged in and has admin role
if (!User::checkAuthentication() || !in_array('admin', $_SESSION['user_roles'])) {
    # If not, return a 403 Forbidden response
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$db = DB::getDB();
$schoolsResult = $db->query("SELECT id, name FROM school");

$schools = [];
while ($row = $schoolsResult->fetch_assoc()) {
    $schools[] = $row;
}

echo json_encode($schools);
?>
