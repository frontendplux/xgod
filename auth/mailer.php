<?php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer/src/Exception.php';
require 'PHPMailer/src/PHPMailer.php';
require 'PHPMailer/src/SMTP.php';
function sendmail($to,$subject,$message){
  $from  = "shoplenca@shoplenca.com"; 
  $namefrom = "shoplenca";
  $mail = new PHPMailer();
  $mail->SMTPDebug = 0;
  $mail->CharSet = 'UTF-8';
  $mail->isSMTP();
  $mail->SMTPAuth   = true;
  $mail->Host   = "server53.web-hosting.com";  
  $mail->Port       = 465;
  $mail->Username   = $from;
  $mail->Password   = "Qwerty252.";
  $mail->SMTPSecure = "ssl";
  $mail->setFrom($from,$namefrom);
  $mail->addCC($from,$namefrom);
  $mail->Subject  = $subject;
  $mail->isHTML();
//   $mail->addAttachment('img/passionflames.png', 'passionflames.png');
  $mail->Body = $message;
  $mail->AltBody  = '';
  $mail->addAddress($to,'shoplenca');
  return $mail->send();
}
// sendmail("info@vsv.ng","verification mail","<b>how are you today</b>");
?>
