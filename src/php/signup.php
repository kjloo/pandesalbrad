<?php

session_start();

if (isset($_POST['signup']) && !empty($_POST['fname']) && !empty($_POST['lname']) && !empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['email'])) {

    include "sqlConn.inc";


    $fname = $_POST['fname'];
    $lname = $_POST['lname'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $email = $_POST['email'];
    $role = "Customer";

    // Check if username is taken
    $sql = "SELECT Username FROM users WHERE Username = ?";

    if($stmt = $conn->prepare($sql)) {
        $stmt->execute([$username]);

		// Get Result
		if ($stmt->rowCount() > 0) {
		    // Username already exists, return error
		    header("Location: ../signup.html?signup=taken");
		} else {
	        // Create SQL Query
            $sql = "INSERT INTO users (Firstname, Lastname, Username, Password, Email, RoleID) SELECT ?, ?, ?, ?, ?, RoleID FROM roles WHERE Role = '$role'";
	
	        if($stmt = $conn->prepare($sql)) {
	            $hash = password_hash($password, PASSWORD_BCRYPT);
                $stmt->execute([$fname, $lname, $username, $hash, $email]);
                // Error check?

                header("Location: ../index.html?signup=success");
            }
		}
	}
    // Close connection
    $conn = null;

} else {
	header("Location: ../index.html?signup=error");
	exit();
}


?>