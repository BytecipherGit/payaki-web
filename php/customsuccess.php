<?php

// $file_path = 'test.txt';
// Save the content to the text file
// $result = file_put_contents($file_path, "Hello World. Testing!");

// $fp = fopen('php/hello.php', 'w');
// if ($fp) {
//     $bytes_written = fwrite($fp, 'welcome ');
//     if ($bytes_written !== false) {
//         $bytes_written += fwrite($fp, 'to php file write');
//         if ($bytes_written !== false) {
//             echo "Content written to the file successfully.";
//         } else {
//             echo "Failed to write the second part of content.";
//         }
//     } else {
//         echo "Failed to write the first part of content.";
//     }
//     fclose($fp);
// } else {
//     echo "Failed to open the file for writing.";
// }

// use PHPMailer\PHPMailer\PHPMailer;

// require 'jwt-api/PHPMailer/src/Exception.php';
// require 'jwt-api/PHPMailer/src/PHPMailer.php';
// require 'jwt-api/PHPMailer/src/SMTP.php';

// $mail = new PHPMailer(true);
// $mail->Host = 'smtp.gmail.com';
// $mail->SMTPAuth = true;
// $mail->Username = 'jharshita259@gmail.com';
// $mail->Password = 'bfhagppogpishvbq';
// $mail->SMTPSecure = 'tls';
// $mail->Port = 587;
// $mail->setFrom('jharshita259@gmail.com');
// $mail->addAddress('tiwarilalit601@mailinator.com');
// $mail->isHTML(true);
// $mail->Subject = 'Success page';
// $mail->Body = 'Success page';
// if ($mail->send()) {
//     echo "Email sent successfully.";
// } else {
//     echo "Failed to send email.";
// }
global $config, $link;
$successMsg = "Your payment successfully done";
$page = new HtmlTemplate('templates/' .$config['tpl_name'].'/customsuccess.tpl');
$page->SetParameter ('OVERALL_HEADER', create_header($lang['PROFILE']));
$page->SetParameter('SUCCESSMESSAGE', $successMsg);
$page->SetParameter('OVERALL_FOOTER', create_footer());
$page->CreatePageEcho();
exit();
?>