<?php

class Role {

    ### Function to fetch 'role' columns based on ID
    ### Fetches all columns by default
    public static function getRole($db_connection, $role_id, $columns = '*') {
        $sql_query = "SELECT $columns FROM role WHERE id = ?";
    
        $prepared_query = mysqli_prepare($db_connection, $sql_query);
    
        if ($prepared_query === false) {
            echo mysqli_error($db_connection);
            return null;
        } else {
            mysqli_stmt_bind_param($prepared_query, 'i', $role_id);
            if (mysqli_stmt_execute($prepared_query)) {
                $result = mysqli_stmt_get_result($prepared_query);
                return mysqli_fetch_assoc($result);
            } else {
                echo mysqli_stmt_error($prepared_query);
                return null;
            }
        }
    }

    ### Function to fetch role names array of a user based on ID
    public static function getRoles($userId = null) {
        $db = DB::getDB();
        $roles = ['admin', 'director', 'teacher', 'parent', 'student'];
        $selectedRoles = [];
    
        if ($userId) {
            $stmt = $db->prepare("SELECT r.name FROM user_roles ur JOIN role r ON ur.role_id = r.id WHERE ur.user_id = ?");
            $stmt->bind_param('i', $userId);
            $stmt->execute();
            $result = $stmt->get_result();
            while ($row = $result->fetch_assoc()) {
                $selectedRoles[] = $row['name'];
            }
        }
    
        return [$roles, $selectedRoles];
    }
    
    ## Get the id of a role by name
    public static function fetchUserRoleByName($db, $role) {
        $stmt = $db->prepare("SELECT id FROM role WHERE name = ?");
        $stmt->bind_param('s', $role);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->fetch_assoc();
    }

}

?>
