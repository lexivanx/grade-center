<?php
require 'classes/DB.php';
require 'classes/Grade.php';
require 'classes/User.php';

session_start();

$db_connection = DB::getDB();

if (User::checkAuthentication()) {
    $results = false;

    if (in_array('student', $_SESSION['user_roles'])) {
        # Students see their own grades
        # fetches grades for the logged-in student
        # joins 'grade' with 'subject' and 'user' tables to get details of subjects and students
        $results = Grade::getStudentGrades($db_connection);
    } elseif (in_array('teacher', $_SESSION['user_roles'])) {
        # Teachers see all grades for students of their classes
        # fetches grades for students taught by the logged-in teacher
        # joins 'grade' with 'subject', 'user', and 'time_table' tables to get details of subjects, students, and classes
        # filters by teacher's ID
        $results = Grade::getAllTeacherGrades($db_connection);
    } elseif (in_array('parent', $_SESSION['user_roles'])) {
        # Parents see all grades for their children
        # fetches grades for children of the logged-in parent
        # joins 'grade' with 'subject', 'user', and 'parents_children' tables to get details of subjects, students, and parent-child relationships
        # filters by parent's ID
        $results = Grade::getParentGrades($db_connection);
    } elseif (in_array('director', $_SESSION['user_roles'])) {
        # Directors see all grades of their own school
        # fetches grades for students in classes within the director's school
        # joins 'grade' with 'subject', 'user', 'class', and 'school' tables to get details of subjects, students, classes, and schools
        # filters by director's ID
        $results = Grade::getDirectorGrades($db_connection);
    }

    if ($results !== false) {
        $grades = mysqli_fetch_all($results, MYSQLI_ASSOC);
    }
}

?>

<?php require 'includes/header.php'; ?>

<div class="logged-in-info">
<?php if (User::checkAuthentication()): ?>
    <p> Currently logged in as: <strong> <?php echo $_SESSION['username']; ?> </strong> </p>
    <a href="logout.php">Logout</a>
    <a href="view-time-table.php">View time table</a><br>
    <a href="view-absences.php">View student absences</a><br>
<?php else: ?>
    <p> No user logged in </p>
    <a href="login.php">Login</a>
<?php endif; ?>
<?php if (User::checkAuthentication() && in_array('admin', $_SESSION['user_roles'])): ?>
    <a href="controllers/crud-user.php">Create/update user</a><br>
    <a href="controllers/crud-school.php">Create/update school</a><br>
    <a href="controllers/director/subjects.php">Manage available subjects</a><br>
    <a href="controllers/director/teacher-subjects.php">Assign subjects to teachers</a><br>
<?php endif; ?>
<?php if (User::checkAuthentication() && in_array('teacher', $_SESSION['user_roles'])): ?>
    <a href="teacher.php">Teacher panel</a><br>
    <a href="absences.php">Manage student absences</a><br>
<?php endif; ?>
<?php if (User::checkAuthentication() && in_array('director', $_SESSION['user_roles'])): ?>
    <a href="controllers/director/parents-children.php">Parents & children</a><br>
    <a href="controllers/director/school-class.php">Manage classes</a><br>
    <a href="controllers/director/time-tables.php">Manage time tables</a><br>
    <a href="analytics.php">Analytics</a><br>
<?php endif; ?>
</div>

<?php if (!User::checkAuthentication()): ?>
    <p>Please, log in to view and manage grades!</p>
<?php elseif (in_array('admin', $_SESSION['user_roles'])): ?>
    <br>
<?php elseif (empty($grades)): ?>
    <p>No grades found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($grades as $grade): ?>
            <li>
                <grade>
                    <h3>Grade ID: <?= htmlspecialchars($grade['id'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p>Grade: <?= htmlspecialchars($grade['grade'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p>Subject: <?= htmlspecialchars($grade['subject_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p>Student: <?= htmlspecialchars($grade['student_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                </grade>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
