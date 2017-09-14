<?php
//require_once "config.php";
require_once "go.php";
require_once "header.php";
?>
			<div class="content">
				<div id="response"></div>
				
				<?php // If an update message was set prior to a redirect
				// to this page display it and clear the message.
				if (isset($_SESSION['update_message'])) {
					foreach ($_SESSION['update_message'] as $message) {
						print $message;
					}
					unset($_SESSION['update_message']);
				}?>
			
				<p>&laquo; <a href="gotionary.php">GOtionary</a></p>
<?php

try {
	if (empty($_SESSION["AUTH"])) {
		throw new Exception("You must log in (above).");
	} else if(!isAuditor()) {
		throw new Exception("You do not have permission to view this page");
	} else {
		$userName = str_replace(" ", "+", $_GET["name"]);
		global $connection;
		$select = $connection->prepare("SELECT name FROM user WHERE name = :name");
		$select->execute(array(":name" => $userName));
		$userRow = $select->fetchObject();
		$select->closeCursor();
		if (!$userRow)
			throw new Exception("Unknown user or user has no codes.");
		
		$user = new User($userName);
		print "<h2>Codes for user ".Go::getUserDisplayName($userName)."</h2>";
		
		// Get the codes the user can edit
		$codes = $user->getCodes();
		// If there are any, put them in a table with editing options
		if (count($codes) > 0) {
			print_codes_table($codes);
		}
		
		// Get the deleted codes the user created
		$codes = $user->getDeletedCodes();
		// If there are any, put them in a table with editing options
		if (count($codes) > 0) {
			print "<h2>Deleted code created by user ".Go::getUserDisplayName($userName)."</h2>";
			print_codes_table($codes, false);
		}
	}
} catch (Exception $e) {
	error_log($e->getMessage(), 3);
	print "<div class='error'>Error. Please contact ".GO_HELP_HTML."</div>"; 
}

function print_codes_table(array $codes, $exists = true) {
	print "\n<table id='my_codes_table'>
	<tr>
		<th>Go Shortcut</th>
		<th>Aliases</th>
		<th>URL</th>
		<th>Description</th>
		<th>Institution</th>
		<th>Last Updated</th>
		<th>Actions</th>
	</tr>";
	foreach ($codes as $code) {
		$current_aliases = array();
		$aliases = $code->getAliases();
		foreach ($aliases as $thisalias) {
			$current_aliases[] = $thisalias->getName();
		}
		$current_aliases = implode(', ', $current_aliases);
		print "<tr>
			<td>";
		if ($exists)
			print "\t<a href='" . htmlspecialchars($code->getUrl()) . "'>" . $code->getName() . "</a>";
		else
			print $code->getName();
		print "	</td>
			<td>
				" . $current_aliases . "
			</td>
			<td style='max-width: 200px; overflow: hidden'>
				" . htmlspecialchars($code->getUrl()) . "
			</td>
			<td>
				" . htmlspecialchars($code->getDescription()) . "
			</td>
			<td>
				" . $code->getInstitution() . "
			</td>
			<td>
				" . $code->getLastUpdateDate() . "
			</td>
			<td>
				";
		if (isSuperAdmin() && $exists) {
			print "<a class='edit_button' href='update.php?code=" . $code->getName() . "&amp;institution=" . $code->getInstitution() . "&amp;url=" . urlencode(curPageURL()) . "'>";
			print "<input onclick='window.location=\"update.php?code=" . $code->getName() . "&amp;institution=" . $code->getInstitution() . "&amp;url=" . urlencode(curPageURL()) . "\"' type='button' value='Edit Shortcut' /></a>";
		}
		
		if ($exists) {
			print "	
				<a class='edit_button' href='info.php?code=".$code->getName()."'><input type='button' onclick='window.location=\"info.php?code=".$code->getName()."\"' value='Info' /></a>";
		}
		print "\n\t\t\t\t<a class='edit_button' href='details.php?code=".$code->getName()."&amp;institution=".$code->getInstitution()."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><input type='button' value='History' /></a>";
		print
			"</td>
		</tr>";
	}
	print "\n</table>";
}
		
?>

</div> </div> </body> </html>
