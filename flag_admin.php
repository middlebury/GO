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
  GROUP BY
  	code 
  ORDER BY
  	num_flags
  DESC;");
  $select->execute();

  ?>
	
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
		//Make each row
  	foreach ($select->fetchAll() as $row) {?>
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
  			<!-- the info button -->
  			<?php print "\n\t\t<a href='info.php?code=".$row['code']."'><input type='button' value='Info' /></a>"; ?>
  			<!-- the history button -->
  			<?php print "\n\t\t<a href='details.php?code=".$row['code']."&amp;institution=".$row['institution']."' onclick=\"var details=window.open(this.href, 'details', 'width=700,height=400,scrollbars=yes,resizable=yes'); details.focus(); return false;\"><input type='button' value='History' /></a>";?>
  			<!-- the clear button -->
  			<form action='flag_clear.php' method='post' id="flag_button_form">
  				<div>
  					<input type='submit' value='Clear Flag(s)' />
  					<?php print "\n\t\t\t\t\t\t\t<input type='hidden' name='xsrfkey' value='".$_SESSION['xsrfkey']."' />";
  								print "\n\t\t\t\t\t\t\t<input type='hidden' value='".$row['code']."' name='code' />";
  								print "\n\t\t\t\t\t\t\t<input type='hidden' value='".$row['institution']."' name='institution' />";?>
  				</div>
  			</form>
  		</td>
  	</tr>
  <?php } /*end foreach ($select->fetchAll() as $row) { */ ?>
  </table>
  <?php //now catch any exceptions
} catch (Exception $e) {
	throw $e;
}

require_once "footer.php";
