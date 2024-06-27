<?php
require '../../classes/DB.php';
require '../../classes/RedirectUtil.php';
require '../../classes/User.php';
require '../../classes/UserRoles.php';
require '../../classes/SchoolClass.php';
require '../../classes/School.php';

session_start();

## Fetch connection to DB
$db_connection = DB::getDB();

## If not logged in or not an admin or director, quit
if (!User::checkAuthentication() || (!in_array('admin', $_SESSION['user_roles']) && !in_array('director', $_SESSION['user_roles']))) {
    die("You are not authorized to view this page");
}

$directorId = $_SESSION['user_id'] ?? null;
$schoolId = null;

## Fetch school ID for director
if (in_array('admin', $_SESSION['user_roles'])) {
    $schoolId = $_POST['school_id'] ?? null;
} elseif (in_array('director', $_SESSION['user_roles'])) {
    $schoolId = School::getDirectorSchoolId($db_connection, $directorId);
}

## Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $operation = $_POST['operation'] ?? 'create';
    $classId = $_POST['class_id'] ?? null;
    $grade = $_POST['grade'] ?? null;
    $letter = $_POST['letter'] ?? null;

    if ($operation === 'create') {
        if (empty($grade) || empty($letter) || empty($schoolId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            ## Add new class
            SchoolClass::createSchoolClass($db_connection, $grade, $letter, $schoolId);
            $_SESSION['success_message'] = "Class added successfully.";
        }
    } elseif ($operation === 'update') {
        if (empty($grade) || empty($letter) || empty($schoolId)) {
            $_SESSION['error_message'] = "All fields are required.";
        } else {
            ## Update existing class
            SchoolClass::updateSchoolClass($db_connection, $classId, $grade, $letter, $schoolId);
            $_SESSION['success_message'] = "Class updated successfully.";
        }
    } elseif ($operation === 'delete') {
        ## Delete existing class
        SchoolClass::deleteSchoolClass($db_connection, $classId);
        $_SESSION['success_message'] = "Class deleted successfully.";
    }

    RedirectUtil::redirectToPath('/grade-center/controllers/director/school-class.php');
    exit;
}

## Fetch classes based on user role
if (in_array('admin', $_SESSION['user_roles'])) {
    # If logged-in user is 'admin':
    # fetches all classes and their school names
    # joins 'class' table with the 'school' table to get the school name for each class
    $classes = SchoolClass::fetchAllClasses($db_connection);
} elseif (in_array('director', $_SESSION['user_roles'])) {
    # If logged-in user is 'director':
    # fetches all classes in director's school and school names.
    # joins 'class' table with 'school' table to get school name of classes
    # filters classes by director's school
    $classes = SchoolClass::fetchDirectorClasses($db_connection, $directorId);
}

## Fetch schools for admin only
if (in_array('admin', $_SESSION['user_roles'])) {
    $schools = School::getAllSchools($db_connection);
}

?>

<?php require '../../includes/header.php'; ?>
<h2>Manage School Classes</h2>

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

<form method="post" id="classForm">
    <div>
        <input type="radio" id="operation_create" name="operation" value="create" checked>
        <label for="operation_create">Create</label>
        <input type="radio" id="operation_update" name="operation" value="update">
        <label for="operation_update">Update</label>
        <input type="radio" id="operation_delete" name="operation" value="delete">
        <label for="operation_delete">Delete</label>
    </div>

    <div id="existing_class_select" style="display: none;">
        <label for="class_id">Select Class</label>
        <select name="class_id" id="class_id">
            <?php foreach ($classes as $class): ?>
                <option value="<?= $class['id'] ?>" 
                        data-grade="<?= $class['grade'] ?>" 
                        data-letter="<?= $class['letter'] ?>" 
                        data-school="<?= $class['school_id'] ?>">
                    <?= $class['grade'] ?><?= $class['letter'] ?> - <?= $class['school_name'] ?>
                </option>
            <?php endforeach; ?>
        </select>
    </div>

    <div>
        <label for="grade">Grade</label>
        <select name="grade" id="grade">
            <?php for ($i = 1; $i <= 12; $i++): ?>
                <option value="<?= $i ?>"><?= $i ?></option>
            <?php endfor; ?>
        </select>
    </div>

    <div>
        <label for="letter">Letter</label>
        <select name="letter" id="letter">
            <option value="A">A</option>
            <option value="B">B</option>
            <option value="V">V</option>
            <option value="G">G</option>
            <option value="D">D</option>
        </select>
    </div>

    <?php if (in_array('admin', $_SESSION['user_roles'])): ?>
        <div>
            <label for="school_id">School</label>
            <select name="school_id" id="school_id">
                <?php foreach ($schools as $school): ?>
                    <option value="<?= $school['id'] ?>"><?= $school['name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    <?php endif; ?>

    <button type="submit">Submit</button>
</form>

<script>
    document.querySelectorAll('input[name="operation"]').forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'create') {
                document.getElementById('existing_class_select').style.display = 'none';
                document.getElementById('grade').disabled = false;
                document.getElementById('letter').disabled = false;
                <?php if (in_array('admin', $_SESSION['user_roles'])): ?>
                    document.getElementById('school_id').disabled = false;
                <?php endif; ?>
                clearFormFields();
            } else {
                document.getElementById('existing_class_select').style.display = 'block';
                if (this.value === 'delete') {
                    document.getElementById('grade').disabled = true;
                    document.getElementById('letter').disabled = true;
                    <?php if (in_array('admin', $_SESSION['user_roles'])): ?>
                        document.getElementById('school_id').disabled = true;
                    <?php endif; ?>
                } else {
                    document.getElementById('grade').disabled = false;
                    document.getElementById('letter').disabled = false;
                    <?php if (in_array('admin', $_SESSION['user_roles'])): ?>
                        document.getElementById('school_id').disabled = false;
                    <?php endif; ?>
                }
            }
        });
    });

    document.getElementById('class_id').addEventListener('change', function() {
        const selectedOption = this.options[this.selectedIndex];
        document.getElementById('grade').value = selectedOption.getAttribute('data-grade');
        document.getElementById('letter').value = selectedOption.getAttribute('data-letter');
        <?php if (in_array('admin', $_SESSION['user_roles'])): ?>
            document.getElementById('school_id').value = selectedOption.getAttribute('data-school');
        <?php endif; ?>
    });

    function clearFormFields() {
        document.getElementById('grade').value = '';
        document.getElementById('letter').value = '';
        <?php if (in_array('admin', $_SESSION['user_roles'])): ?>
            document.getElementById('school_id').value = '';
        <?php endif; ?>
        document.getElementById('class_id').selectedIndex = -1;
    }
</script>

<?php require '../../includes/footer.php'; ?>
