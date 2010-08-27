<?php
require_once "header.php";
//echo session_name();
//echo session_id();
//var_dump($_POST);
//var_dump($_SESSION);
//print $_SESSION["AUTH"]->getId();
/*if ($_POST['user_id'] == $_SESSION["AUTH"]->getId()) {
	print "<p>All Good</p>";
} else if ($_POST['user_id'] != $_SESSION["AUTH"]->getId()) {
	print "<p>Not Good</p>";
} else {
	print "<p>:P</p>";
	}*/
if ($_POST['xsrfkey'] == $_SESSION['xsrfkey']) {
	print "<p>All Good</p>";
} else if ($_POST['xsrfkey'] != $_SESSION['xsrfkey']) {
	print "<p>Not Good</p>";
} else {
	print "<p>:P</p>";
	}
?>

