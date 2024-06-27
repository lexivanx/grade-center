<?php
require '../../classes/DB.php';
require '../../classes/RedirectUtil.php';
require '../../classes/User.php';
require '../../classes/UserRoles.php';
require '../../classes/TimeTable.php';
require '../../classes/SchoolSubject.php';
require '../../classes/SchoolClass.php';
require '../../classes/TeacherSubjects.php';

session_start();

## Fetch connection to DB
$db_connection = DB::getDB();

## If not logged in or not an admin or director, quit
if (!User::checkAuthentication() || (!in_array('admin', $_SESSION['user_roles']) && !in_array('director', $_SESSION['user_roles']))) {
    die("You are not authorized to view this page");
}

$directorId = $_SESSION['user_id'] ?? null;

## Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $operation = $_POST['operation'] ?? 'create';
    $timeTableId = $_POST['time_table_id'] ?? null;
    $dayWeek = $_POST['day_week'] ?? null;
    $timeStart = $_POST['time_start'] ?? null;
    $timeEnd = $_POST['time_end'] ?? null;
    $semester = $_POST['semester'] ?? null;
    $teacherId = $_POST['teacher_id'] ?? null;
    $classId = $_POST['class_id'] ?? null;
    $subjectId = $_POST['subject_id'] ?? null;

    if ($operation === 'create') {
        if (empty($dayWeek) || empty($timeStart) || empty($timeEnd) || empty($semester) || empty($teacherId) || empty($classId) || empty($subjectId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            # Add new time table entry
            TimeTable::createTimeTableEntry($db_connection, $dayWeek, $timeStart, $timeEnd, $semester, $teacherId, $classId, $subjectId);
            $_SESSION['success_message'] = "Time table entry added successfully.";
        }
    } elseif ($operation === 'update') {
        if (empty($dayWeek) || empty($timeStart) || empty($timeEnd) || empty($semester) || empty($teacherId) || empty($classId) || empty($subjectId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            # Update existing time table entry
            TimeTable::updateTimeTableEntry($db_connection, $timeTableId, $dayWeek, $timeStart, $timeEnd, $semester, $teacherId, $classId, $subjectId);
            $_SESSION['success_message'] = "Time table entry updated successfully.";
        }
    } elseif ($operation === 'delete') {
        # Delete existing time table entry
        TimeTable::deleteTimeTableEntry($db_connection, $timeTableId);
        $_SESSION['success_message'] = "Time table entry deleted successfully.";
    }

    RedirectUtil::redirectToPath('/grade-center/controllers/director/time-tables.php');
    exit;
}

## Fetch all time table entries
if (in_array('admin', $_SESSION['user_roles'])) {
    # If logged-in user is 'admin':
    # fetches all entries from 'time_table'
    # joins 'time_table' with 'user', 'class', and 'subject' tables to get details of teacher, class, and subject
    $timeTables = TimeTable::fetchAllTimeTables($db_connection);
} elseif (in_array('director', $_SESSION['user_roles'])) {
    # If logged-in user is 'director':
    # fetches all entries from 'time_table' for classes in director's school
    # joins 'time_table' with 'user', 'class', 'school', and 'subject' tables to get details of teacher, class, and subject
    # filters by director's school
    $timeTables = TimeTable::fetchDirectorTimeTable($db_connection, $directorId);
}

## Fetch teachers
## fetches all users with role 'teacher'
## joins 'user' table with 'user_roles' table and 'role' table to filter users by role
$teachers = UserRoles::fetchAllTeachers($db_connection);

## Fetch classes based on user role
if (in_array('admin', $_SESSION['user_roles'])) {
    # If logged-in user is 'admin':
    # fetches all classes and their respective school names
    # joins 'class' table with 'school' table to get school name for each class 
    $classes = SchoolClass::fetchAllClassesAndNames($db_connection);
} elseif (in_array('director', $_SESSION['user_roles'])) {
    # If logged-in user is 'director':
    # fetches all classes in director's school
    # joins 'class' table with 'school' table to get school name for each class
    # filters classes by director's school
    $classes = SchoolClass::fetchDirectorClassesAndNames($db_connection, $directorId);
}

## Fetch subjects
$subjects = SchoolSubject::fetchAllSubjects($db_connection);

?>

<?php require '../../includes/header.php'; ?>
<h2>Manage Time Table Entries</h2>

<?php if (isset($_SESSION['success_message'])): ?>
    <div class="success-message">
        <?= $_SESSION['success_message'] ?>
    </div>
    <?php unset($_SESSION['success_message']); ?>
<?php endif; ?>

<?php if (isset($_SESSION['error_message'])): ?>
    <div class="error-message">
        <?= $_SESSION['error_message'] ?>
    </div>
    <?php unset($_SESSION['error_message']); ?>
<?php endif; ?>

<form method="post" id="timeTableForm">
    <div>
        <input type="radio" id="operation_create" name="operation" value="create" checked>
        <label for="operation_create">Create</label>
        <input type="radio" id="operation_update" name="operation" value="update">
        <label for="operation_update">Update</label>
        <input type="radio" id="operation_delete" name="operation" value="delete">
        <label for="operation_delete">Delete</label>
    </div>

    <div id="existing_time_table_select" style="display: none;">
        <label for="time_table_id">Select Time Table Entry</label>
        <select name="time_table_id" id="time_table_id">
            <?php foreach ($timeTables as $timeTable): ?>
                <option value="<?= $timeTable['id'] ?>" 
                        data-day="<?= $timeTable['day_week'] ?>" 
                        data-start="<?= $timeTable['time_start'] ?>" 
                        data-end="<?= $timeTable['time_end'] ?>" 
                        data-semester="<?= $timeTable['semester'] ?>" 
                        data-teacher="<?= $timeTable['teacher_id'] ?>" 
                        data-class="<?= $timeTable['class_id'] ?>" 
                        data-subject="<?= $timeTable['subject_id'] ?>">
                    <?= $timeTable['day_week'] ?>: <?= $timeTable['time_start'] ?> - <?= $timeTable['time_end'] ?>, <?= $timeTable['teacher_name'] ?>, <?= $timeTable['grade'].$timeTable['letter'] ?>, <?= $timeTable['subject_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="day_week">Day of the Week</label>
        <select name="day_week" id="day_week">
            <option value="mon">Monday</option>
            <option value="tue">Tuesday</option>
            <option value="wed">Wednesday</option>
            <option value="thr">Thursday</option>
            <option value="fri">Friday</option>
            <option value="sat">Saturday</option>
            <option value="sun">Sunday</option>
        </select>
    </div>

    <div>
        <label for="time_start">Start Time</label>
        <input type="time" name="time_start" id="time_start">
    </div>

    <div>
        <label for="time_end">End Time</label>
        <input type="time" name="time_end" id="time_end">
    </div>

    <div>
        <label for="semester">Semester</label>
        <input type="text" name="semester" id="semester">
    </div>

    <div>
        <label for="teacher_id">Teacher</label>
        <select name="teacher_id" id="teacher_id">
            <?php foreach ($teachers as $teacher): ?>
                <option value="<?= $teacher['id'] ?>"><?= $teacher['full_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="class_id">Class</label>
        <select name="class_id" id="class_id">
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['id'] ?>"><?= $class['class_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="subject_id">Subject</label>
        <select name="subject_id" id="subject_id">
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['id'] ?>"><?= $subject['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit">Submit</button>
</form>

<script src="/grade-center/js-scripts/time-tables.js"></script>

<?php require '../../includes/footer.php'; ?>
