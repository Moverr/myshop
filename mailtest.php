<?php
$from = "From: info@itp.nwtdemos.com";
$to = "support@propersupport.com";
$subject = "Hi ";
$body = "TEST";

if (mail($to,$subject,$body,$from)) {
echo "MAIL 001 - OK";
} else {
echo "MAIL FAILED";
}
?>