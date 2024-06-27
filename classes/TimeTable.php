<?php
class TimeTable {

    # Add new time table entry
    public static function createTimeTableEntry($db_connection, $dayWeek, $timeStart, $timeEnd, $semester, $teacherId, $classId, $subjectId) {
        $stmt = $db_connection->prepare("INSERT INTO time_table (day_week, time_start, time_end, semester, teacher_id, class_id, subject_id) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssiii', $dayWeek, $timeStart, $timeEnd, $semester, $teacherId, $classId, $subjectId);
        $stmt->execute();
    }

    # Update existing time table entry
    public static function updateTimeTableEntry($db_connection, $timeTableId, $dayWeek, $timeStart, $timeEnd, $semester, $teacherId, $classId, $subjectId) {
        $stmt = $db_connection->prepare("UPDATE time_table SET day_week = ?, time_start = ?, time_end = ?, semester = ?, teacher_id = ?, class_id = ?, subject_id = ? WHERE id = ?");
        $stmt->bind_param('ssssiiii', $dayWeek, $timeStart, $timeEnd, $semester, $teacherId, $classId, $subjectId, $timeTableId);
        $stmt->execute();
    }

    # Delete existing time table entry
    public static function deleteTimeTableEntry($db_connection, $timeTableId) {
        $stmt = $db_connection->prepare("DELETE FROM time_table WHERE id = ?");
        $stmt->bind_param('i', $timeTableId);
        $stmt->execute();
    }
    
    # If logged-in user is 'admin':
    # fetches all entries from 'time_table'
    # joins 'time_table' with 'user', 'class', and 'subject' tables to get details of teacher, class, and subject
    public static function fetchAllTimeTables($db_connection) {
        $timeTableQuery = $db_connection->prepare("
        SELECT tt.id, tt.day_week, tt.time_start, tt.time_end, tt.semester, sch.name as school_name,
               t.full_name as teacher_name, c.grade, c.letter, s.name as subject_name, 
               tt.teacher_id, tt.class_id, tt.subject_id
        FROM time_table tt
        JOIN user t ON tt.teacher_id = t.id
        JOIN class c ON tt.class_id = c.id
        JOIN subject s ON tt.subject_id = s.id
        JOIN school sch ON c.school_id = sch.id
        ORDER BY tt.day_week, tt.time_start
        ");
        $timeTableQuery->execute();
        $timeTableResult = $timeTableQuery->get_result();
        $timeTables = $timeTableResult->fetch_all(MYSQLI_ASSOC);
        return $timeTables;
    }

    # If logged-in user is 'director':
    # fetches all entries from 'time_table' for classes in director's school
    # joins 'time_table' with 'user', 'class', 'school', and 'subject' tables to get details of teacher, class, and subject
    # filters by director's school
    public static function fetchDirectorTimeTable($db_connection, $directorId) {
        $timeTableQuery = $db_connection->prepare("
        SELECT tt.id, tt.day_week, tt.time_start, tt.time_end, tt.semester, 
               t.full_name as teacher_name, c.grade, c.letter, s.name as subject_name, 
               tt.teacher_id, tt.class_id, tt.subject_id
        FROM time_table tt
        JOIN user t ON tt.teacher_id = t.id
        JOIN class c ON tt.class_id = c.id
        JOIN school sch ON c.school_id = sch.id
        JOIN subject s ON tt.subject_id = s.id
        WHERE sch.director_id = ?
        ORDER BY tt.day_week, tt.time_start
        ");
        $timeTableQuery->bind_param('i', $directorId);
        $timeTableQuery->execute();
        $timeTableResult = $timeTableQuery->get_result();
        $timeTables = $timeTableResult->fetch_all(MYSQLI_ASSOC);
        return $timeTables;
    }

    # fetches entries from 'time_table' where the logged-in teacher is assigned
    # joins 'time_table' with 'user', 'class', 'subject', and 'school' tables to get details of teachers, classes, subjects, and schools
    # filters by teacher's ID
    public static function fetchTeacherTimeTable($db_connection, $teacherId) {
        $timeTableQuery = $db_connection->prepare("
            SELECT tt.*, t.full_name as teacher_name, c.grade, c.letter, s.name as subject_name, sch.name as school_name
            FROM time_table tt
            JOIN user t ON tt.teacher_id = t.id
            JOIN class c ON tt.class_id = c.id
            JOIN subject s ON tt.subject_id = s.id
            JOIN school sch ON c.school_id = sch.id
            WHERE tt.teacher_id = ?
            ORDER BY tt.day_week, tt.time_start");
        $timeTableQuery->bind_param('i', $teacherId);
        $timeTableQuery->execute();
        $timeTableResult = $timeTableQuery->get_result();
        $timeTables = $timeTableResult->fetch_all(MYSQLI_ASSOC);
        return $timeTables;
    }

    # fetches entries from 'time_table' for classes of the logged-in parent's children
    # joins 'time_table' with 'user', 'class', 'subject', 'school', 'user' (students), and 'parents_children' tables to get details of teachers, classes, subjects, schools, and parent-child relationships
    # filters by parent's ID
    public static function fetchParentTimeTable($db_connection, $parentId) {
        $timeTableQuery = $db_connection->prepare("
            SELECT tt.*, t.full_name as teacher_name, c.grade, c.letter, s.name as subject_name, sch.name as school_name
            FROM time_table tt
            JOIN user t ON tt.teacher_id = t.id
            JOIN class c ON tt.class_id = c.id
            JOIN subject s ON tt.subject_id = s.id
            JOIN school sch ON c.school_id = sch.id
            JOIN user u ON u.class_id = c.id
            JOIN parents_children pc ON pc.child_id = u.id
            WHERE pc.parent_id = ?
            ORDER BY tt.day_week, tt.time_start");
        $timeTableQuery->bind_param('i', $parentId);
        $timeTableQuery->execute();
        $timeTableResult = $timeTableQuery->get_result();
        $timeTables = $timeTableResult->fetch_all(MYSQLI_ASSOC);        
        return $timeTables;
    }
    
    # fetches entries from 'time_table' for the logged-in student's class
    # joins 'time_table' with 'user', 'class', and 'subject' tables to get details of teachers, classes, and subjects
    # filters by student's class ID
    public static function fetchStudentTimeTable($db_connection, $studentId) {
        $timeTableQuery = $db_connection->prepare("
            SELECT tt.*, t.full_name as teacher_name, c.grade, c.letter, s.name as subject_name
            FROM time_table tt
            JOIN user t ON tt.teacher_id = t.id
            JOIN class c ON tt.class_id = c.id
            JOIN subject s ON tt.subject_id = s.id
            WHERE c.id = (SELECT class_id FROM user WHERE id = ?)
            ORDER BY tt.day_week, tt.time_start");
        $timeTableQuery->bind_param('i', $studentId);
        $timeTableQuery->execute();
        $timeTableResult = $timeTableQuery->get_result();
        $timeTables = $timeTableResult->fetch_all(MYSQLI_ASSOC);
        return $timeTables;
    }

}
?>
