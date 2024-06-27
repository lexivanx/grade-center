<?php
require '../../classes/DB.php';
require '../../classes/RedirectUtil.php';
require '../../classes/User.php';
require '../../classes/UserRoles.php';
require '../../classes/ParentsChildren.php';

session_start();

## Fetch connection to DB
$db_connection = DB::getDB();

## If not logged in or not an admin or director, quit
if (!User::checkAuthentication() || (!in_array('admin', $_SESSION['user_roles']) && !in_array('director', $_SESSION['user_roles']))) {
    die("You are not authorized to view this page");
}

$directorId = $_SESSION['user_id'] ?? null;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $operation = $_POST['operation'] ?? 'create';
    $parentChildId = $_POST['parent_child_id'] ?? null;
    $parentId = $_POST['parent_id'] ?? null;
    $childId = $_POST['child_id'] ?? null;

    if ($operation === 'create') {
        if (empty($parentId) || empty($childId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            ParentsChildren::addParentChildRel($db_connection, $parentId, $childId);
            $_SESSION['success_message'] = "Parent-child relationship added successfully.";
        }
    } elseif ($operation === 'update') {
        if (empty($parentId) || empty($childId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            ParentsChildren::updateParentChildRel($db_connection, $parentChildId, $parentId, $childId);
            $_SESSION['success_message'] = "Parent-child relationship updated successfully.";
        }
    } elseif ($operation === 'delete') {
        ParentsChildren::deleteParentChildRel($db_connection, $parentChildId);
        $_SESSION['success_message'] = "Parent-child relationship deleted successfully.";
    }

    RedirectUtil::redirectToPath('/grade-center/controllers/director/parents-children.php');
    exit;
}

## Fetch all parent-child relationships
## joins 'user' table twice: get parent details and get child details
## ordered by ID in descending order
$relationships = ParentsChildren::fetchParentChildRelationships($db_connection);

## Fetch parents - users with role 'parent'
## joins 'user' table with 'user_roles' table and 'role' table to filter users by their role
$parents = ParentsChildren::fetchParents($db_connection);

## Fetch students based on user role
if (in_array('admin', $_SESSION['user_roles'])) {
    # If logged-in user is 'admin':
    # fetches all users with role 'student'.
    # joins 'user' table with the 'user_roles' table and the 'role' table to filter users by role
    $students = UserRoles::fetchAllStudents($db_connection);
} elseif (in_array('director', $_SESSION['user_roles'])) {
    # If logged-in user is 'director'
    # fetches all students in classes of director's school.
    # joins 'user' table with the 'user_roles' table and the 'role' table to filter users by their role
    # joins 'class' and 'school' tables to filter students by director's school
    $students = UserRoles::fetchStudentsFromSchool($db_connection, $directorId);
}

?>

<?php require '../../includes/header.php'; ?>
<h2>Manage Parent-Child Relationships</h2>

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

<form method="post" id="relationshipForm">
    <div>
        <input type="radio" id="operation_create" name="operation" value="create" checked>
        <label for="operation_create">Create</label>
        <input type="radio" id="operation_update" name="operation" value="update">
        <label for="operation_update">Update</label>
        <input type="radio" id="operation_delete" name="operation" value="delete">
        <label for="operation_delete">Delete</label>
    </div>

    <div id="existing_relationship_select" style="display: none;">
        <label for="parent_child_id">Select Relationship</label>
        <select name="parent_child_id" id="parent_child_id">
            <?php foreach ($relationships as $relationship): ?>
                <option value="<?= $relationship['id'] ?>" 
                        data-parent="<?= $relationship['parent_id'] ?>" 
                        data-child="<?= $relationship['child_id'] ?>">
                    <?= $relationship['parent_name'] ?> - <?= $relationship['child_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="parent_id">Parent</label>
        <select name="parent_id" id="parent_id">
            <?php foreach ($parents as $parent): ?>
                <option value="<?= $parent['id'] ?>"><?= $parent['full_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="child_id">Child</label>
        <select name="child_id" id="child_id">
            <?php foreach ($students as $student): ?>
                <option value="<?= $student['id'] ?>"><?= $student['full_name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <button type="submit">Submit</button>
</form>

<script src="/grade-center/js-scripts/parents-children.js"></script>

<?php require '../../includes/footer.php'; ?>
