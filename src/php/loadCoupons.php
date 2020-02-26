<?php

include "sqlConn.inc";
require_once "adminUtils.inc";

$data = array();
if (is_user_admin()) {
    $method = $_SERVER['REQUEST_METHOD'];
    if ($method == 'GET') {
        $parameters = array();
        // Create SQL Query
        $sqlArr = ["SELECT * FROM coupons"];

        if (!empty($_GET['code'])) {
            $code = $_GET['code'];
            array_push($parameters, "%$code%");
            array_push($sqlArr, "WHERE Code LIKE ?");
        } else if (!empty($_GET['status'])) {
            $status = intval($_GET['status'] === 'true' ? True : False);
            array_push($parameters, "$status");
            array_push($sqlArr, "WHERE Active = ?");
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

echo json_encode($data, JSON_NUMERIC_CHECK);

?>