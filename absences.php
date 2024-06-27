<?php
require 'classes/DB.php';
require 'classes/RedirectUtil.php';
require 'classes/User.php';
require 'classes/UserRoles.php';
require 'classes/Absences.php';
require 'classes/SchoolSubject.php';
require 'classes/TeacherSubjects.php';

session_start();

## Fetch connection to DB
$db_connection = DB::getDB();

## If not logged in or not a teacher/admin, quit
if (!User::checkAuthentication() || (!in_array('teacher', $_SESSION['user_roles']) && !in_array('admin', $_SESSION['user_roles']))) {
    die("You are not authorized to view this page");
}

## Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $operation = $_POST['operation'] ?? 'create';
    $absenceId = $_POST['absence_id'] ?? null;
    $dateOfAbsence = $_POST['date_of_absence'] ?? null;
    $studentId = $_POST['student_id'] ?? null;
    $subjectId = $_POST['subject_id'] ?? null;

    if ($operation === 'create') {
        if (empty($dateOfAbsence) || empty($studentId) || empty($subjectId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            # Add new absence
            Absences::createAbsence($db_connection, $dateOfAbsence, $studentId, $subjectId);
            $_SESSION['success_message'] = "Absence added successfully.";
        }
    } elseif ($operation === 'update') {
        if (empty($dateOfAbsence) || empty($studentId) || empty($subjectId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            # Update existing absence
            Absences::updateAbsence($db_connection, $absenceId, $dateOfAbsence, $studentId, $subjectId);
            $_SESSION['success_message'] = "Absence updated successfully.";
        }
    } elseif ($operation === 'delete') {
        # Delete existing absence
        Absences::deleteAbsence($db_connection, $absenceId);
        $_SESSION['success_message'] = "Absence deleted successfully.";
    }

    RedirectUtil::redirectToPath('/grade-center/absences.php');
    exit;
}

## Fetch logged-in teacher user_id
$teacherId = $_SESSION['user_id'];
if (in_array('admin', $_SESSION['user_roles'])) {
    # If logged-in user is 'admin':
    # fetches all absences
    # joins 'absences' table with 'user' and 'subject' tables to get details of students and subjects
    $absences = Absences::fetchAllAbsences($db_connection);
} else {
    # fetches absences for classes taught by the teacher
    # joins 'absences' table with 'user', 'subject', and 'time_table' tables to get details of students, subjects, and classes
    # filters by teacher's ID
    $absences = Absences::fetchTeacherAbsences($db_connection, $teacherId);
}

## Fetch subjects for teacher or all subjects if admin
if (in_array('admin', $_SESSION['user_roles'])) {
    # If logged-in user is 'admin':
    # fetches all subjects
    $subjects = SchoolSubject::fetchAllSubjects($db_connection);
} else {
    # If logged-in user is 'teacher':
    # fetches subjects taught by the teacher
    # joins 'teacher_subjects' table with 'subject' table to get details of subjects
    # filters by teacher's ID
    $subjects = TeacherSubjects::fetchTeacherSubjects($db_connection, $teacherId);
}


## Fetch students
if (in_array('admin', $_SESSION['user_roles'])) {
    # If logged-in user is 'admin':
    # fetches all students
    # joins 'user' table with 'user_roles' table to filter users by role
    $students = UserRoles::fetchAllStudents($db_connection);
} else {
    # If logged-in user is 'teacher':
    # fetches students from classes taught by the teacher
    # joins 'time_table' table with 'user' table to get details of students
    # joins 'user_roles' and 'role' tables to filter users by role
    # filters by teacher's ID
    $students = UserRoles::fetchTeacherStudents($db_connection, $teacherId);
}

?>

<?php require 'includes/header.php'; ?>
<h2>Manage Student Absences</h2>

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

<form method="post" id="absenceForm">
    <div>
        <input type="radio" id="operation_create" name="operation" value="create" checked>
        <label for="operation_create">Create</label>
        <input type="radio" id="operation_update" name="operation" value="update">
        <label for="operation_update">Update</label>
        <input type="radio" id="operation_delete" name="operation" value="delete">
        <label for="operation_delete">Delete</label>
    </div>

    <div id="existing_absence_select" style="display: none;">
        <label for="absence_id">Select Absence</label>
        <select name="absence_id" id="absence_id">
            <?php foreach ($absences as $absence): ?>
                <option value="<?= $absence['id'] ?>" 
                        data-date="<?= $absence['date_of_absence'] ?>" 
                        data-student="<?= $absence['student_id'] ?>" 
                        data-subject="<?= $absence['subject_id'] ?>">
                    <?= $absence['date_of_absence'] ?> - <?= $absence['full_name'] ?> (<?= $absence['subject_name'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="date_of_absence">Date of Absence</label>
        <input type="date" name="date_of_absence" id="date_of_absence">
    </div>

    <div>
        <label for="student_id">Student</label>
        <select name="student_id" id="student_id">
            <?php foreach ($students as $student): ?>
                <option value="<?= $student['id'] ?>"><?= $student['full_name'] ?></option>
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

<script src="/grade-center/js-scripts/absences.js"></script>

<?php require 'includes/footer.php'; ?>
