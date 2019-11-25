<?php

include "sqlConn.inc";

include "imageUtils.inc";
$data = array();
if (is_user_admin()) {
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == 'GET') {

        $parameters = array();
        // Create SQL Query
        $sql = "SELECT * FROM users";

        $data = array();
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute($parameters);

            // output data of each row
            if ($stmt->rowCount() > 0) {
                foreach ($stmt as $row) {
                    $data[] = $row;
                }
            }
        }
        // Close Connection
        $conn = null;
    }
}

echo json_encode($data);

?>