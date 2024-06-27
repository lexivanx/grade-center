<?php

class User {

    ### Return true if user and pass are correct
    public static function userAuth($username, $password, $db_connection) {
        $sql_query = "SELECT * FROM user WHERE username = ?";

        $prepared_query = mysqli_prepare($db_connection, $sql_query);

        ## Check for error in query
        if ($prepared_query === false) {
            echo mysqli_error($db_connection);
        } else {
            ## Bind username and execute query
            mysqli_stmt_bind_param($prepared_query, "s", $username);
            mysqli_stmt_execute($prepared_query);

            $result = mysqli_stmt_get_result($prepared_query);
            $user = mysqli_fetch_assoc($result);

            ## Verify if hashed pass is correct
            if ($user) {
                return password_verify($password, $user['password']);
            }
        }
        ## Return false if no user is found or on error
        return false; 
    }

    ### Function to fetch 'user' columns based on ID
    ### Fetches all columns by default
    public static function getUser($db_connection, $id, $columns = '*') {
        $sql_query = "SELECT $columns FROM user WHERE id = ?";

        ## Prevents SQL injection
        $prepared_query = mysqli_prepare($db_connection, $sql_query);

        if ($prepared_query === false ) {
            echo mysqli_error($db_connection);
        } else {
            mysqli_stmt_bind_param($prepared_query, 'i', $id);
            if (mysqli_stmt_execute($prepared_query)) {
                $result = mysqli_stmt_get_result($prepared_query);
                $user = mysqli_fetch_assoc($result);
                return $user;
            } else {
                echo mysqli_stmt_error($prepared_query);
            }
        }
    }

    ### Used for login to fetch Id based on the login username info
    public static function getUserIdByUsername($db_connection, $username, $columns = 'id') {
        $sql_query = "SELECT $columns FROM user WHERE username = ?";
    
        ## Prevents SQL injection
        $prepared_query = mysqli_prepare($db_connection, $sql_query);
    
        if ($prepared_query === false) {
            echo mysqli_error($db_connection);
        } else {
            mysqli_stmt_bind_param($prepared_query, 's', $username);
            if (mysqli_stmt_execute($prepared_query)) {
                $result = mysqli_stmt_get_result($prepared_query);
                $user = mysqli_fetch_assoc($result);
                return $user['id'];
            } else {
                echo mysqli_stmt_error($prepared_query);
            }
        }
        return null;
    }
    
    ## Fetch directors for dropdown
    ## fetches all users with role 'director'
    ## joins 'user' table with 'user_roles' table and 'role' table to filter users by role
    public static function getDirectors($db_connection) {
        $stmt = $db_connection->prepare("
            SELECT u.id, u.full_name
            FROM user u
            JOIN user_roles ur ON u.id = ur.user_id
            JOIN role r ON ur.role_id = r.id
            WHERE r.name = 'director'
        ");
        $stmt->execute();
        $result = $stmt->get_result();
        $directors = $result->fetch_all(MYSQLI_ASSOC);
        return $directors;
    }
    
    ## Check if session variable is set and true
    public static function checkAuthentication() {
        return isset($_SESSION['is_logged_in']) && $_SESSION['is_logged_in'];
    }

    ## Add new user
    public static function createUser($db, $username, $hashedPassword, $fullName, $age, $classId) {
        if (!empty($classId)) {
            $stmt = $db->prepare("INSERT INTO user (username, password, full_name, age, class_id) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssii", $username, $hashedPassword, $fullName, $age, $classId);
        } else {
            $stmt = $db->prepare("INSERT INTO user (username, password, full_name, age) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sssi", $username, $hashedPassword, $fullName, $age);
        }
        $stmt->execute();
        return $stmt->insert_id;
    }

    ## Update user
    public static function updateUser($db, $userId, $username, $fullName, $age, $classId) {
        if (!empty($classId)) {
            $stmt = $db->prepare("UPDATE user SET username = ?, full_name = ?, age = ?, class_id = ? WHERE id = ?");
            $stmt->bind_param("ssiii", $username, $fullName, $age, $classId, $userId);
        } else {
            $stmt = $db->prepare("UPDATE user SET username = ?, full_name = ?, age = ?, class_id = NULL WHERE id = ?");
            $stmt->bind_param("ssii", $username, $fullName, $age, $userId);
        }
        $stmt->execute();
    }

    ## Update password only
    public static function updateUserPassword($db, $userId, $hashedPassword) {
        $stmt = $db->prepare("UPDATE user SET password = ? WHERE id = ?");
        $stmt->bind_param("si", $hashedPassword, $userId);
        $stmt->execute();
    }

    ## Delete user
    public static function deleteUser($db, $userId) {
        $stmt = $db->prepare("DELETE FROM user WHERE id = ?");
        $stmt->bind_param("i", $userId);
        $stmt->execute();
    }

    ## Fetch all users' id and username columns 
    public static function fetchUsers($db) {
        $result = $db->query("SELECT id, username FROM user");
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function fetchUserRoleByName($db, $role) {
        $stmt = $db->prepare("SELECT id FROM role WHERE name = ?");
        $stmt->bind_param('s', $role);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

    public static function addUserRole($db, $userId, $roleId) {
        $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $roleId);
        $stmt->execute();
    }
}
?>