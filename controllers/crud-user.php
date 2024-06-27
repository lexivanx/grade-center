<?php
require '../classes/DB.php';
require '../classes/Role.php';
require '../classes/RedirectUtil.php';
require '../classes/User.php';
require '../classes/UserRoles.php';

session_start();

if (!User::checkAuthentication() || !in_array('admin', $_SESSION['user_roles'])) {
    RedirectUtil::redirectToPath('/grade-center/index.php');
    exit;
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $db = DB::getDB();

    $action = $_POST['action'] ?? '';
    $userId = $_POST['user_id'] ?? null;
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $fullName = $_POST['full_name'] ?? '';
    $age = $_POST['age'] ?? '';
    $classId = $_POST['class_id'] ?? '';
    $roles = $_POST['roles'] ?? [];

    if (empty($username)) {
        $errors[] = "Username is required.";
    } else {
        // Check if the username already exists
        $existingUserId = User::getUserIdByUsername($db, $username);
        if ($action == 'create') {
            if ($existingUserId) {
                $errors[] = "Username already exists.";
            }
        } else if ($action == 'update') {
            if ($existingUserId && $existingUserId != $userId) {
                $errors[] = "Username already exists.";
            }
        }
    }
    
    if ($action == 'create' && empty($password)) {
        $errors[] = "Password is required.";
    }
    if (empty($fullName)) {
        $errors[] = "Full name is required.";
    }
    if (empty($age)) {
        $errors[] = "Age is required.";
    }

    if (empty($errors)) {
        if ($action == 'create') {
            ## Add a new user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            $userId = User::createUser($db, $username, $hashedPassword, $fullName, $age, $classId);
            $success = "User created successfully.";
        } elseif ($action == 'update') {
            ## Update user
            User::updateUser($db, $userId, $username, $fullName, $age, $classId);

            if ($password) {
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                User::updateUserPassword($db, $userId, $hashedPassword);
            }
            $success = "User updated successfully.";
        } elseif ($action == 'delete') {
            ## Delete user roles and then user
            UserRoles::removeUserRoles($db, $userId);
            User::deleteUser($db, $userId);
            $success = "User deleted successfully.";
        }

        ## Update roles when creating / updating
        if ($action != 'delete') {
            UserRoles::removeUserRoles($db, $userId);
            foreach ($roles as $role) {
                $roleData = Role::fetchUserRoleByName($db, $role);
                $roleId = $roleData['id'] ?? null;
                if ($roleId) {
                    UserRoles::addUserRole($db, $userId, $roleId);
                }
            }
        }
    }
}

$db = DB::getDB();
## Fetch existing users for dropdown
$users = User::fetchUsers($db);

require '../includes/header.php';
?>

<h4>User Management</h4>

<?php if (!empty($errors)): ?>
    <div class="error-message">
        <?php foreach ($errors as $error): ?>
            <p><?= htmlspecialchars($error) ?></p>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<?php if (!empty($success)): ?>
    <div class="success-message">
        <p><?= htmlspecialchars($success) ?></p>
    </div>
<?php endif; ?>

<form method="post">
    <div>
        <input type="radio" id="create_user" name="action" value="create">
        <label for="create_user">Create User</label>
    </div>
    <div>
        <input type="radio" id="update_user" name="action" value="update" checked>
        <label for="update_user">Update User</label>
    </div>
    <div>
        <input type="radio" id="delete_user" name="action" value="delete">
        <label for="delete_user">Delete User</label>
    </div>

    <div id="existing_user_select">
        <label for="user_id">Select User</label>
        <select name="user_id" id="user_id">
            <?php foreach ($users as $user): ?>
                <option value="<?= $user['id'] ?>"><?= htmlspecialchars($user['username']) ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="user_form_fields">
        <div>
            <label for="username">Username</label>
            <input type="text" name="username" id="username">
        </div>

        <div>
            <label for="password">Password</label>
            <input type="password" name="password" id="password">
        </div>

        <div>
            <label for="full_name">Full Name</label>
            <input type="text" name="full_name" id="full_name">
        </div>

        <div>
            <label for="age">Age</label>
            <input type="number" name="age" id="age">
        </div>

        <div>
            <p>Select School and Class - STUDENTS ONLY</p>
            <label for="school_id">School</label>
            <select name="school_id" id="school_id">
                <option value="">-- Select School --</option>
            </select>
        </div>

        <div>
            <label for="class_id">Class</label>
            <select name="class_id" id="class_id">
                <option value="">-- Select Class --</option>
            </select>
        </div>

        <div>
            <label>Roles</label><br>
            <?php list($roles, $selectedRoles) = Role::getRoles(); ?>
            <?php foreach ($roles as $role): ?>
                <input type="checkbox" name="roles[]" value="<?= $role ?>" <?= isset($userData) && in_array($role, $userData['roles']) ? 'checked' : '' ?>> <?= ucfirst($role) ?><br>
            <?php endforeach; ?>
        </div>
    </div>

    <button type="submit">Submit</button>
</form>

<script src="/grade-center/js-scripts/user.js"></script>

<?php require '../includes/footer.php'; ?>
