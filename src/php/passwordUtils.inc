<?php

function validate_password($password, $password2) {
    $errors = array();
    if ($password != $password2) {
        $errors[] = "Passwords do not match";
    } 
    if (strlen($password) < 10) {
        $errors[] = "Password must contain at least 10 characters";
    }
    if (!preg_match("/[0-9]/", $password)) {
        $errors[] = "Password must include at least one number";
    }
    if (!preg_match("/[a-z]/", $password)) {
        $errors[] = "Password must include at least lowercase one letter";
    }
    if (!preg_match("/[A-Z]/", $password)) {
        $errors[] = "Password must include at least uppercase one letter";
    }
    if (!preg_match("/\W/", $password)) {
        $errors[] = "Password must include at least one special character";
    }
    return $errors;
}

?>