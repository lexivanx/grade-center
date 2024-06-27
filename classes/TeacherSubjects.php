<?php
class TeacherSubjects {

    ## Add new teacher-subject relationship
    public static function createTeacherSubjectRel($db_connection, $teacherId, $subjectId) {
        $stmt = $db_connection->prepare("INSERT INTO teacher_subjects (teacher_id, subject_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $teacherId, $subjectId);
        $stmt->execute();
    }
    
    ## Update existing teacher-subject relationship
    public static function updateTeacherSubjectRel($db_connection, $teacherSubjectId, $teacherId, $subjectId) {
        $stmt = $db_connection->prepare("UPDATE teacher_subjects SET teacher_id = ?, subject_id = ? WHERE id = ?");
        $stmt->bind_param('iii', $teacherId, $subjectId, $teacherSubjectId);
        $stmt->execute();
    }

    ## Delete existing teacher-subject relationship
    public static function deleteTeacherSubjectRel($db_connection, $teacherSubjectId) {
        $stmt = $db_connection->prepare("DELETE FROM teacher_subjects WHERE id = ?");
        $stmt->bind_param('i', $teacherSubjectId);
        $stmt->execute();
    }

    ## Fetch teacher-subject relationships
    ## retrieves relationships between 'teachers' and 'subjects' from the 'teacher_subjects' table
    ## joins 'user' table to get 'teacher' details and 'subject' table to get subject details
    ## results are ordered by ID in descending order
    public static function fetchTeacherSubjectRelationships($db_connection) {
        $relationshipQuery = $db_connection->prepare("
            SELECT ts.id, t.full_name as teacher_name, s.name as subject_name, ts.teacher_id, ts.subject_id
            FROM teacher_subjects ts
            JOIN user t ON ts.teacher_id = t.id
            JOIN subject s ON ts.subject_id = s.id
            ORDER BY ts.id DESC
            ");
        $relationshipQuery->execute();
        $relationshipResult = $relationshipQuery->get_result();
        $relationships = $relationshipResult->fetch_all(MYSQLI_ASSOC);
        return $relationships;
    }

    # fetches subjects taught by the teacher
    # joins 'teacher_subjects' table with 'subject' table to get details of subjects
    # filters by teacher's ID
    public static function fetchTeacherSubjects($db_connection, $teacherId) {
        $subjectQuery = $db_connection->prepare("
            SELECT s.id, s.name 
            FROM teacher_subjects ts
            JOIN subject s ON ts.subject_id = s.id
            WHERE ts.teacher_id = ?
            ");
        $subjectQuery->bind_param('i', $teacherId);
        $subjectQuery->execute();
        $subjectResult = $subjectQuery->get_result();
        $subjects = $subjectResult->fetch_all(MYSQLI_ASSOC);
        return $subjects;
    }

    

}
?>
