<?php
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";
//go_functions.php gives us access to getRealIpAddr() function
require_once "go_functions.php";
//Mail.php is the PEAR script that includes the mail class for sending mail
require_once "Mail.php";
//mime.php includes support for mime mail
require_once "Mail/mime.php";

//check for xss attempt
if ($_POST['xsrfkey'] != $_SESSION['xsrfkey']) {
	die("Session variables do not match");
}

// Comment is a required field if submitting a flag. If it's not
// filled in, set a session var, stop executing, and return to the
// info page.
if (REASON_FOR_FLAGGING_REQUIRED == true) {
	if ($_POST['flag_comment'] == '') {
		$_SESSION['comment_required'] = true;
		$_SESSION['update_message'][] = "<p class='update_message_failure'>Reason is a required field. Please fill in the reason the shortcut is being flagged.</p>";
		die(header("location: info.php?code=".$_POST['code']));
	}
}

//try to do this and catch the error if there is an issue
try {
	//get the statement object for this insert statement
  $insert = $connection->prepare("INSERT INTO flag (code, user, ipaddress, institution, url, comment) VALUES (?, ?, ?, ?, ?, ?)");
  
  //we want to add the current code to the session array
  //"flagged" so we know the user has flagged this code
  $_SESSION['flagged'][] = $_POST["code"];
  
  
  //bind the values represented by the "?" in the statement
  //first bind code
  $insert->bindValue(1, $_POST["code"]);
  if (isset($_SESSION["AUTH"])) {
  	//bind the logged in user
  	$insert->bindValue(2, $_SESSION["AUTH"]->getId());
  } else {
  	//otherwise just leave the user field blank
  	$insert->bindValue(2, '');
  }
  //bind the ipaddress
  $insert->bindValue(3, getRealIpAddr());
  
  //bind the institution
  $insert->bindValue(4, $_POST["institution"]);
  
  //bind the url
  $insert->bindValue(5, $_POST["url"]);
  
  //bind the comment
  $insert->bindValue(6, $_POST["flag_comment"]);
  
  //finally execute the statement
  $insert->execute();
  
  //send mail to each go superadmin indicating that this 
  //code has been flagged using the goAdmin array
  //from config.php to get the emails of each admin
  foreach ($goAdmin as $current_admin) {
  $to[] = GoAuthCas::getEmail($current_admin);
  }
  $to = implode(', ', $to);
  $headers['From'] = GO_ALERTS_EMAIL_NAME . ' <' . GO_ALERTS_EMAIL_ADDRESS . '>';
  $headers['Subject'] = 'The go code '.$_POST["code"].' was flagged as linking to inappropriate content.';
  $mime = new Mail_mime;
  if (isset($_SESSION["AUTH"])) {
  $text = 'The GO code (aka. link) "'.$_POST["code"].'" was flagged by '.$_SESSION["AUTH"]->getName().' from '.getRealIpAddr().' as linking to inappropriate content. Please administer this flag via the admin interface ('.$institutions[$_POST["institution"]]['base_uri'].'flag_admin.php).

- The GO application';
	$html = 'The GO code (aka. link) "<a href="'.$institutions[$_POST["institution"]]['base_uri'].'info.php?code='.$_POST["code"].'">'.$_POST["code"].'</a>" was flagged by '.$_SESSION["AUTH"]->getName().' from '.getRealIpAddr().' as linking to inappropriate content. Please administer this flag via the <a href="'.$institutions[$_POST["institution"]]['base_uri'].'flag_admin.php">admin interface</a>.<br /><br />

- The GO application';
} else {
	$text = 'The GO code (aka. link) "'.$_POST["code"].'" was flagged by Anon from '.getRealIpAddr().' as linking to inappropriate content. Please administer this flag via the admin interface ('.$institutions[$_POST["institution"]]['base_uri'].'flag_admin.php).

- The GO application';
	$html = 'The GO code (aka. link) "<a href="'.$institutions[$_POST["institution"]]['base_uri'].'info.php?code='.$_POST["code"].'">'.$_POST["code"].'</a>" was flagged by Anon from '.getRealIpAddr().' as linking to inappropriate content. Please administer this flag via the <a href="'.$institutions[$_POST["institution"]]['base_uri'].'flag_admin.php">admin interface</a>.<br /><br />

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
  //}
//now catch any exceptions
} catch (Exception $e) {
	throw $e;
}

//redirect on completion
header("location: info.php?code=".$_POST['code']);
?>



