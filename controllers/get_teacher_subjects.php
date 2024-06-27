<?php
require '../classes/DB.php';
require '../classes/TeacherSubjects.php';

if (isset($_GET['teacher_id'])) {
    $teacherId = $_GET['teacher_id'];
    $db_connection = DB::getDB();
    $subjects = TeacherSubjects::fetchTeacherSubjects($db_connection, $teacherId);
    echo json_encode(['subjects' => $subjects]);
}
?>
