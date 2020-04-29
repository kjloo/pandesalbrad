<?php

session_start();

require_once "adminUtils.inc";
$data = array();
if (is_user_admin()) {
    // Create sql command to default products
    include "sqlConn.inc";
    $sql = "INSERT INTO items(ProductID, Price, FormatID, ChoiceID) (SELECT p.ProductID, f.DefaultPrice, f.FormatID, c.ChoiceID FROM products AS p LEFT JOIN formats AS f ON True LEFT JOIN format_options AS fo ON f.FormatID = fo.FormatID LEFT JOIN choices AS c ON fo.OptionID = c.OptionID ORDER BY ProductID) ON DUPLICATE KEY UPDATE Price = DefaultPrice";
    if($stmt = $conn->prepare($sql)) {
        $stmt->execute();
        // Error Check?
        //$stmt->closeCursor();
    }

    $data["Message"] = "Fleshed Out Store";
} else {
    $data["Message"] = "Insufficient Permissions";
}

echo json_encode($data);

?>