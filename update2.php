<?php
//go_functions.php gives us access to the isSuperAdmin function 
require_once "go_functions.php";
require_once "go.php";
require_once "header.php";
require_once "admin_nav.php";

$is_admin = false;
$code = new Code($_GET['code'], $_GET['institution']);
global $institutions;

if (isset($_SESSION['AUTH'])) {
	$select =  $connection->prepare("
  SELECT
  	user
  FROM
  	user_to_code
  WHERE
  	code = ?
  	AND
  	institution = ?");
  $select->bindValue(1, $_GET['code']);
  $select->bindValue(2, $_GET['institution']);
  $select->execute();
  
  foreach ($select->fetchAll() as $row) {
		if ($row['user'] == $_SESSION['AUTH']->getId()) {
			$is_admin = true;
		}
	}

	if (isSuperAdmin($_SESSION['AUTH']->getId()) || $is_admin) {
		
		?>
		<div class="content">
			<div id="response"></div>
		
				<form action="update_process.php" method="post">
					<input type="hidden" name="xsrfkey" value="<?php print $_SESSION['xsrfkey'] ?>" />
					<input type="hidden" name="code" value="<?php print $code->getName() ?>" />
					<input type="hidden" name="institution" value="<?php print $code->getInstitution() ?>" />
					<div>
						<p>You are authorized to admin this code.</p>
						<h2><?php print $code->getName() ?> (<?php print $code->getInstitution() ?>)</h2>
						<p>
							URL: <input name="update_url" type="text" size="<?php print strlen($code->getUrl()); ?>" value="<?php print $code->getUrl(); ?>" />
						</p>
						<p>
							Description<br />
							<textarea cols="50" rows="3" name="update_description"><?php echo $code->getDescription(); ?></textarea>
						</p>
						<p>
							<input value="1" type="radio" <?php if($code->getPublic()) echo "checked=\"checked\""; ?> /> Show on GOtionary 
							<input value="0" type="radio" <?php if(!$code->getPublic()) echo "checked=\"checked\""; ?> /> Hide from GOtionary
						</p>
						<h3>Aliases</h3>
						<ul>
						<?php
							foreach($code->getAliases() as $name => $alias) {
								print "<li>" . $alias->getName();
								print " <input type='checkbox' /> Delete?</li>";
							}
						?>
						</ul>
						<input type="text" name="alias" /><input type="submit" name="add_alias" value="Add Alias"/>
						</p>
						<h3>Admins</h3>
						<ul>
						<?php
							foreach($code->getUsers() as $cUser) {
								$username = $_SESSION["AUTH"]->getName($cUser->getName());
								print "<li id='" . $username . "'>". $username;
								print " <input type='checkbox' /> Delete?</li>";
							}
						?>
						</ul>
						<input type="text" name="admin" /><input type="submit" name="add_admin" value="Add Admin"/>
						<p><input type="submit" name="update" value="Update Code" />
						<input type="submit" name="delete" value="Delete Code" /></p>
					</div>
				</form> 
		<?php
	}
	else {
		die("You are not authorized to admin this code.");
	}
}

require_once "footer.php";