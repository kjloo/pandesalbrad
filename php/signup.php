<?php

session_start();

if (isset($_POST['signup']) && !empty($_POST['fname']) && !empty($_POST['lname']) && !empty($_POST['username']) && !empty($_POST['password']) && !empty($_POST['email'])) {

    include "sqlConn.inc";


    $fname = mysqli_real_escape_string($conn, $_POST['fname']);
    $lname = mysqli_real_escape_string($conn, $_POST['lname']);
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $role = "Customer";

    // Check if username is taken
    $sql = "SELECT Username FROM users WHERE Username = ?";

    if($stmt = mysqli_prepare($conn, $sql)) {
		mysqli_stmt_bind_param($stmt, "s", $username);

		mysqli_stmt_execute($stmt);

		$result = mysqli_stmt_get_result($stmt);
		
		mysqli_stmt_close($stmt);

		// Get Result
		if ($result->num_rows > 0) {
		    // Username already exists, return error
		    header("Location: ../signup.html?signup=taken");
		    exit();
		} else {
	        // Create SQL Query
            $sql = "INSERT INTO users (Firstname, Lastname, Username, Password, Email, RoleID) SELECT ?, ?, ?, ?, ?, RoleID FROM roles WHERE Role = '$role'";
	
	        if($stmt = mysqli_prepare($conn, $sql)) {
	            $hash = password_hash($password, PASSWORD_BCRYPT);
                mysqli_stmt_bind_param($stmt, "sssss", $fname, $lname, $username, $hash, $email);

                mysqli_stmt_execute($stmt);
		
                mysqli_stmt_close($stmt);

                header("Location: ../index.html?signup=success");
            }
		}
	}

} else {
	header("Location: ../index.html?signup=error");
	exit();
}


?>