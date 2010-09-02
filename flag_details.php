<?php
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";
//functions.php gives us access to the isSuperAdmin function 
require_once "functions.php";
require_once "header.php";
?>

<div class="content">
	<div id="response"></div>

<?php
//COLLECT AND PROCESS THE DATA FOR FLAGS

try {
	//check if user should see this page
	if (!isSuperAdmin()) {
		die("You do not have permission to view this page");
	}
	//set array to hold results
	$result = array();
	//get the statement object for this select statement
	$select = $connection->prepare("SELECT * FROM flag WHERE code = ?");
  $select->bindValue(1, str_replace(" ", "+", $_GET["code"]));
	$select->execute();
  //place the results of the select into results
  if ($select != '') {
  	foreach ($select as $row) {
  		$result[] = $row;
  	}
  }
	
	//array to hold the output for display
	$output_array = array();
  	if ($result != '') {
  		foreach ($result as $row) {  		
  		//finally we can add a row of items to output array
  		$output_array[] = array($row['code'], $row['user'], $row['ipaddress'], $row['timestamp']);
  	}
  }
  ?>
  
  <!-- GENERATE THE OUTPUT -->
  
  <!-- this is our table of flags -->
  <h2 class="flag_detail_header">Flags for this Code</h2>
  <table class="flag_admin_table">
  	<tr>
  		<th>Code</th>
  		<th>User</th>
  		<th>IP Address</th>
  		<th>Timestamp</th>
  	</tr>
  	<?php
  	if ($output_array != '') {
  		//this is where we print out the cells of the table row
  		foreach ($output_array as $value) {
  			print "\n<tr>";
  			foreach ($value as $x) {
  					print "\n<td>".$x."</td>";
  				}
  			print "\n</tr>";
  			}
  		}
  	?>
  	</table>

	<?php
	//COLLECT AND PROCESS THE DATA FOR LOGS

	//set array to hold results
	$result = array();
	//get the statement object for this select statement
	$select = $connection->prepare("SELECT * FROM log WHERE code = ?");
  $select->bindValue(1, str_replace(" ", "+", $_GET["code"]));
	$select->execute();
  //place the results of the select into results
  if ($select != '') {
  	foreach ($select as $row) {
  		$result[] = $row;
  	}
  }
	
	//array to hold the output for display
	$output_array = array();
  	if ($result != '') {
  		foreach ($result as $row) {  		
  		//finally we can add a row of items to output array
  		$output_array[] = array($row['tstamp'], $row['alias'], $row['description'], $row['user_id'], $row['user_display_name']);
  	}
  }
  ?>
  
  <!-- GENERATE THE OUTPUT -->
  	
  <!-- this is our table of logs -->
  <h2 class="flag_detail_header">Logs for this Code</h2>
  <table class="flag_admin_table">
  	<tr>
  		<th>Timestamp</th>
  		<th>Alias</th>
  		<th>Description</th>
  		<th>User ID</th>
  		<th>Display Name</th>
  	</tr>
  	<?php
  	if ($output_array != array()) {
  		//this is where we print out the cells of the table row
  		foreach ($output_array as $value) {
  			print "\n<tr>";
  			foreach ($value as $x) {
  					print "\n<td>".$x."</td>";
  				}
  			print "\n</tr>";
  			}
  		} else {
  			print "\n<tr>";
  			print "\n<td colspan=5 class='center'>No results</td>";
  			print "\n</tr>";
  		}
  	?>
  	</table>
  	
  <?php

//now catch any exceptions
} catch (Exception $e) {
	throw $e;
} //end catch (Exception $e) {

require_once "footer.php";
