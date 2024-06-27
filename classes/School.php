<?php
class School {

    ### Function to fetch 'school' columns based on ID
    ### Fetches all columns by default
    public static function getSchool($db_connection, $id, $columns = '*') {
        $sql_query = "SELECT $columns FROM school WHERE id = ?";

        ## Prevents SQL injection
        $prepared_query = mysqli_prepare($db_connection, $sql_query);

        if ($prepared_query === false ) {
            echo mysqli_error($db_connection);
        } else {
            mysqli_stmt_bind_param($prepared_query, 'i', $id);
            if (mysqli_stmt_execute($prepared_query)) {
                $result = mysqli_stmt_get_result($prepared_query);
                $school = mysqli_fetch_assoc($result);
                return $school;
            } else {
                echo mysqli_stmt_error($prepared_query);
            }
        }
    }

    ## Fetch school ID for director
    public static function getDirectorSchoolId($db_connection, $directorId) {
        $schoolQuery = $db_connection->prepare("SELECT id FROM school WHERE director_id = ?");
        $schoolQuery->bind_param('i', $directorId);
        $schoolQuery->execute();
        $schoolResult = $schoolQuery->get_result();
        $school = $schoolResult->fetch_assoc();
        $schoolId = $school['id'];
        return $schoolId;
    }

    ## Fetch all schools in descending order
    public static function getAllSchools($db_connection, $columns = '*') {
        $stmt = $db_connection->prepare("SELECT $columns FROM school ORDER BY id DESC");
        $stmt->execute();
        $result = $stmt->get_result();
        $schools = $result->fetch_all(MYSQLI_ASSOC);
        return $schools;
    }

    ### Function to add a new school
    public static function createSchool($db_connection, $name, $country, $city, $street, $street_num, $director_id) {
        $stmt = $db_connection->prepare("INSERT INTO school (name, country, city, street, street_num, director_id) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param('ssssid', $name, $country, $city, $street, $street_num, $director_id);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    ### Function to update an existing school
    public static function updateSchool($db_connection, $schoolId, $name, $country, $city, $street, $street_num, $director_id) {
        $stmt = $db_connection->prepare("UPDATE school SET name = ?, country = ?, city = ?, street = ?, street_num = ?, director_id = ? WHERE id = ?");
        $stmt->bind_param('ssssidi', $name, $country, $city, $street, $street_num, $director_id, $schoolId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

    ### Function to delete an existing school
    public static function deleteSchool($db_connection, $schoolId) {
        $stmt = $db_connection->prepare("DELETE FROM school WHERE id = ?");
        $stmt->bind_param('i', $schoolId);
        $stmt->execute();
        return $stmt->affected_rows > 0;
    }

}
?>