<?php
//go_functions.php gives us access to the isSuperAdmin function 
require_once "go_functions.php";
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";
//Mail.php is the PEAR script that includes the mail class for sending mail
require_once "Mail.php";
//mime.php includes support for mime mail
require_once "Mail/mime.php";

//check for xss attempt
if ($_POST['xsrfkey'] != $_SESSION['xsrfkey']) {
	die("Session variables do not match");
}

//validation of reason field
if (!Code::isValidDescription($_POST['notes'])) {
			$_SESSION['update_message'][] = "<p class='update_message_failure'>The notes you entered contain invalid characters. The characters allowed are letters, numbers, and common puntcuation. Please make adjustments and try again.</p>";
			// Redirect to originating location
			die(header("location: flag_admin.php"));
}

// This script should only run for superadmins
if (!isSuperAdmin()) {
		die("You do not have permission to view this page");
	}

try {
	//get the statement object for this update statement
	//set who completed the flag and when it was completed
	$update = $connection->prepare("UPDATE flag SET completed = ?, completed_on = NOW(), notes = ? WHERE code = ? AND institution = ? AND completed = '0'");
	$update->bindValue(1, $_SESSION["AUTH"]->getName());
	$update->bindValue(2, $_POST['notes']);
  $update->bindValue(3, $_POST['code']);
  $update->bindValue(4, $_POST['institution']);
	$update->execute();
		
	//log completion
	Go::log("Flag as inappropriate flag was completed", $_POST['code']);
	
	//send mail to each go superadmin indicating that this 
  //code has been flagged using the goAdmin array
  //from config.php to get the emails of each admin
  foreach ($goAdmin as $current_admin) {
  $to[] = GoAuthCas::getEmail($current_admin);
  }
  $to = implode(', ', $to);
  $headers['From'] = GO_ALERTS_EMAIL_NAME . ' <' . GO_ALERTS_EMAIL_ADDRESS . '>';
  $headers['Subject'] = 'The flagged GO code '.$_POST["code"].' was administered by '.$_SESSION["AUTH"]->getName().'.';
  $mime = new Mail_mime;
  if (isset($_SESSION["AUTH"])) {
    $text = 'The flagged GO code (aka. link) "'.$_POST["code"].'" was completed by '.$_SESSION["AUTH"]->getName().'. History may be viewed via the admin interface ('.$institutions[$_POST["institution"]]['base_uri'].'flag_admin.php).

- The GO application';
	  $html = 'The flagged GO code (aka. link) "<a href="'.$institutions[$_POST["institution"]]['base_uri'].'info.php?code='.$_POST["code"].'">'.$_POST["code"].'</a>" was completed by '.$_SESSION["AUTH"]->getName().'. History may be viewed via the <a href="'.$institutions[$_POST["institution"]]['base_uri'].'flag_admin.php">admin interface</a>.<br /><br />

- The GO application';	
	}
	$mime->setTXTBody($text);
	$mime->setHTMLBody($html);
	//get MIME formatted message headers and body
	$body = $mime->get();
	$headers = $mime->headers($headers);
  $message = Mail::factory('mail');
  //foreach ($to as $current_address) {
  $message->send($to, $headers, $body);
	
//now catch any exceptions
} catch (Exception $e) {
	throw $e;
} //end catch (Exception $e) {

//redirect on completion
header("location: flag_admin.php?code=".$_POST['code']);
