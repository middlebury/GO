<?php
// Require go_functions so we have access to function isSuperAdmin
require_once "go_functions.php";
require_once "header.php";
require_once "admin_nav.php";
?>

<div class="content">
	<div id="response"></div>

<?php

$user = new User($_SESSION["AUTH"]->getId());

// If an update message was set prior to a redirect
// to this page display it and clear the message.
if (isset($_SESSION['update_message'])) {
	foreach ($_SESSION['update_message'] as $message) {
		print $message;
	}
	unset($_SESSION['update_message']);
}

print "<h2>All Codes</h2>";

// Only superadmin may admin all codes so show all
if (isSuperAdmin($user->getName())) {

	$select = $connection->prepare("
  SELECT
  	name,
  	institution,
  	aliases,
  	description,
  	url
  FROM
  	code
  	LEFT JOIN
  			(SELECT
  				code,
  					GROUP_CONCAT(name SEPARATOR ', ') AS
  				aliases
  			FROM
  				alias
  			GROUP BY
  				code)
  		AS
  	grouped_alias
  		ON name = grouped_alias.code
  ORDER BY
  	name");
  $select->execute();

	print "<table id='my_codes_table'>
		<tr>
			<th>Go Shortcut</th>
			<th>Description</th>
			<th>Aliases</th>
			<th>Institution</th>
			<th>Actions</th>
		</tr>";
	//
	foreach ($select->fetchAll() as $row) {
		//$codes[$row['institution'] . "/" . $row['name']] = new Code($row['name'], $row['institution']);
		print "
		<tr>
			<td>
				<a href='" . htmlentities($row['url']) . "'>" . $row['name'] . "</a>
			</td>
			<td>
				" . $row['description'] . "
			</td>
			<td>
				" . $row['aliases'] . "
			</td>
			<td>
				" . $row['institution'] . "
			</td>
			<td>

				<a class='edit_button' href='update.php?code=" . $row['name'] . "&amp;institution=" . $row['institution'] . "&amp;url=" . urlencode(curPageURL()) . "'><input type='button' value='Edit Shortcut' /></a>

				<a class='edit_button' href='info.php?code=".$row['name']."'><input type='button' value='Info' /></a>
				<a class='edit_button' href='details.php?code=".$row['name']."&amp;institution=".$row['institution']."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><input type='button' value='History' />

				</a>
			</td>
		</tr>
		";
	}
	print "</table>";
} else {
	die("You are not authorized to view this page.");
}

require_once "footer.php";
