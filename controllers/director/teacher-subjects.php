<?php
require '../../classes/DB.php';
require '../../classes/RedirectUtil.php';
require '../../classes/User.php';
require '../../classes/UserRoles.php';
require '../../classes/TeacherSubjects.php';
require '../../classes/SchoolSubject.php';

session_start();

## Fetch connection to DB
$db_connection = DB::getDB();

if (!User::checkAuthentication() || !in_array('admin', $_SESSION['user_roles'])) {
    RedirectUtil::redirectToPath('/grade-center/index.php');
    exit;
}

## Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $operation = $_POST['operation'] ?? 'create';
    $teacherSubjectId = $_POST['teacher_subject_id'] ?? null;
    $teacherId = $_POST['teacher_id'] ?? null;
    $subjectId = $_POST['subject_id'] ?? null;

    if ($operation === 'create') {
        if (empty($teacherId) || empty($subjectId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            ## Add new teacher-subject relationship
            TeacherSubjects::createTeacherSubjectRel($db_connection, $teacherId, $subjectId);
            $_SESSION['success_message'] = "Teacher-subject relationship added successfully.";
        }
    } elseif ($operation === 'update') {
        if (empty($teacherId) || empty($subjectId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            ## Update existing teacher-subject relationship
            TeacherSubjects::updateTeacherSubjectRel($db_connection, $teacherSubjectId, $teacherId, $subjectId);
            $_SESSION['success_message'] = "Teacher-subject relationship updated successfully.";
        }
    } elseif ($operation === 'delete') {
        ## Delete existing teacher-subject relationship
        TeacherSubjects::deleteTeacherSubjectRel($db_connection, $teacherSubjectId);
        $_SESSION['success_message'] = "Teacher-subject relationship deleted successfully.";
    }

    RedirectUtil::redirectToPath('/grade-center/controllers/director/teacher-subjects.php');
    exit;
}

## Fetch teacher-subject relationships
## retrieves relationships between 'teachers' and 'subjects' from the 'teacher_subjects' table
## joins 'user' table to get 'teacher' details and 'subject' table to get subject details
## results are ordered by ID in descending order
$relationships = TeacherSubjects::fetchTeacherSubjectRelationships($db_connection);

## Fetch teachers
## retrieves all users who have role 'teacher'
## joins 'user' table with 'user_roles' table and 'role' table to filter users by role
$teachers = UserRoles::fetchAllTeachers($db_connection);

## Fetch subjects
$subjects = SchoolSubject::fetchAllSubjects($db_connection);

?>

<?php require '../../includes/header.php'; ?>
<h2>Manage Teacher-Subject Relationships</h2>

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

<form method="post" id="teacherSubjectForm">
    <div>
        <input type="radio" id="operation_create" name="operation" value="create" checked>
        <label for="operation_create">Create</label>
        <input type="radio" id="operation_update" name="operation" value="update">
        <label for="operation_update">Update</label>
        <input type="radio" id="operation_delete" name="operation" value="delete">
        <label for="operation_delete">Delete</label>
    </div>

    <div id="existing_relationship_select" style="display: none;">
        <label for="teacher_subject_id">Select Relationship</label>
        <select name="teacher_subject_id" id="teacher_subject_id">
            <?php foreach ($relationships as $relationship): ?>
                <option value="<?= $relationship['id'] ?>" 
                        data-teacher="<?= $relationship['teacher_id'] ?>" 
                        data-subject="<?= $relationship['subject_id'] ?>">
                    <?= $relationship['teacher_name'] ?> - <?= $relationship['subject_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
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
        <label for="subject_id">Subject</label>
        <select name="subject_id" id="subject_id">
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['id'] ?>"><?= $subject['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit">Submit</button>
</form>

<script src="/grade-center/js-scripts/teacher-subjects.js"></script>

<?php require '../../includes/footer.php'; ?>
