<?php
//functions.php gives us access to the isSuperAdmin function 
require_once "functions.php";
//go.php handles the session and xss check for admin
//pages and pages where a session is necessary
require_once "go.php";
//header.php looks pretty
require_once "header.php";
require_once "admin_nav.php";
?>

<div class="content">
	<div id="response"></div>

<?php
//Create a table of codes so we know which
//ones have been flagged and how many times

try {
	//Keep non-admins out of this page
	if (!isSuperAdmin()) {
		die("You do not have permission to view this page");
	}
	//We want to know the code, the number of times flagged
	//the destination, and any aliases
  $select = $connection->prepare("
  SELECT
  	flag.code,
  	COUNT(flag.code) AS num_flags,
  	aliases, code.url,
  	code.institution
  FROM
  	flag
  	LEFT JOIN
  			(SELECT
  				code,
  				GROUP_CONCAT(name SEPARATOR ', ') AS aliases
  			FROM alias
  			GROUP BY code)
  		AS grouped_alias ON flag.code = grouped_alias.code
  	LEFT JOIN code ON flag.code = code.name AND flag.institution = code.institution 
  GROUP BY code 
  ORDER BY num_flags DESC;");
  $select->execute();

  ?>
	
	<!-- Here is the table -->
  <table class="flag_admin_table">
  	<tr>
  		<th>Code</th>
  		<th># of Flags</th>
  		<th>Destination</th>
  		<th>Aliases</th>
  		<th>Actions</th>
  	</tr>
  	<?php

		//Make each row
  	foreach ($select->fetchAll() as $row) {
  			print "\n<tr>";
  			
  			//the code
  			print "\n\t<td>";
  			print "\n\t\t<a href='info.php?code=".$row['code']."'>".$row['code']."</a>";
  			print "\n\t</td>";
  			
  			//the # of flags
  			print "\n\t<td>";
  			print $row['num_flags'];
  			print "\n\t</td>";
  			
  			//the url
  			print "\n\t<td>";
  			print "<a href='".$row['url']."'>".$row['url']."</a>";
  			print "\n\t</td>";
  			
  			//the aliases
  			print "\n\t<td>";
  			print $row['aliases'];
  			print "\n\t</td>";

  			//we want to be able to get additional info
  			//or delete all flags for each code in the table
  			print "\n\t<td class='action_cells'>";
  			//the info button
  			print "\n\t\t<a href='flag_details.php?code=".$row['code']."&institution=".$row['institution']."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><button>Info</button></a>";
  			//the clear button
  			print "\n\t\t<form action='flag_clear.php' method='post'>";
  			print "\n\t\t<div><input type='hidden' name='xsrfkey' value='". $_SESSION['xsrfkey']."' />";
  			print "\n<input type='hidden' value='".$row['code']."' name='code' />";
  			print "\n<input type='hidden' value='".$row['institution']."' name='institution' />";
  			print "\n<input type='submit' value='Clear Flags' />";
  			print "\n</div></form>";
  			print "\n</td>";
  			print "\n</tr>";
  		} //end foreach ($select->fetchAll() as $row) {
  		
  	?>
  	</table>
  	<?php

//now catch any exceptions
} catch (Exception $e) {
	throw $e;
}

require_once "footer.php";
