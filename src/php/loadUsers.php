<?php

include "sqlConn.inc";

include "imageUtils.inc";
$data = array();
if (is_user_admin()) {
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == 'GET') {
        $parameters = array();
        // Create SQL Query
        $sqlArr = ["SELECT UserID, Username, Firstname, Lastname, Email, Activated, RoleID, DATE_FORMAT(SignupDate, '%M %d, %Y') AS SignupDate FROM users"];

        if (!empty($_SERVER['PATH_INFO'])) {
            $username = $_SERVER['PATH_INFO'];
            array_push($parameters, "%$username%");
            array_push($sqlArr, "WHERE Username LIKE ?");
        }

        $data = array();

        $sql = join(" ", $sqlArr);
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