<?php
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";
//go_functions.php gives us access to the isSuperAdmin function 
require_once "go_functions.php";
require_once "header.php";
?>

<div class="content">
	<div id="response"></div>

<?php
//COLLECT AND PROCESS THE DATA FOR FLAGS

try {
	//check if user should see this page
	if (!isAuditor()) {
		die("You do not have permission to view this page");
	}
	//get the statement object for this select statement
	$select = $connection->prepare("SELECT * FROM flag WHERE code = ? AND institution = ?");
  $select->bindValue(1, str_replace(" ", "+", $_GET["code"]));
  $select->bindValue(2, $_GET["institution"]);
	$select->execute();
	?>
	
	<!-- this is our table of flags -->
	<?php if (count($select->fetchAll())) { ?>
  	<h2 class="flag_detail_header">Flags for go/<?php print htmlentities($_GET["code"]); ?></h2>
  	<table class="flag_admin_table">
  		<tr>
  			<th>Code</th>
  			<th>User</th>
  			<th>Comment</th>
  			<th>IP Address</th>
  			<th>Timestamp</th>
  		</tr>
  		<?php
  		// We already execute the query earlier for a conditional
  		// so we need to execute it again in order to fetch it.
  		// alternately we could have put the resutls in a var
  		$select->execute();
  		foreach ($select->fetchAll() as $row) {
  			print "\n<tr>";
  			print "\n<td>".$row['code']."</td>";
  			if ($row['user']) {
  				print "\n<td>".GoAuthCas::getName($row['user'])."</td>";
  			} else {
  				print "\n<td></td>";
  			}
  			print "\n<td>".$row['comment']."</td>";
  			print "\n<td>".$row['ipaddress']."</td>";
  			print "\n<td>".$row['timestamp']."</td>";
  			print "\n</tr>";
  		} //end foreach ($select->fetchAll() as $row) { 
  		?>
  	</table>
  <?php } //end if (count($select->fetchAll()) { ?>

	<?php
	//COLLECT AND PROCESS THE DATA FOR LOGS

	//get the statement object for this select statement
	$select = $connection->prepare("SELECT * FROM log WHERE code = ? AND institution = ?");
  $select->bindValue(1, str_replace(" ", "+", $_GET["code"]));
  $select->bindValue(2, $_GET["institution"]);
	$select->execute();
  ?>
  	
  <!-- this is our table of logs -->
  <h2 class="flag_detail_header">Logs for go/<?php print htmlentities($_GET["code"]); ?></h2>
  <table class="flag_admin_table">
  	<tr>
  		<th>Timestamp</th>
  		<th>Alias</th>
  		<th>Description</th>
  		<th>Display Name</th>
  	</tr>
  	<?php
  		//this is where we print out the cells of the table row
  		$results = $select->fetchAll();
  		if (count($results)) {
  			foreach ($results as $row) {
  				print "\n<tr>";
  				print "\n<td>".$row['tstamp']."</td>";
  				print "\n<td>".$row['alias']."</td>";
  				print "\n<td>".htmlentities($row['description'])."</td>";
  				print "\n<td>".$row['user_display_name']."</td>";
  				print "\n</tr>";
  			}
  		} else {
  			print "\n<tr>";
  			print "\n<td colspan='5' class='center'>No results</td>";
  			print "\n</tr>";
  		}
  	?>
  	</table>
  	
  <?php

//now catch any exceptions
} catch (Throwable $e) {
	throw $e;
} //end catch (Throwable $e) {

require_once "footer.php";
