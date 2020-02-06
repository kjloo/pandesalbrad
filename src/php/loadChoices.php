<?php

$data = array();
if (!empty($_GET["format"])) {

    include "sqlConn.inc";

    $format = $_GET["format"];

    // Create SQL Query
    $sql = "SELECT c.Name, c.ChoiceID FROM format_options AS fo
        INNER JOIN options AS o ON fo.OptionID = o.OptionID
        INNER JOIN choices AS c ON o.OptionID = c.OptionID
        WHERE fo.FormatID = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$format]);

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

echo json_encode($data);

?>