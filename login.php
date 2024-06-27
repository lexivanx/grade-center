<?php

require 'classes/RedirectUtil.php';
require 'classes/User.php';
require 'classes/DB.php';
require 'classes/School.php';
require 'classes/UserRoles.php';
require 'classes/Role.php';

session_start();

## Fetch connection to DB
$db_connection = DB::getDB();
$schools = School::getAllSchools($db_connection);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {

    if (User::userAuth($_POST['username'], $_POST['password'], $db_connection)) {

        ## Prevent session fixation
        session_regenerate_id(true);

        ## Set session variables
        $_SESSION['is_logged_in'] = true;
        $_SESSION['username'] = $_POST['username'];

        ## Fetch all necessary user details
        $_SESSION['user_id'] = User::getUserIdByUsername($db_connection, $_SESSION['username']);
        $_SESSION['full_name'] = USer::getUser($db_connection, $_SESSION['user_id'], 'full_name');

        ## Fetch user roles from user_roles table
        $userRoles = UserRoles::getUserRoles($db_connection, $_SESSION['user_id']);
        $_SESSION['user_roles'] = $userRoles;

        // Debug output for user roles
        error_log("User Roles: " . print_r($_SESSION['user_roles'], true));

        RedirectUtil::redirectToPath('/grade-center/index.php');

    } else {

        $error = "Username or password are invalid";

    }

}

?>

<?php require 'includes/header.php'; ?>

<h4> User login </h4>

<?php if (!empty($error)) : ?>

    <p class="error-message"><?= $error ?></p>

<?php endif; ?>

<form method="post">

    <div>
        <label for="username">Username</label>
        <input type="text" name="username" id="username" value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username'], ENT_QUOTES, 'UTF-8') : ''; ?>">
    </div>

    <div>
        <label for="password">Password</label>
        <input type="password" name="password" id="password">
    </div>

    <button type="submit">Login</button>

</form>

<?php require 'includes/footer.php'; ?>
