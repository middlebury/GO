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

	$urlSearchQuery = "";
	$search_code = '';
	if (!empty($_GET['code'])) {
		$search_code = strip_tags(str_replace(["'", '"'], '', $_GET['code']));
		$urlSearchQuery .= '&code='.$search_code;
	}

	$search_user = '';
	if (!empty($_GET['user'])) {
		$search_user = strip_tags(str_replace(["'", '"'], '', $_GET['user']));
		$urlSearchQuery .= '&user='.$search_user;
	}

	$search_sort = 'DESC';
	if (!empty($_GET['sort'])) {
		if ($_GET['sort'] == 'ASC') {
			$search_sort = 'ASC';
		}
		$urlSearchQuery .= '&sort='.$search_sort;
	}

	$search_per_page = 50;
	if (!empty($_GET['pp'])) {
		$search_per_page = max(50, intval($_GET['pp']));
		$urlSearchQuery .= '&pp='.$search_per_page;
	}

	//We want to know the code, the number of times flagged
	//the destination, any aliases, and any comments
	$where = "";
	$queryArgs = [];
	if (!empty($search_code)) {
		$where .= " AND (code LIKE :code_like OR alias LIKE :alias_like)";
		$queryArgs[':code_like'] = str_replace('*', '%', $search_code);
		$queryArgs[':alias_like'] = str_replace('*', '%', $search_code);
	}
	if (!empty($search_user)) {
		$where .= " AND user_display_name LIKE :user_like";
		$queryArgs[':user_like'] = str_replace('*', '%', $search_user);
	}

  $select = $connection->prepare("
  SELECT
  	COUNT(*)
  FROM
  	log
	WHERE TRUE $where
  ;");
  $select->execute($queryArgs);
	$total = intval($select->fetchColumn());
	$select->closeCursor();
	$page = 1;
	if (!empty($_GET['page'])) {
		$page = max(1, intval($_GET['page']));
	}
	$pageSize = $search_per_page;
	$offset = intval(($page - 1) * $pageSize);
	$totalPages = ceil($total/$pageSize);

	//We want to know the code, the number of times flagged
	//the destination, any aliases, and any comments
  $select2 = $connection->prepare("
  SELECT
  	*
  FROM
  	log
	WHERE TRUE $where
  ORDER BY
  	id $search_sort
  LIMIT $offset, $pageSize
  ;");
  $select2->execute($queryArgs);

	ob_start();
  ?>

	<p class="pagination"><?php

	print "<a href='logs.php?". $urlSearchQuery ."'>&laquo;</a> ";

	$start = max(1, $page - 10);
	for ($i = $start; $i < $page; $i++) {
		print "<a href='logs.php?page=".$i."&". $urlSearchQuery ."'>".$i."</a> ";
	}

	print $page." ";

	$end = min($totalPages, $page + 10);
	for ($i = $page + 1; $i <= $end; $i++) {
		print "<a href='logs.php?page=".$i."&". $urlSearchQuery ."'>".$i."</a> ";
	}

	$last = $totalPages;
	print "<a href='logs.php?page=".$last."&". $urlSearchQuery ."'>&raquo;</a> ";

	?></p>

	<?php
	$pagination = ob_get_clean();
	?>


	<h1>GO Logs</h1>

	<form action="logs.php" method="get">
		<strong>Search by... </strong>
		<label>Code/Alias: <input type="text" name="code" value="<?php print $search_code; ?>"></label>
		<label>Username: <input type="text" name="user" value="<?php print $search_user; ?>"></label>
		<select name="sort">
			<option value="DESC"<?php print (($search_sort == 'DESC')?' selected="selected"':''); ?>>Newest First</option>
			<option value="ASC"<?php print (($search_sort == 'ASC')?' selected="selected"':''); ?>>Oldest First</option>
		</select>
		<select name="pp">
			<option value="50"<?php print (($search_per_page == 50)?' selected="selected"':''); ?>>50 per page</option>
			<option value="100"<?php print (($search_per_page == 100)?' selected="selected"':''); ?>>100 per page</option>
			<option value="200"<?php print (($search_per_page == 200)?' selected="selected"':''); ?>>200 per page</option>
			<option value="500"<?php print (($search_per_page == 500)?' selected="selected"':''); ?>>500 per page</option>
			<option value="1000"<?php print (($search_per_page == 1000)?' selected="selected"':''); ?>>1000 per page</option>
			<option value="10000"<?php print (($search_per_page == 10000)?' selected="selected"':''); ?>>10,000 per page</option>
		</select>
		<input type="submit" value="Search">
		<br><em>Use asterisks (*) as wild cards in search.</em>
	</form>

	<?php print $pagination; ?>

  <table class="logs_table">
  	<tr>
			<th>Date</th>
			<th>Domain</th>
  		<th>Code</th>
			<th>Alias</th>
			<th>User</th>
  		<th>Description</th>
  	</tr><?php
		//Make each row
  	foreach ($select2->fetchAll() as $row) {?>
  	<tr>
			<td><?php print $row['tstamp']; ?></td>
			<td><?php print $row['institution']; ?></td>
			<!-- the code -->
  		<td><?php print"<a href='info.php?code=".$row['code']."'>".$row['code']."</a>";?></td>
			<td><?php print $row['alias']; ?></td>
  		<!-- the # of flags -->
			<td><?php print $row['user_display_name']; ?></td>
  		<td><?php print $row['description'];?></td>
  	</tr>
  <?php } /*end foreach ($select->fetchAll() as $row) { */ ?>
  </table>

	<?php print $pagination; ?>

  <?php //now catch any exceptions
} catch (Throwable $e) {
	var_dump($e);
	throw $e;
}

require_once "footer.php";
