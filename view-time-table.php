<?php
require 'classes/DB.php';
require 'classes/RedirectUtil.php';
require 'classes/User.php';
require 'classes/UserRoles.php';
require 'classes/TimeTable.php';

session_start();

## Fetch connection to DB
$db_connection = DB::getDB();

## If not logged in, quit
if (!User::checkAuthentication()) {
    die("You are not authorized to view this page");
}

$userId = $_SESSION['user_id'];
$userRoles = $_SESSION['user_roles'];

if (in_array('admin', $userRoles)) {
    # Admins see all entries
    $timeTableEntries = TimeTable::fetchAllTimeTables($db_connection);
} elseif (in_array('director', $userRoles)) {
    # Directors see entries for their school's classes
    $timeTableEntries = TimeTable::fetchDirectorTimeTable($db_connection, $userId);
} elseif (in_array('teacher', $userRoles)) {
    # Teachers see entries where their id is teacher_id
    $timeTableEntries = TimeTable::fetchTeacherTimeTable($db_connection, $userId);
} elseif (in_array('parent', $userRoles)) {
    # Parents see entries for their children's classes
    $timeTableEntries = TimeTable::fetchParentTimeTable($db_connection, $userId);
} elseif (in_array('student', $userRoles)) {
    # Students see entries for their own class
    $timeTableEntries = TimeTable::fetchStudentTimeTable($db_connection, $userId);
}

?>

<?php require 'includes/header.php'; ?>
<h2>View Time Table</h2>

<table class="time-table">
    <thead>
        <tr>
            <th>Day</th>
            <th>Start Time</th>
            <th>End Time</th>
            <th>Semester</th>
            <th>Teacher</th>
            <th>Class</th>
            <th>Subject</th>
            <?php if (in_array('admin', $userRoles) || in_array('teacher', $userRoles) || in_array('parent', $userRoles)): ?>
                <th>School</th>
            <?php endif; ?>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($timeTableEntries as $entry): ?>
            <tr>
                <td><?= ucfirst($entry['day_week']) ?></td>
                <td><?= $entry['time_start'] ?></td>
                <td><?= $entry['time_end'] ?></td>
                <td><?= $entry['semester'] ?></td>
                <td><?= $entry['teacher_name'] ?></td>
                <td><?= $entry['grade'] . $entry['letter'] ?></td>
                <td><?= $entry['subject_name'] ?></td>
                <?php if (in_array('admin', $userRoles) || in_array('teacher', $userRoles) || in_array('parent', $userRoles)): ?>
                    <td><?= $entry['school_name'] ?></td>
                <?php endif; ?>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require 'includes/footer.php'; ?>
