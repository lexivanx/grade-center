<?php
require 'classes/DB.php';
require 'classes/RedirectUtil.php';
require 'classes/User.php';
require 'classes/UserRoles.php';
require 'classes/Grade.php';
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
    $gradeId = $_POST['grade_id'] ?? null;
    $grade = $_POST['grade'] ?? null;
    $studentId = $_POST['student_id'] ?? null;
    $subjectId = $_POST['subject_id'] ?? null;

    if ($operation === 'create') {
        if (empty($grade) || empty($studentId) || empty($subjectId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            # Add new grade
            Grade::addGrade($db_connection, $grade, $studentId, $subjectId);
            $_SESSION['success_message'] = "Grade added successfully.";
        }
    } elseif ($operation === 'update') {
        if (empty($grade) || empty($studentId) || empty($subjectId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            # Update existing grade
            Grade::updateGrade($db_connection, $grade, $studentId, $subjectId, $gradeId);
            $_SESSION['success_message'] = "Grade updated successfully.";
        }
    } elseif ($operation === 'delete') {
        # Delete existing grade
        Grade::deleteGrade($db_connection, $gradeId);
        $_SESSION['success_message'] = "Grade deleted successfully.";
    }

    RedirectUtil::redirectToPath('/grade-center/teacher.php');
    exit;
}

## Fetch logged-in teacher user_id
$teacherId = $_SESSION['user_id'];

## Fetch grades
if (in_array('admin', $_SESSION['user_roles'])) {
    # If logged-in user is 'admin':
    # fetches all grades
    $grades = Grade::getAllGrades($db_connection);
} else {
    # If logged-in user is 'teacher':
    # fetches grades for students taught by teacher
    $grades = Grade::getTeacherGrades($db_connection, $teacherId);
}

## Fetch subjects
if (in_array('admin', $_SESSION['user_roles'])) {
    # If logged-in user is 'admin':
    # fetches all subjects
    $subjects = SchoolSubject::fetchAllSubjects($db_connection);
} else {
    # If logged-in user is 'teacher':
    # fetches subjects taught by teacher
    $subjects = TeacherSubjects::fetchTeacherSubjects($db_connection, $teacherId);
}

## Fetch students
if (in_array('admin', $_SESSION['user_roles'])) {
    # If logged-in user is 'admin':
    # fetches all students
    $students = UserRoles::fetchAllStudents($db_connection);
} else {
    # If logged-in user is 'teacher':
    # fetches students from classes taught by teacher
    $students = UserRoles::fetchTeacherStudents($db_connection, $teacherId);
}

?>

<?php require 'includes/header.php'; ?>
<h2>Teacher Panel</h2>

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

<form method="post" id="gradeForm">
    <div>
        <input type="radio" id="operation_create" name="operation" value="create" checked>
        <label for="operation_create">Create</label>
        <input type="radio" id="operation_update" name="operation" value="update">
        <label for="operation_update">Update</label>
        <input type="radio" id="operation_delete" name="operation" value="delete">
        <label for="operation_delete">Delete</label>
    </div>

    <div id="existing_grade_select" style="display: none;">
        <label for="grade_id">Select Grade</label>
        <select name="grade_id" id="grade_id">
            <?php foreach ($grades as $grade): ?>
                <option value="<?= $grade['id'] ?>" 
                        data-grade="<?= $grade['grade'] ?>" 
                        data-student="<?= $grade['user_id'] ?>" 
                        data-subject="<?= $grade['subject_id'] ?>">
                    <?= $grade['grade'] ?> - <?= $grade['full_name'] ?> (<?= $grade['subject_name'] ?>)
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="grade">Grade</label>
        <input type="number" step="0.01" name="grade" id="grade" min="2.00" max="6.00">
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

<script src="/grade-center/js-scripts/teacher.js"></script>

<?php require 'includes/footer.php'; ?>
