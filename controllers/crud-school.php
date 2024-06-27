<?php
require '../classes/DB.php';
require '../classes/User.php';
require '../classes/School.php';
require '../classes/SchoolClass.php';
require '../classes/RedirectUtil.php';

session_start();

## Fetch connection to DB
$db_connection = DB::getDB();

## If not logged in or not an admin, quit
if (!User::checkAuthentication() || !in_array('admin', $_SESSION['user_roles'])) {
    die("You are not authorized to view this page");
}

## Form for adding/updating/deleting schools
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    $schoolId = $_POST['school_id'] ?? null;
    $name = $_POST['name'] ?? null;
    $country = $_POST['country'] ?? null;
    $city = $_POST['city'] ?? null;
    $street = $_POST['street'] ?? null;
    $street_num = $_POST['street_num'] ?? null;
    $director_id = $_POST['director_id'] ?? null;

    if ($action !== 'delete' && (empty($name) || empty($country) || empty($city) || empty($street) || empty($street_num) || empty($director_id))) {
        $_SESSION['error_message'] = "All fields are required.";
    } else {
        if ($action == 'create') {
            # Add new school
            $success = School::createSchool($db_connection, $name, $country, $city, $street, $street_num, $director_id);
            if ($success) {
                $_SESSION['success_message'] = "School added successfully.";
            }
        } elseif ($action == 'update') {
            # Update existing school
            $success = School::updateSchool($db_connection, $schoolId, $name, $country, $city, $street, $street_num, $director_id);
            if ($success) {
                $_SESSION['success_message'] = "School updated successfully.";
            }
        } elseif ($action == 'delete') {
            # Delete existing school
            $success = School::deleteSchool($db_connection, $schoolId);
            if ($success) {
                $_SESSION['success_message'] = "School deleted successfully.";
            }
        }
    }

    RedirectUtil::redirectToPath('/grade-center/controllers/crud-school.php');
    exit;
}

# Fetch directors for dropdown
$directors = User::getDirectors($db_connection);

## Fetch existing schools for update
$schools = School::getAllSchools($db_connection);
?>

<?php require '../includes/header.php'; ?>

<h2>Manage Schools</h2>

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

<form method="post" id="schoolForm">
    <div>
        <input type="radio" id="create_school" name="action" value="create" checked>
        <label for="create_school">Create School</label>
    </div>
    <div>
        <input type="radio" id="update_school" name="action" value="update">
        <label for="update_school">Update School</label>
    </div>
    <div>
        <input type="radio" id="delete_school" name="action" value="delete">
        <label for="delete_school">Delete School</label>
    </div>

    <div id="existing_school_select" style="display: none;">
        <label for="school_id">Select School</label>
        <select name="school_id" id="school_id">
            <option value="">-- Select School --</option>
            <?php foreach ($schools as $school): ?>
                <option value="<?= $school['id'] ?>"><?= $school['name'] ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="school_form_fields">
        <div>
            <label for="name">Name</label>
            <input type="text" name="name" id="name">
        </div>

        <div>
            <label for="country">Country</label>
            <input type="text" name="country" id="country">
        </div>

        <div>
            <label for="city">City</label>
            <input type="text" name="city" id="city">
        </div>

        <div>
            <label for="street">Street</label>
            <input type="text" name="street" id="street">
        </div>

        <div>
            <label for="street_num">Street Number</label>
            <input type="number" name="street_num" id="street_num">
        </div>

        <div>
            <label for="director_id">Director</label>
            <select name="director_id" id="director_id">
                <option value="">-- Select Director --</option>
                <?php foreach ($directors as $director): ?>
                    <option value="<?= $director['id'] ?>"><?= $director['full_name'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <button type="submit">Submit</button>
</form>

<script src="/grade-center/js-scripts/school.js"></script>

<?php require '../includes/footer.php'; ?>
