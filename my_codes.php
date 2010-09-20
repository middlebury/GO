<?php
// Require go_functions so we have access to function isSuperAdmin
require_once "go_functions.php";
require_once "header.php";
require_once "admin_nav.php";
?>

<div class="content">
	<div id="response"></div>

<?php
// Show all codes the currently logged in user may admin

// What codes may the current user admin?
$user = new User($_SESSION["AUTH"]->getId());
if (isSuperAdmin($user->getName())) {
	
	$select = $connection->prepare("
  SELECT
  	name,
  	institution
  FROM
  	code");
  $select->execute();

	foreach ($select->fetchAll() as $row) {
		$codes[$row['institution'] . "/" . $row['name']] = new Code($row['name'], $row['institution']);
	}
} else {
	$codes = $user->getCodes();
}

/*print "Is super admin?: <br />";
var_dump(isSuperAdmin($user->getName()));
print "<br />";
print "<br />";

print "UID: <br />";
var_dump($user->getName());
print "<br />";
print "<br />";

print "Codes: <br />";
var_dump($codes);
print "<br />";
print "<br />";*/

if (count($codes) > 0) {
	print "<p>";
	foreach ($codes as $name => $code) {
		print "<a href='update2.php?code=" . $code->getName() . "&amp;institution=" . $code->getInstitution() . "'>" . $code->getName() . "</a><br />";
	}
	print "</p>";
}

?>


<?php
require_once "footer.php";
?>