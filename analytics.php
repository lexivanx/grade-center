<?php
require 'classes/DB.php';
require 'classes/RedirectUtil.php';
require 'classes/User.php';
require 'classes/UserRoles.php';
require 'classes/School.php';
require 'classes/Analytics.php';

session_start();

## Fetch connection to DB
$db_connection = DB::getDB();

## If not logged in or not an admin or director, quit
if (!User::checkAuthentication() || (!in_array('admin', $_SESSION['user_roles']) && !in_array('director', $_SESSION['user_roles']))) {
    die("You are not authorized to view this page");
}

$userId = $_SESSION['user_id'];
$userRoles = $_SESSION['user_roles'];

## Initialize the SQL query and parameters
$teacherAveragesSql = "";
$subjectAveragesSql = "";
$teacherAveragesParams = [];
$subjectAveragesParams = [];
$schoolId = null;

if (in_array('director', $userRoles)) {
    # Fetch the director's school ID
    $schoolId = School::getDirectorSchoolId($db_connection, $userId);
}

## Fetch teacher averages
$teacherAveragesData = Analytics::getTeacherAverages($db_connection, $userRoles, $schoolId);

## Fetch subject averages
$subjectAveragesData = Analytics::getSubjectAverages($db_connection, $userRoles, $schoolId);

?>

<?php require 'includes/header.php'; ?>
<h2>School Analytics</h2>

<h3>Average Grades per Teacher</h3>
<table class="analytics-table">
    <thead>
        <tr>
            <th>School</th>
            <th>Teacher</th>
            <th>Average Grade</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($teacherAveragesData as $data): ?>
            <tr>
                <td><?= $data['school_name'] ?></td>
                <td><?= $data['teacher_name'] ?></td>
                <td><?= number_format($data['average_grade'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h3>Average Grades per Subject</h3>
<table class="analytics-table">
    <thead>
        <tr>
            <th>School</th>
            <th>Subject</th>
            <th>Average Grade</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($subjectAveragesData as $data): ?>
            <tr>
                <td><?= $data['school_name'] ?></td>
                <td><?= $data['subject_name'] ?></td>
                <td><?= number_format($data['average_grade'], 2) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require 'includes/footer.php'; ?>
