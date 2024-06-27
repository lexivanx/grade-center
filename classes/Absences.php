<?php
class Absences {

    # Add new absence
    public static function createAbsence($db_connection, $dateOfAbsence, $studentId, $subjectId) {
        $stmt = $db_connection->prepare("INSERT INTO absences (date_of_absence, student_id, subject_id) VALUES (?, ?, ?)");
        $stmt->bind_param('sii', $dateOfAbsence, $studentId, $subjectId);
        $stmt->execute();
    }

    # Update existing absence
    public static function updateAbsence($db_connection, $absenceId, $dateOfAbsence, $studentId, $subjectId) {
        $stmt = $db_connection->prepare("UPDATE absences SET date_of_absence = ?, student_id = ?, subject_id = ? WHERE id = ?");
        $stmt->bind_param('siii', $dateOfAbsence, $studentId, $subjectId, $absenceId);
        $stmt->execute();
    }

    # Delete existing absence
    public static function deleteAbsence($db_connection, $absenceId) {
        $stmt = $db_connection->prepare("DELETE FROM absences WHERE id = ?");
        $stmt->bind_param('i', $absenceId);
        $stmt->execute();
    }

    # If logged-in user is 'admin':
    # fetches all absences
    # joins 'absences' table with 'user' and 'subject' tables to get details of students and subjects
    public static function fetchAllAbsences($db_connection) {
        $absenceQuery = $db_connection->prepare("
            SELECT a.id, a.date_of_absence, u.full_name, s.name AS subject_name, a.student_id, a.subject_id
            FROM absences a
            JOIN user u ON a.student_id = u.id
            JOIN subject s ON a.subject_id = s.id
            ORDER BY a.id DESC
            ");
        $absenceQuery->execute();
        $absenceResult = $absenceQuery->get_result();
        $absences = $absenceResult->fetch_all(MYSQLI_ASSOC);
        return $absences;
    }


    # fetches absences for classes taught by the teacher
    # joins 'absences' table with 'user', 'subject', and 'time_table' tables to get details of students, subjects, and classes
    # filters by teacher's ID
    public static function fetchTeacherAbsences($db_connection, $teacherId) {
        $absenceQuery = $db_connection->prepare("
            SELECT a.id, a.date_of_absence, u.full_name, s.name AS subject_name, a.student_id, a.subject_id
            FROM absences a
            JOIN user u ON a.student_id = u.id
            JOIN subject s ON a.subject_id = s.id
            JOIN time_table tt ON u.class_id = tt.class_id
            JOIN teacher_subjects ts ON ts.subject_id = a.subject_id AND ts.teacher_id = tt.teacher_id
            WHERE tt.teacher_id = ?
            ORDER BY a.id DESC
        ");
        $absenceQuery->bind_param('i', $teacherId);
        $absenceQuery->execute();
        $absenceResult = $absenceQuery->get_result();
        $absences = $absenceResult->fetch_all(MYSQLI_ASSOC);
        return $absences;
    }
    
    # fetches absences for students in classes within the director's school
    # joins 'absences' with 'subject', 'user', 'class', and 'school' tables to get details of subjects, students, classes, and schools
    # filters by director's ID
    public static function fetchDirectorAbsences($db_connection, $directorId) {
        $absenceQuery = $db_connection->prepare("
            SELECT a.id, a.date_of_absence, u.full_name, s.name AS subject_name, a.student_id, a.subject_id
            FROM absences a
            JOIN user u ON a.student_id = u.id
            JOIN subject s ON a.subject_id = s.id
            JOIN class c ON u.class_id = c.id
            JOIN school sch ON c.school_id = sch.id
            WHERE sch.director_id = ?
            ORDER BY a.id DESC
            ");
        $absenceQuery->bind_param('i', $directorId);
        $absenceQuery->execute();
        $absenceResult = $absenceQuery->get_result();
        $absences = $absenceResult->fetch_all(MYSQLI_ASSOC);
        return $absences;
    }

    # fetches absences for children of the logged-in parent
    # joins 'absences' with 'subject', 'user', and 'parents_children' tables to get details of subjects, students, and parent-child relationships
    # filters by parent's ID
    public static function fetchParentAbsences($db_connection, $parentId) {
        $absenceQuery = $db_connection->prepare("
            SELECT a.id, a.date_of_absence, u.full_name, s.name AS subject_name, a.student_id, a.subject_id
            FROM absences a
            JOIN user u ON a.student_id = u.id
            JOIN subject s ON a.subject_id = s.id
            JOIN parents_children pc ON a.student_id = pc.child_id
            WHERE pc.parent_id = ?
            ORDER BY a.id DESC
            ");
        $absenceQuery->bind_param('i', $parentId);
        $absenceQuery->execute();
        $absenceResult = $absenceQuery->get_result();
        $absences = $absenceResult->fetch_all(MYSQLI_ASSOC);
        return $absences;
    }

    # fetches absences for the logged-in student
    # joins 'absences' with 'subject' and 'user' tables to get details of subjects and students
    # filters by student's ID
    public static function fetchStudentAbsences($db_connection, $studentId) {
        $absenceQuery = $db_connection->prepare("
            SELECT a.id, a.date_of_absence, u.full_name, s.name AS subject_name, a.student_id, a.subject_id
            FROM absences a
            JOIN user u ON a.student_id = u.id
            JOIN subject s ON a.subject_id = s.id
            WHERE a.student_id = ?
            ORDER BY a.id DESC
            ");
        $absenceQuery->bind_param('i', $studentId);
        $absenceQuery->execute();
        $absenceResult = $absenceQuery->get_result();
        $absences = $absenceResult->fetch_all(MYSQLI_ASSOC);
        return $absences;
    }
}
?>