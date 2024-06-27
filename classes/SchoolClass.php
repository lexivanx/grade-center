<?php
class SchoolClass {

    ### Function to fetch 'class' columns based on ID
    ### Fetches all columns by default
    public static function getSchoolClass($db_connection, $id, $columns = '*') {
        $sql_query = "SELECT $columns FROM class WHERE id = ?";
        $prepared_query = mysqli_prepare($db_connection, $sql_query);

        if ($prepared_query === false ) {
            echo mysqli_error($db_connection);
        } else {
            mysqli_stmt_bind_param($prepared_query, 'i', $id);
            if (mysqli_stmt_execute($prepared_query)) {
                $result = mysqli_stmt_get_result($prepared_query);
                $school_class = mysqli_fetch_assoc($result);
                return $school_class;
            } else {
                echo mysqli_stmt_error($prepared_query);
            }
        }
    }

    ## Add new class
    public static function createSchoolClass($db_connection, $grade, $letter, $schoolId) {
        $stmt = $db_connection->prepare("INSERT INTO class (grade, letter, school_id) VALUES (?, ?, ?)");
        $stmt->bind_param('isi', $grade, $letter, $schoolId);
        $stmt->execute();
    }

    ## Update existing class
    public static function updateSchoolClass($db_connection, $classId, $grade, $letter, $schoolId) {
        $stmt = $db_connection->prepare("UPDATE class SET grade = ?, letter = ?, school_id = ? WHERE id = ?");
        $stmt->bind_param('isii', $grade, $letter, $schoolId, $classId);
        $stmt->execute();
    }
    
    ## Delete existing class
    public static function deleteSchoolClass($db_connection, $classId) {
        $stmt = $db_connection->prepare("DELETE FROM class WHERE id = ?");
        $stmt->bind_param('i', $classId);
        $stmt->execute();
    }

    # If logged-in user is 'admin':
    # fetches all classes and their school names
    # joins 'class' table with the 'school' table to get the school name for each class
    public static function fetchAllClasses($db_connection) {
        $classQuery = $db_connection->prepare("
        SELECT c.id, c.grade, c.letter, s.name as school_name, c.school_id
        FROM class c
        JOIN school s ON c.school_id = s.id
        ORDER BY c.id DESC
        ");
        $classQuery->execute();
        $classResult = $classQuery->get_result();
        $classes = $classResult->fetch_all(MYSQLI_ASSOC);
        return $classes;
    }

    # If logged-in user is 'director':
    # fetches all classes in director's school and school names.
    # joins 'class' table with 'school' table to get school name of classes
    # filters classes by director's school
    public static function fetchDirectorClasses($db_connection, $directorId) {
        $classQuery = $db_connection->prepare("
        SELECT c.id, c.grade, c.letter, s.name as school_name, c.school_id
        FROM class c
        JOIN school s ON c.school_id = s.id
        WHERE s.director_id = ?
        ORDER BY c.id DESC
        ");
        $classQuery->bind_param('i', $directorId);
        $classQuery->execute();
        $classResult = $classQuery->get_result();
        $classes = $classResult->fetch_all(MYSQLI_ASSOC);
        return $classes;
    }
    
    # If logged-in user is 'admin':
    # fetches all classes and their respective school names
    # joins 'class' table with 'school' table to get school name for each class 
    public static function fetchAllClassesAndNames($db_connection) {
        $classQuery = $db_connection->prepare("
        SELECT c.id, CONCAT(c.grade, c.letter, ' - ', s.name) as class_name
        FROM class c
        JOIN school s ON c.school_id = s.id
        ");
        $classQuery->execute();
        $classResult = $classQuery->get_result();
        $classes = $classResult->fetch_all(MYSQLI_ASSOC);
        return $classes;
    }
    
    # If logged-in user is 'director':
    # fetches all classes in director's school
    # joins 'class' table with 'school' table to get school name for each class
    # filters classes by director's school
    public static function fetchDirectorClassesAndNames($db_connection, $directorId) {
        $classQuery = $db_connection->prepare("
        SELECT c.id, CONCAT(c.grade, c.letter) as class_name
        FROM class c
        JOIN school s ON c.school_id = s.id
        WHERE s.director_id = ?
        ");
        $classQuery->bind_param('i', $directorId);
        $classQuery->execute();
        $classResult = $classQuery->get_result();
        $classes = $classResult->fetch_all(MYSQLI_ASSOC);
        return $classes;
    }


}
?>
