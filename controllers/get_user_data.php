<?php
require '../classes/DB.php';
require '../classes/User.php';

session_start();

## Check if user is logged in and has admin role
if (!User::checkAuthentication() || !in_array('admin', $_SESSION['user_roles'])) {
    ##If not, return a 403 Forbidden response
    http_response_code(403);
    echo json_encode(['error' => 'Access denied']);
    exit;
}

$userId = $_GET['user_id'] ?? 0;
$db = DB::getDB();

## Fetch user data along with class_id
$userResult = $db->query("SELECT * FROM user WHERE id = $userId");
if ($userResult->num_rows > 0) {
    $user = $userResult->fetch_assoc();
} else {
    echo json_encode([]);
    exit;
}

## Fetch the class and school information
$classId = $user['class_id'];
$schoolId = null;

if ($classId) {
    $classResult = $db->query("SELECT school_id FROM class WHERE id = $classId");
    if ($classResult->num_rows > 0) {
        $class = $classResult->fetch_assoc();
        $schoolId = $class['school_id'];
    }
}

## Fetch user roles
$rolesResult = $db->query("SELECT r.name FROM user_roles ur JOIN role r ON ur.role_id = r.id WHERE ur.user_id = $userId");
$roles = [];
while ($row = $rolesResult->fetch_assoc()) {
    $roles[] = $row['name'];
}

echo json_encode([
    'username' => $user['username'],
    'full_name' => $user['full_name'],
    'age' => $user['age'],
    'class_id' => $user['class_id'],
    'school_id' => $schoolId,
    'roles' => $roles,
]);
?>
