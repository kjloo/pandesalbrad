<?php

session_start();

include "imageUtils.inc";
if (is_user_admin()) {
    if (isset($_POST['update']) && !empty($_POST['productID']) && !empty($_POST['pname']) && !empty($_POST['iname']) && !empty($_POST['format']) && !empty($_POST['price']) && !empty($_POST['category'])) {

        include "sqlConn.inc";
        $productID = $_POST['productID'];
        $pname = $_POST['pname'];
        $iname = $_POST['iname'];
        $format = $_POST['format'];
        $price = $_POST['price'];
        $category = $_POST['category'];

        $choices = $_POST['itemChoices'];

        // Need to get old image name to check if it changed
        $oldiname = get_image_name_from_db($productID);

        // Check if file data was uploaded and update.
        if (isset($_FILES['uploadedImage']) && $_FILES['uploadedImage']['size'] > 0) {
            // Delete current Image
            delete_image($oldiname);
            // Upload new Image
            $errors = upload_image($iname);
            if(empty($errors) != true) {
                header("Location: ../upload.html?upload=fail&message=" . join(",", $errors));
                exit();
            }
        } else if ($iname != $oldiname) {
            $errors = move_image($oldiname, $iname);
          
            if(empty($errors) != true) {
                header("Location: ../upload.html?upload=fail&message=" . join(",", $errors));
                exit();
            }
        }

        // Begin Transcation
        $conn->beginTransaction();

        // Update product info in database
        // Create SQL Query
        $sql = "UPDATE products SET Image = ?, Name = ?, CollectionID = ? WHERE ProductID = ?";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$iname, $pname, $category, $productID]);
            // Error Check?
            $stmt->closeCursor();
        }

        if (!empty($choices)) {
            foreach ($choices as $choiceID) {
                // Check if item available in table
                $sql = "SELECT * FROM items WHERE ProductID = ? AND FormatID = ? AND ChoiceID = ?";
                if($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$productID, $format, $choiceID]);
                    $exists = $stmt->rowCount() > 0;
                    // Error Check?
                    $stmt->closeCursor();
                }
                if ($exists) {
                    // Create SQL Query
                    $sql = "UPDATE items SET Price = ? WHERE ProductID = ? AND FormatID = ? AND ChoiceID = ?";
                    if($stmt = $conn->prepare($sql)) {
                        $stmt->execute([$price, $productID, $format, $choiceID]);
                        // Error Check?
                        $stmt->closeCursor();
                    }
                } else {
                    $sql = "INSERT INTO items (ProductID, FormatID, ChoiceID, Price) VALUES (?, ?, ?, ?)";
                    if($stmt = $conn->prepare($sql)) {
                        $stmt->execute([$productID, $format, $choiceID, $price]);
                        // Error Check?
                        $stmt->closeCursor();
                    }
                }
            }   
        } else {
            // Check if item available in table
            $sql = "SELECT * FROM items WHERE ProductID = ? AND FormatID = ?";
            if($stmt = $conn->prepare($sql)) {
                $stmt->execute([$productID, $format]);
                $exists = $stmt->rowCount() > 0;
                // Error Check?
                $stmt->closeCursor();
            }
            if ($exists) {
                // Create SQL Query
                $sql = "UPDATE items SET Price = ? WHERE ProductID = ? AND FormatID = ?";
                if($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$price, $productID, $format]);
                    // Error Check?
                    $stmt->closeCursor();
                }
            } else {
                $sql = "INSERT INTO items (ProductID, FormatID, Price) VALUES (?, ?, ?)";
                if($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$productID, $format, $price]);
                    // Error Check?
                    $stmt->closeCursor();
                }
            }
        }

        $conn->commit();
        $conn = null;
        header("Location: ../upload.html?upload=success&message=File Update Successful.");
    } else if (isset($_POST['upload']) && isset($_FILES['uploadedImage']) && !empty($_POST['pname']) && !empty($_POST['iname']) && !empty($_POST['format']) && !empty($_POST['price']) && !empty($_POST['category'])) {

        include "sqlConn.inc";
        $pname = $_POST['pname'];
        $iname = $_POST['iname'];
        $format = $_POST['format'];
        $price = $_POST['price'];
        $category = $_POST['category'];

        $choices = $_POST['itemChoices'];

        // Copy file to server product directory
        $errors = upload_image($iname);
          
        if (empty($errors) != true) {
            header("Location: ../upload.html?upload=fail&message=" . join(",", $errors));
            exit();
        }

        // Begin SQL Transaction
        $conn->beginTransaction();
        // Create SQL Query
        // First insert product
        $sql = "INSERT INTO products (Image, Name, CollectionID) VALUES (?, ?, ?)";
        if($stmt = $conn->prepare($sql)) {
            $stmt->execute([$iname, $pname, $category]);
            // Error Check?
            $stmt->closeCursor();
        }
        $productID = $conn->lastInsertId();
        if (!empty($choices)) {
            foreach ($choices as $choiceID) {
                $sql = "INSERT INTO items (ProductID, FormatID, ChoiceID, Price) VALUES (?, ?, ?, ?)";
                if($stmt = $conn->prepare($sql)) {
                    $stmt->execute([$productID, $format, $choiceID, $price]);
                    // Error Check?
                    $stmt->closeCursor();
                }
            }
        } else {
            $sql = "INSERT INTO items (ProductID, FormatID, Price) VALUES (?, ?, ?)";
            if($stmt = $conn->prepare($sql)) {
                $stmt->execute([$productID, $format, $price]);
                // Error Check?
                $stmt->closeCursor();
            }
        }
        $conn->commit();

        $conn = null;
        header("Location: ../upload.html?upload=success&message=File Upload Successful.");
    } else {
        header("Location: ../upload.html?upload=fail&message=Incorrect Parameters.");
        exit();
    }
} else {
    header("Location: ../upload.html?upload=fail&message=Insufficient Permissions.");
    exit();
}

?>