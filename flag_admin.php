<?php
//go_functions.php gives us access to the isSuperAdmin function 
require_once "go_functions.php";
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

// If an update message was set prior to a redirect
// to this page display it and clear the message.
if (isset($_SESSION['update_message'])) {
	foreach ($_SESSION['update_message'] as $message) {
		print $message;
	}
	unset($_SESSION['update_message']);
}

//Create a table of codes so we know which
//ones have been flagged and how many times

try {
	//Keep non-superadmins out of this page
	if (!isSuperAdmin()) {
		die("You do not have permission to view this page");
	}
	
	//We want to know the code, the number of times flagged
	//the destination, any aliases, and any comments
  $select = $connection->prepare("
  SELECT
  	flag.code,
  		COUNT(flag.code) AS
  	num_flags,
  	aliases,
  	flag.url,
  	flag.institution,
  	GROUP_CONCAT(comment SEPARATOR ', ') AS
  		comment
  FROM
  	flag
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
  		ON flag.code = grouped_alias.code 
 	WHERE
 		completed = '0'
  GROUP BY
  	code 
  ORDER BY
  	num_flags
  DESC;");
  $select->execute();
	
	//We want to know the code, the number of times flagged
	//the destination, any aliases, and any comments
  $select2 = $connection->prepare("
  SELECT
  	flag.code,
  		COUNT(flag.code) AS
  	num_flags,
  	aliases,
  	flag.url,
  	flag.institution,
  	flag.completed,
  	flag.completed_on,
  	flag.notes,
  	GROUP_CONCAT(comment SEPARATOR ', ') AS
  		comment
  FROM
  	flag
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
  		ON flag.code = grouped_alias.code 
 	WHERE
 		completed != '0'
  GROUP BY
  	code 
  ORDER BY
  	flag.completed_on
  DESC
  LIMIT
  	20;");
  $select2->execute();
	
  ?>
	
	<p>Flags in need of moderation</p>
	
	<!-- Here is the table -->
  <table class="flag_admin_table">
  	<tr>
  		<th>Code</th>
  		<th># of Flags</th>
  		<th>Destination</th>
  		<th>Aliases</th>
  		<th>Comment(s)</th>
  		<th>Actions</th>
  	</tr><?php
  	$results = $select->fetchAll();
  	if ($results == array()) {
				print "<td colspan='6' style='text-align: center;'>None</td>";
		}
		//Make each row
  	foreach ($results as $row) {?>
  	<tr>
  		<!-- the code -->
  		<td><?php print"<a href='info.php?code=".$row['code']."'>".$row['code']."</a>";?></td>
  		<!-- the # of flags -->
  		<td><?php print $row['num_flags'];?></td>
  		<!-- the URL -->
  		<td><?php print "<a href='".$row['url']."'>".$row['url']."</a>";?></td>
  		<!-- the aliases -->
  		<td><?php print $row['aliases'];?></td>
  		<td><?php print $row['comment'];?></td>
  		<!-- we want to be able to get additional info
  		or delete all flags for each code in the table -->
  		<td class='action_cells'>
  		  <form action='flag_clear.php' method='post' id="flag_button_form">
  			<!-- the info button -->
  			<?php print "\n\t\t<a href='info.php?code=".$row['code']."'><input onclick='window.location=\"info.php?code=".$row['code']."\"' type='button' value='Info' /></a>"; ?>
  			<!-- the history button -->
  			<?php print "\n\t\t<a href='details.php?code=".$row['code']."&amp;institution=".$row['institution']."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><input type='button' value='History' /></a>";?>
  			<!-- the clear button -->
  					<input type='submit' value='Complete' />
  					<?php print "\n\t\t\t\t\t\t\t<input type='hidden' name='xsrfkey' value='".$_SESSION['xsrfkey']."' />";
  								print "\n\t\t\t\t\t\t\t<input type='hidden' value='".$row['code']."' name='code' />";
  								print "\n\t\t\t\t\t\t\t<input type='hidden' value='".$row['institution']."' name='institution' />";?>
  					<br />
            Notes:<br />
            <textarea name='notes'/></textarea>
  			</form>
  		</td>
  	</tr>
  <?php } /*end foreach ($select->fetchAll() as $row) { */ ?>
  </table>
  
  <p class="completed" >Completed Flags</p>
  
  <table class="flag_admin_table completed">
  	<tr>
  		<th>Code</th>
  		<th># of Flags</th>
  		<th>Destination</th>
  		<th>Aliases</th>
  		<th>Notes</th>
  		<th>Completed By</th>
  		<th>On</th>
  		<th>Actions</th>
  	</tr><?php
		//Make each row
  	foreach ($select2->fetchAll() as $row) {?>
  	<tr>
  		<!-- the code -->
  		<td><?php print"<a href='info.php?code=".$row['code']."'>".$row['code']."</a>";?></td>
  		<!-- the # of flags -->
  		<td><?php print $row['num_flags'];?></td>
  		<!-- the URL -->
  		<td><?php print "<a href='".$row['url']."'>".$row['url']."</a>";?></td>
  		<!-- the aliases -->
  		<td><?php print $row['aliases'];?></td>
  		<td><?php print $row['notes'];?></td>
  		<td><?php print $row['completed'];?></td>
  		<td><?php print $row['completed_on'];?></td>
  		<!-- we want to be able to get additional info
  		or delete all flags for each code in the table -->
  		<td class='action_cells'>
  			<!-- the info button -->
  			<?php print "\n\t\t<a href='info.php?code=".$row['code']."'><input onclick='window.location=\"info.php?code=".$row['code']."\"' type='button' value='Info' /></a>"; ?>
  			<!-- the history button -->
  			<?php print "\n\t\t<a href='details.php?code=".$row['code']."&amp;institution=".$row['institution']."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><input type='button' value='History' /></a>";?>
  		</td>
  	</tr>
  <?php } /*end foreach ($select->fetchAll() as $row) { */ ?>
  </table>
  
  <?php //now catch any exceptions
} catch (Exception $e) {
	throw $e;
}

require_once "footer.php";
