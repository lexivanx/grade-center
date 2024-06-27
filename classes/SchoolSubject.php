<?php
class SchoolSubject {

    ## Add new subject
    public static function createSubject($db_connection, $name) {
        $stmt = $db_connection->prepare("INSERT INTO subject (name) VALUES (?)");
        $stmt->bind_param('s', $name);
        $stmt->execute();
    }

    ## Update existing subject
    public static function updateSubject($db_connection, $subjectId, $name) {
        $stmt = $db_connection->prepare("UPDATE subject SET name = ? WHERE id = ?");
        $stmt->bind_param('si', $name, $subjectId);
        $stmt->execute();
    }

    ## Delete existing subject
    public static function deleteSubject($db_connection, $subjectId) {
        $stmt = $db_connection->prepare("DELETE FROM subject WHERE id = ?");
        $stmt->bind_param('i', $subjectId);
        $stmt->execute();
    }

    ## Fetch all subjects, columns id and name
    public static function fetchAllSubjects($db_connection) {
        $subjectQuery = $db_connection->prepare("SELECT id, name FROM subject ORDER BY id DESC");
        $subjectQuery->execute();
        $subjectResult = $subjectQuery->get_result();
        $subjects = $subjectResult->fetch_all(MYSQLI_ASSOC);
        return $subjects;
    }
    

}
?>
