<?php
class ParentsChildren {

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


    ## Add new parent-child relationship
    public static function addParentChildRel($db_connection, $parentId, $childId) {
        $stmt = $db_connection->prepare("INSERT INTO parents_children (parent_id, child_id) VALUES (?, ?)");
        $stmt->bind_param('ii', $parentId, $childId);
        $stmt->execute();
    }

    ## Update existing parent-child relationship
    public static function updateParentChildRel($db_connection, $parentChildId, $parentId, $childId) {
        $stmt = $db_connection->prepare("UPDATE parents_children SET parent_id = ?, child_id = ? WHERE id = ?");
        $stmt->bind_param('iii', $parentId, $childId, $parentChildId);
        $stmt->execute();
    }

    ## Delete existing parent-child relationship
    public static function deleteParentChildRel($db_connection, $parentChildId) {
        $stmt = $db_connection->prepare("DELETE FROM parents_children WHERE id = ?");
        $stmt->bind_param('i', $parentChildId);
        $stmt->execute();
    }
    

    ## Fetch all parent-child relationships
    ## joins 'user' table twice: get parent details and get child details
    ## ordered by ID in descending order
    public static function fetchParentChildRelationships($db_connection) {
        $relationshipQuery = $db_connection->prepare("
        SELECT pc.id, p.full_name as parent_name, c.full_name as child_name, pc.parent_id, pc.child_id
        FROM parents_children pc
        JOIN user p ON pc.parent_id = p.id
        JOIN user c ON pc.child_id = c.id
        ORDER BY pc.id DESC
        ");
        $relationshipQuery->execute();
        $relationshipResult = $relationshipQuery->get_result();
        $relationships = $relationshipResult->fetch_all(MYSQLI_ASSOC);
        return $relationships;
    }

    ## Fetch parents - users with role 'parent'
    ## joins 'user' table with 'user_roles' table and 'role' table to filter users by their role
    public static function fetchParents($db_connection) {
        $parentQuery = $db_connection->prepare("
        SELECT u.id, u.full_name
        FROM user u
        JOIN user_roles ur ON u.id = ur.user_id
        JOIN role r ON ur.role_id = r.id
        WHERE r.name = 'parent'
        ");
        $parentQuery->execute();
        $parentResult = $parentQuery->get_result();
        $parents = $parentResult->fetch_all(MYSQLI_ASSOC);
        return $parents;
    }
    
    

}
?>