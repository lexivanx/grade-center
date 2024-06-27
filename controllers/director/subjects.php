<?php
require '../../classes/DB.php';
require '../../classes/RedirectUtil.php';
require '../../classes/User.php';
require '../../classes/UserRoles.php';
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
    $subjectId = $_POST['subject_id'] ?? null;
    $name = $_POST['name'] ?? null;

    if ($operation === 'create') {
        if (empty($name)) {
            $_SESSION['error_message'] = "Name field is required.";
        } else {
            ## Add new subject
            SchoolSubject::createSubject($db_connection, $name);
            $_SESSION['success_message'] = "Subject added successfully.";
        }
    } elseif ($operation === 'update') {
        if (empty($name)) {
            $_SESSION['error_message'] = "Name field is required.";
        } else {
            ## Update existing subject
            SchoolSubject::updateSubject($db_connection, $subjectId, $name);
            $_SESSION['success_message'] = "Subject updated successfully.";
        }
    } elseif ($operation === 'delete') {
        ## Delete existing subject
        SchoolSubject::deleteSubject($db_connection, $subjectId);
        $_SESSION['success_message'] = "Subject deleted successfully.";
    }

    RedirectUtil::redirectToPath('/grade-center/controllers/director/subjects.php');
    exit;
}

## Fetch all subjects
$subjects = SchoolSubject::fetchAllSubjects($db_connection);

?>

<?php require '../../includes/header.php'; ?>
<h2>Manage Subjects</h2>

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

<form method="post" id="subjectForm">
    <div>
        <input type="radio" id="operation_create" name="operation" value="create" checked>
        <label for="operation_create">Create</label>
        <input type="radio" id="operation_update" name="operation" value="update">
        <label for="operation_update">Update</label>
        <input type="radio" id="operation_delete" name="operation" value="delete">
        <label for="operation_delete">Delete</label>
    </div>

    <div id="existing_subject_select" style="display: none;">
        <label for="subject_id">Select Subject</label>
        <select name="subject_id" id="subject_id">
            <?php foreach ($subjects as $subject): ?>
                <option value="<?= $subject['id'] ?>" data-name="<?= $subject['name'] ?>">
                    <?= $subject['name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="name">Name</label>
        <input type="text" name="name" id="name">
    </div>

    <button type="submit">Submit</button>
</form>

<script src="/grade-center/js-scripts/subjects.js"></script>

<?php require '../../includes/footer.php'; ?>
