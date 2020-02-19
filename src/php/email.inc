<?php

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\Exception;

// Load Composer's autoloader
require 'mailer/autoload.php';

function send_email($subject, $msg, $email) {
    // Instantiation and passing `true` enables exceptions
    $mail = new PHPMailer(true);
    $errors = array();
    try {
        //Server settings
        //$mail->SMTPDebug = SMTP::DEBUG_SERVER;                      // Enable verbose debug output
        //$mail->Debugoutput = 'error_log';
        //$mail->isSMTP();                                            // Send using SMTP
        $mail->Host       = 'localhost';             // Set the SMTP server to send through
        //$mail->SMTPAuth   = false;                                   // Enable SMTP authentication
        //$mail->SMTPSecure = 'tls'; 
        //$mail->Username   = 'mailer@pandesalbradart.com';      // SMTP username
        //$mail->Password   = 'm4leb0x';                              // SMTP password
        //$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;         // Enable TLS encryption; `PHPMailer::ENCRYPTION_SMTPS` also accepted
        $mail->Port       = 587;                                    // TCP port to connect to

        //Recipients
        $mail->setFrom('mailer@pandesalbradart.com', 'PandesalBrad');
        $mail->addAddress($email);     // Add a recipient
        $mail->addReplyTo('mailer@pandesalbradart.com', 'PandesalBrad');

        // DKIM Signature
        /*$mail->DKIM_domain = 'pandesalbradart.com';
        $mail->DKIM_private = '/var/www/pandesalbradart/resources/private.key';
        $mail->DKIM_selector = 'pandesal'; //Prefix for the DKIM selector
        $mail->DKIM_passphrase = ''; //leave blank if no Passphrase
        $mail->DKIM_identity = $mail->From;*/

        // Attachments
        /*$mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
        $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name*/

        // Content
        $mail->isHTML(false);                                  // Set email format to HTML
        $mail->Subject = $subject;
        $mail->Body    = $msg;
        //$mail->AltBody = 'This is the body in plain text for non-HTML mail clients';

        $mail->send();
    } catch (Exception $e) {
        array_push($errors, $mail->ErrorInfo);
    }
    return $errors;
}

/*function send_email($subject, $msg, $email) {
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
}*/

?>