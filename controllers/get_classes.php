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

$schoolId = $_GET['school_id'] ?? 0;
$db = DB::getDB();
$classesResult = $db->query("SELECT id, grade, letter FROM class WHERE school_id = $schoolId");

$classes = [];
while ($row = $classesResult->fetch_assoc()) {
    $classes[] = $row;
}

echo json_encode($classes);
?>
