<?php

function send_email($subject, $msg, $email) {
    $headers = "Reply-To: PandesalBrad <mail-daemon@pandesalbradart.com>\r\n";
    $headers .= "Return-Path: PandesalBrad <mail-daemon@pandesalbradart.com>\r\n";
    $headers .= "From: PandesalBrad <mail-daemon@pandesalbradart.com>\r\n";
    $headers .= "Organization: PandesalBrad\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/plain; charset=iso-8859-1\r\n";
    $headers .= "X-Priority: 3\r\n";
    $headers .= "X-Mailer: PHP". phpversion() ."\r\n";

    // Use wordwrap() if lines are longer than 70 characters
    $msg = wordwrap($msg, 70);

    // Send email
    return mail($email, $subject, $msg, $headers);
}

?>