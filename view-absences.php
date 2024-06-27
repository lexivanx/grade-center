<?php
require 'classes/DB.php';
require 'classes/User.php';
require 'classes/Absences.php';

session_start();

$db_connection = DB::getDB();

if (User::checkAuthentication()) {

    if (in_array('admin', $_SESSION['user_roles'])) {
        # Admins see all absences regardless of school, student, etc
        $absences = Absences::fetchAllAbsences($db_connection);
    } elseif (in_array('director', $_SESSION['user_roles'])) {
        # Directors see all absences of their own school
        $absences = Absences::fetchDirectorAbsences($db_connection, $_SESSION['user_id']);
    } elseif (in_array('teacher', $_SESSION['user_roles'])) {
        # Teachers see all absences for students of their classes
        $absences = Absences::fetchTeacherAbsences($db_connection, $_SESSION['user_id']);
    } elseif (in_array('parent', $_SESSION['user_roles'])) {
        # Parents see all absences for their children
        $absences = Absences::fetchParentAbsences($db_connection, $_SESSION['user_id']);
    } elseif (in_array('student', $_SESSION['user_roles'])) {
        # Students see their own absences
        $absences = Absences::fetchStudentAbsences($db_connection, $_SESSION['user_id']);
    }

}

?>

<?php require 'includes/header.php'; ?>

<?php if (empty($absences)): ?>
    <p>No absences found.</p>
<?php else: ?>
    <ul>
        <?php foreach ($absences as $absence): ?>
            <li>
                <absence>
                    <h3>Absence ID: <?= htmlspecialchars($absence['id'], ENT_QUOTES, 'UTF-8'); ?></h3>
                    <p>Date of Absence: <?= htmlspecialchars($absence['date_of_absence'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p>Subject: <?= htmlspecialchars($absence['subject_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                    <p>Student: <?= htmlspecialchars($absence['full_name'], ENT_QUOTES, 'UTF-8'); ?></p>
                </absence>
            </li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>

<?php require 'includes/footer.php'; ?>
