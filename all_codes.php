<?php
// Require go_functions so we have access to function isSuperAdmin
require_once "go_functions.php";
require_once "header.php";
require_once "admin_nav.php";
?>

<div class="content">
	<div id="response"></div>

	<h2>All Codes (click to edit)</h2>

<?php
// Show all codes the currently logged in user may admin
// What codes may the current user admin?

$user = new User($_SESSION["AUTH"]->getId());

// Superadmin may admin all codes so show all
if (isSuperAdmin($user->getName())) {

	$select = $connection->prepare("
  SELECT
  	name,
  	institution
  FROM
  	code
  ORDER BY
  	name");
  $select->execute();
	
	print "<table>";
	//
	foreach ($select->fetchAll() as $row) {
		//$codes[$row['institution'] . "/" . $row['name']] = new Code($row['name'], $row['institution']);
		print "<tr>
		<td>
			<a href='update2.php?code=" . $row['name'] . "&amp;institution=" . $row['institution'] . "'>" . $row['name'] . "</a>
		</td>
		<td>
			" . $row['institution'] . "
		</td>";
	}
	print "</table>";
} else {
	die("You are not authorizd to view this page.");
}