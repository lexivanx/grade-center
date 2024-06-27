<?php
class Analytics {

    public static function getTeacherAverages($db_connection, $userRoles, $schoolId = null) {
        if (in_array('admin', $userRoles)) {
            $sql = "
                SELECT sch.name AS school_name, t.full_name AS teacher_name, AVG(g.grade) AS average_grade
                FROM grade g
                JOIN user s ON g.user_id = s.id
                JOIN class c ON s.class_id = c.id
                JOIN school sch ON c.school_id = sch.id
                JOIN teacher_subjects ts ON g.subject_id = ts.subject_id
                JOIN user t ON ts.teacher_id = t.id
                GROUP BY sch.name, t.full_name
                ORDER BY sch.name, t.full_name";
            $params = [];
        } elseif (in_array('director', $userRoles)) {
            $sql = "
                SELECT sch.name AS school_name, t.full_name AS teacher_name, AVG(g.grade) AS average_grade
                FROM grade g
                JOIN user s ON g.user_id = s.id
                JOIN class c ON s.class_id = c.id
                JOIN school sch ON c.school_id = sch.id
                JOIN teacher_subjects ts ON g.subject_id = ts.subject_id
                JOIN user t ON ts.teacher_id = t.id
                WHERE sch.id = ?
                GROUP BY sch.name, t.full_name
                ORDER BY sch.name, t.full_name";
            $params = [$schoolId];
        }

        $query = $db_connection->prepare($sql);
        if (!empty($params)) {
            $types = str_repeat('i', count($params));
            $query->bind_param($types, ...$params);
        }
        $query->execute();
        $result = $query->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }

    public static function getSubjectAverages($db_connection, $userRoles, $schoolId = null) {
        if (in_array('admin', $userRoles)) {
            $sql = "
                SELECT sch.name AS school_name, sub.name AS subject_name, AVG(g.grade) AS average_grade
                FROM grade g
                JOIN subject sub ON g.subject_id = sub.id
                JOIN user s ON g.user_id = s.id
                JOIN class c ON s.class_id = c.id
                JOIN school sch ON c.school_id = sch.id
                GROUP BY sch.name, sub.name
                ORDER BY sch.name, sub.name";
            $params = [];
        } elseif (in_array('director', $userRoles)) {
            $sql = "
                SELECT sch.name AS school_name, sub.name AS subject_name, AVG(g.grade) AS average_grade
                FROM grade g
                JOIN subject sub ON g.subject_id = sub.id
                JOIN user s ON g.user_id = s.id
                JOIN class c ON s.class_id = c.id
                JOIN school sch ON c.school_id = sch.id
                WHERE sch.id = ?
                GROUP BY sch.name, sub.name
                ORDER BY sch.name, sub.name";
            $params = [$schoolId];
        }

        $query = $db_connection->prepare($sql);
        if (!empty($params)) {
            $types = str_repeat('i', count($params));
            $query->bind_param($types, ...$params);
        }
        $query->execute();
        $result = $query->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    }
    
}
?>