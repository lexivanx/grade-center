<?php
require '../classes/DB.php';

if (isset($_GET['school_id'])) {
    $schoolId = $_GET['school_id'];

    $db_connection = DB::getDB();
    $stmt = $db_connection->prepare("SELECT * FROM school WHERE id = ?");
    $stmt->bind_param('i', $schoolId);
    $stmt->execute();
    $result = $stmt->get_result();
    $school = $result->fetch_assoc();

    echo json_encode($school);
}
?>
