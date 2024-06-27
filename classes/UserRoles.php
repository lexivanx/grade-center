<?php

class UserRoles {

    ### Function to fetch the names of roles based on user ID
    public static function getUserRoles($db_connection, $user_id) {
        $sql_query = "
            SELECT r.name 
            FROM user_roles ur
            JOIN role r ON ur.role_id = r.id
            WHERE ur.user_id = ?
        ";

        $prepared_query = mysqli_prepare($db_connection, $sql_query);

        if ($prepared_query === false) {
            echo mysqli_error($db_connection);
            return [];
        } else {
            mysqli_stmt_bind_param($prepared_query, 'i', $user_id);
            if (mysqli_stmt_execute($prepared_query)) {
                $result = mysqli_stmt_get_result($prepared_query);
                $roles = [];
                while ($row = mysqli_fetch_assoc($result)) {
                    $roles[] = $row['name'];
                }
                return $roles;
            } else {
                echo mysqli_stmt_error($prepared_query);
                return [];
            }
        }
    }

    # Remove existing roles of user before adding updated set
    public static function removeUserRoles($db, $userId) {
        $db->query("DELETE FROM user_roles WHERE user_id = $userId");
    }

    ## Add a role to a user
    public static function addUserRole($db, $userId, $roleId) {
        $stmt = $db->prepare("INSERT INTO user_roles (user_id, role_id) VALUES (?, ?)");
        $stmt->bind_param("ii", $userId, $roleId);
        $stmt->execute();
    }


    # fetches all users with role 'student'.
    # joins 'user' table with the 'user_roles' table and the 'role' table to filter users by role
    public static function fetchAllStudents($db_connection) {
        $studentQuery = $db_connection->prepare("
        SELECT u.id, u.full_name
        FROM user u
        JOIN user_roles ur ON u.id = ur.user_id
        JOIN role r ON ur.role_id = r.id
        WHERE r.name = 'student'
        ");
        $studentQuery->execute();
        $studentResult = $studentQuery->get_result();
        $students = $studentResult->fetch_all(MYSQLI_ASSOC);
        return $students;
    }

    # fetches students from classes taught by the teacher
    # joins 'time_table' table with 'user' table to get details of students
    # joins 'user_roles' and 'role' tables to filter users by role
    # filters by teacher's ID
    public static function fetchTeacherStudents($db_connection, $teacherId) {
        $studentQuery = $db_connection->prepare("
        SELECT DISTINCT u.id, u.full_name
        FROM time_table tt
        JOIN user u ON tt.class_id = u.class_id
        WHERE tt.teacher_id = ? AND u.id IN (
            SELECT ur.user_id
            FROM user_roles ur
            JOIN role r ON ur.role_id = r.id
            WHERE r.name = 'student'
        )
        ");
        $studentQuery->bind_param('i', $teacherId);
        $studentQuery->execute();
        $studentResult = $studentQuery->get_result();
        $students = $studentResult->fetch_all(MYSQLI_ASSOC);
        return $students;
    }

    # If logged-in user is 'director'
    # fetches all students in classes of director's school.
    # joins 'user' table with the 'user_roles' table and the 'role' table to filter users by their role
    # joins 'class' and 'school' tables to filter students by director's school
    public static function fetchStudentsFromSchool($db_connection, $directorId){
        $studentQuery = $db_connection->prepare("
        SELECT u.id, u.full_name
        FROM user u
        JOIN user_roles ur ON u.id = ur.user_id
        JOIN role r ON ur.role_id = r.id
        JOIN class c ON u.class_id = c.id
        JOIN school s ON c.school_id = s.id
        WHERE r.name = 'student' AND s.director_id = ?
        ");
        $studentQuery->bind_param('i', $directorId);
        $studentQuery->execute();
        $studentResult = $studentQuery->get_result();
        $students = $studentResult->fetch_all(MYSQLI_ASSOC);
        return $students;
    }

    ## Fetch teachers
    ## retrieves all users who have role 'teacher'
    ## joins 'user' table with 'user_roles' table and 'role' table to filter users by role
    public static function fetchAllTeachers($db_connection) { 
        $teacherQuery = $db_connection->prepare("
        SELECT u.id, u.full_name
        FROM user u
        JOIN user_roles ur ON u.id = ur.user_id
        JOIN role r ON ur.role_id = r.id
        WHERE r.name = 'teacher'
        ");
        $teacherQuery->execute();
        $teacherResult = $teacherQuery->get_result();
        $teachers = $teacherResult->fetch_all(MYSQLI_ASSOC);
        return $teachers;
    }    
    
}

?>
