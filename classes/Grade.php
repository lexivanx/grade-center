<?php
class Grade {

    ### Function to fetch 'grade' columns based on ID
    ### Fetches all columns by default
    public static function getGrade($db_connection, $id, $columns = '*') {
        $sql_query = "SELECT $columns FROM grade WHERE id = ?";

        ## Prevents SQL injection
        $prepared_query = mysqli_prepare($db_connection, $sql_query);

        if ($prepared_query === false ) {
            echo mysqli_error($db_connection);
        } else {
            mysqli_stmt_bind_param($prepared_query, 'i', $id);
            if (mysqli_stmt_execute($prepared_query)) {
                $result = mysqli_stmt_get_result($prepared_query);
                $grade = mysqli_fetch_assoc($result);
                return $grade;
            } else {
                echo mysqli_stmt_error($prepared_query);
            }
        }
    }

    # Add new grade
    public static function addGrade($db_connection, $grade, $studentId, $subjectId) {
        $stmt = $db_connection->prepare("INSERT INTO grade (grade, user_id, subject_id) VALUES (?, ?, ?)");
        $stmt->bind_param('dii', $grade, $studentId, $subjectId);
        $stmt->execute();
    }

    # Update existing grade
    public static function updateGrade($db_connection, $grade, $studentId, $subjectId, $gradeId) {
        $stmt = $db_connection->prepare("UPDATE grade SET grade = ?, user_id = ?, subject_id = ? WHERE id = ?");
        $stmt->bind_param('diii', $grade, $studentId, $subjectId, $gradeId);
        $stmt->execute();
    }

    # Delete existing grade
    public static function deleteGrade($db_connection, $gradeId) {
        $stmt = $db_connection->prepare("DELETE FROM grade WHERE id = ?");
        $stmt->bind_param('i', $gradeId);
        $stmt->execute();
    }

    # fetches all grades
    # joins 'grade' with 'user' and 'subject' tables to get details of students and subjects
    public static function getAllGrades($db_connection) {
        $gradeQuery = $db_connection->prepare("
            SELECT g.id, g.grade, u.full_name, s.name AS subject_name, g.user_id, g.subject_id
            FROM grade g
            JOIN user u ON g.user_id = u.id
            JOIN subject s ON g.subject_id = s.id
            ORDER BY g.id DESC
        ");
        $gradeQuery->execute();
        $gradeResult = $gradeQuery->get_result();
        $grades = $gradeResult->fetch_all(MYSQLI_ASSOC);
        return $grades;
    }

    # fetches grades for students taught by teacher
    # joins 'grade' with 'user', 'subject', and 'time_table' tables to get details of students, subjects, and classes
    # filters by teacher's ID
    public static function getTeacherGrades($db_connection, $teacherId){
        $gradeQuery = $db_connection->prepare("
        SELECT g.id, g.grade, u.full_name, s.name AS subject_name, g.user_id, g.subject_id
        FROM grade g
        JOIN user u ON g.user_id = u.id
        JOIN subject s ON g.subject_id = s.id
        JOIN time_table tt ON u.class_id = tt.class_id
        WHERE tt.teacher_id = ?
        ");
        $gradeQuery->bind_param('i', $teacherId);
        $gradeQuery->execute();
        $gradeResult = $gradeQuery->get_result();
        $grades = $gradeResult->fetch_all(MYSQLI_ASSOC);
        return $grades;
    }

    # fetches grades for the student
    # joins 'grade' with 'subject' and 'user' tables to get details of subjects and students
    public static function getStudentGrades($db_connection) {
        $sql = "
            SELECT g.*, s.name as subject_name, u.full_name as student_name
            FROM grade g
            JOIN subject s ON g.subject_id = s.id
            JOIN user u ON g.user_id = u.id
            WHERE g.user_id = ? 
            ORDER BY g.id DESC";
        $prepared_query = mysqli_prepare($db_connection, $sql);
        mysqli_stmt_bind_param($prepared_query, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($prepared_query);
        $results = mysqli_stmt_get_result($prepared_query);
        return $results;
    }

    # fetches grades for students taught by the logged-in teacher
    # joins 'grade' with 'subject', 'user', and 'time_table' tables to get details of subjects, students, and classes
    # filters by teacher's ID
    public static function getAllTeacherGrades($db_connection) {
        $sql = "
            SELECT g.*, s.name as subject_name, u.full_name as student_name
            FROM grade g
            JOIN subject s ON g.subject_id = s.id
            JOIN user u ON g.user_id = u.id
            JOIN time_table tt ON u.class_id = tt.class_id
            WHERE tt.teacher_id = ?
            ORDER BY g.id DESC";
        $prepared_query = mysqli_prepare($db_connection, $sql);
        mysqli_stmt_bind_param($prepared_query, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($prepared_query);
        $results = mysqli_stmt_get_result($prepared_query);
        return $results;
    }

    # fetches grades for children of the logged-in parent
    # joins 'grade' with 'subject', 'user', and 'parents_children' tables to get details of subjects, students, and parent-child relationships
    # filters by parent's ID
    public static function getParentGrades($db_connection) {
        $sql = "
            SELECT g.*, s.name as subject_name, u.full_name as student_name
            FROM grade g
            JOIN subject s ON g.subject_id = s.id
            JOIN user u ON g.user_id = u.id
            JOIN parents_children pc ON g.user_id = pc.child_id
            WHERE pc.parent_id = ?
            ORDER BY g.id DESC";
        $prepared_query = mysqli_prepare($db_connection, $sql);
        mysqli_stmt_bind_param($prepared_query, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($prepared_query);
        $results = mysqli_stmt_get_result($prepared_query);
        return $results;
    }
    
    # fetches grades for students in classes within the director's school
    # joins 'grade' with 'subject', 'user', 'class', and 'school' tables to get details of subjects, students, classes, and schools
    # filters by director's ID
    public static function getDirectorGrades($db_connection) {
        $sql = "
            SELECT g.*, s.name as subject_name, u.full_name as student_name
            FROM grade g
            JOIN subject s ON g.subject_id = s.id
            JOIN user u ON g.user_id = u.id
            JOIN class c ON u.class_id = c.id
            JOIN school sch ON c.school_id = sch.id
            WHERE sch.director_id = ?
            ORDER BY g.id DESC";
        $prepared_query = mysqli_prepare($db_connection, $sql);
        mysqli_stmt_bind_param($prepared_query, "i", $_SESSION['user_id']);
        mysqli_stmt_execute($prepared_query);
        $results = mysqli_stmt_get_result($prepared_query);
        return $results;
    }
}
?>