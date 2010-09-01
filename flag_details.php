<?php
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";
require_once "header.php";

//COLLECT AND PROCESS THE DATA

try {
	//set array to hold results
	$result = array();
	//get the statement object for this select statement
	$select = $connection->prepare("SELECT * FROM flag WHERE code = ?");
  $select->bindValue(1, htmlentities($_GET['code']));
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
  
  <!-- this is our table of results -->
  <table id="flag_admin_table">
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

//now catch any exceptions
} catch (Exception $e) {
	throw $e;
} //end catch (Exception $e) {

require_once "footer.php";
