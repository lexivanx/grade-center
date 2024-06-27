<?php

session_start();

require '../includes/db.php';
require '../classes/Grade.php';
require '../classes/User.php';
require '../includes/authentication.php';

if (!checkAuthentication()) {
    die("You don't have permission to view this page");
}

$db_connection = getDB();

if (!isset($_GET['id'])) {
    die("ID not specified, no grade found");
}

$grade = Grade::getGrade($db_connection, $_GET['id'], 'id, student_id, course_id, grade, date_recorded');

if ($grade) {
    $gradeId = $grade['id'];
    $studentId = $grade['student_id'];
    $courseId = $grade['course_id'];
    $gradeValue = $grade['grade'];
    $dateRecorded = $grade['date_recorded'];

    $student = User::getUser($db_connection, $studentId, 'full_name');
    $studentFullName = $student['full_name'];

    $course = Grade::getGrade($db_connection, $courseId, 'course_name');
    $courseName = $course['course_name'];

    if ($_SESSION['user_role'] != "admin" && $_SESSION['user_role'] != "teacher") {
        die("You don't have permission to view or edit this grade");
    }
} else {
    die("No grade found");
}

?>

<?php require '../includes/header.php'; ?>

<h4>Grade Information</h4>

<?php if (!empty($errors)): ?>
    <ul>
        <?php foreach ($errors as $error) { ?>
            <li class="error-message"><?= $error; ?></li>
        <?php } ?>
    </ul>
<?php endif; ?>

<form class="grade-details" method="post">
    <div>
        <p>Grade ID: <?= htmlspecialchars($gradeId, ENT_QUOTES, 'UTF-8'); ?></p>
    </div>
    <div>
        <label for="student_full_name">Student Name:</label>
        <input type="text" name="student_full_name" id="student_full_name" value="<?= htmlspecialchars($studentFullName, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    <div>
        <label for="course_name">Course Name:</label>
        <input type="text" name="course_name" id="course_name" value="<?= htmlspecialchars($courseName, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    <div>
        <label for="grade">Grade:</label>
        <input type="text" name="grade" id="grade" value="<?= htmlspecialchars($gradeValue, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    <div>
        <label for="date_recorded">Date Recorded:</label>
        <input type="datetime-local" name="date_recorded" id="date_recorded" value="<?= htmlspecialchars($dateRecorded, ENT_QUOTES, 'UTF-8'); ?>" required>
    </div>
    <button type="submit">Submit</button>
    <a class="cancel-link" href="/grade-center/views/grade.php?id=<?= htmlspecialchars($gradeId, ENT_QUOTES, 'UTF-8') ?>">Cancel</a>
</form>

<?php require '../includes/footer.php'; ?>