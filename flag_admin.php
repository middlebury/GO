<?php
//functions.php gives us access to the isSuperAdmin function 
require_once "functions.php";
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";
//header.php looks pretty
require_once "header.php";

//COLLECT AND PROCESS THE DATA

try {
	//check if user should see this page
	if (!isSuperAdmin()) {
		die("You do not have permission to view this page");
	}
	//set array to hold results
	$result = array();
	//get the statement object for this select statement
	$select = $connection->query("SELECT code FROM flag");
  //place the results of the select into results
  if ($select != '') {
  	foreach ($select as $row) {
  		$result[] = $row[0];
  	}
  }
  
  //create an array that holds the values (keys)
  //and the number of times they appeard in the results (value)
 	$no_of_codes = array_count_values($result);
	
	//array to hold the output for display
	$output_array = array();
  if ($no_of_codes != '') {
  	//this is the main loop that builds the output array
  	foreach($no_of_codes as $key => $val) {
  		//we already have the codes and the number of time they
  		//have been flagged, so now we want to get the url associated
  		//with the codes
			$select = $connection->prepare("SELECT url FROM code WHERE name = ?");
  		$select->bindValue(1, $key);
  		$select->execute();
  		//put these into url
  		if ($select != '') {
  			foreach ($select as $row) {
  				$url = $row[0];
  			}
  		}
  		//we want to do the same thing for aliases
  		$result = array();
  		$select = $connection->prepare("SELECT name FROM alias WHERE code = ?");
  		$select->bindValue(1, $key);
  		$select->execute();
  		//place the results into results for now
  		//there can be multiple aliases so we want to
  		//process them further
  		if ($select != '') {
  			foreach ($select as $row) {
  				$result[] = $row[0];
  			}
  		}
  		//make an array for aliases into which
  		//we put all the aliases returned
  		$alias = array();
  		if ($result != '') {
  			foreach ($result as $current_alias) {
  				$alias[] = $current_alias;
  			}
  		}
  		//now implode so we have a string
  		//of aliases separared by commas
  		$alias = implode(', ', $alias);
  		
  		//finally we can add a row of items to output array
  		$output_array[] = array($key, $val, $url, $alias);
  		//now we want to sort this on the number of times
  		//the code was flagged inappropriate. We'll use
  		//array_multisort which needs column data so
  		//let's break this up into columns
			if ($output_array != '') {
				foreach ($output_array as $key => $row) {
    			$code[$key]  = $row['0'];
    			$no_of_flags[$key] = $row['1'];
    			$url[$key]  = $row['2'];
    			$alias[$key] = $row['3'];
				}
			}
			//now sort output array based on the number
			//of flags column
  		array_multisort($no_of_flags, SORT_DESC, $output_array);
  	}
  } //end if ($no_of_codes != '') {
  ?>
  
  <!-- GENERATE THE OUTPUT -->
  
  <!-- this is our table of results -->
  <table id="flag_admin_table">
  	<tr>
  		<th>Code</th>
  		<th># of Flags</th>
  		<th>Destination</th>
  		<th>Aliases</th>
  		<th>Actions</th>
  	</tr>
  	<?php
  	if ($output_array != '') {
  		//this is where we print out the cells of the table row
  		//this increment keeps track of the row
  		$i = 0;
  		foreach ($output_array as $value) {
  			print "\n<tr>";
  			//turn appropriate items into links
  			//this incrememnt keeps track of the cells in the row
  			$ii = 0;
  			foreach ($value as $x) {
  				if ($ii == 0) {
  					//change the code to a link to the codes info page
  					$x = '<a href="info.php?code='.$x.'">'.$x.'</a>';
  					print "\n<td>".$x."</td>";
  				} else if ($ii == 2) {
  					//change the destination to a link to the destination URL	
  					$x = '<a href="'.$x.'">'.$x.'</a>';
  					print "\n<td>".$x."</td>";
  					//for anything else just print the value
  				} else {
  					print "\n<td>".$x."</td>";
  				}
  				$ii++;
  			}
  			//this is where we make the last column of Actions
  			print "\n<td class='action_cells'>";
  			//this link takes you to additional details about the flags set for this code
  			print "\n<a href='flag_details.php?code=".$code[$i]."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><input type='button' value='Info' /></a>";
  			//this form submits a delete request for the current flag
  			print "\n<form name='clear_flags' action='flag_clear.php' method='post'>";
  			print '<input type="hidden" name="xsrfkey" value="'. $_SESSION['xsrfkey']. '" />';
  			print "\n<input type='hidden' value='".$code[$i]."' name='code' />";
  			print "\n<input type='submit' value='Clear Flags' />";
  			print "\n</form>";
  			print "\n</td>";
  			print "\n</tr>";
  			$i++;
  		}	
  	}
  	?>
  	</table>
  <?php

//now catch any exceptions
} catch (Exception $e) {
	throw $e;
}

require_once "footer.php";
