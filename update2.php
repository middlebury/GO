<?php
//go_functions.php gives us access to the isSuperAdmin function as well as isAdmin
require_once "go_functions.php";
require_once "go.php";
require_once "header.php";
require_once "admin_nav.php";
?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script src="update2.js" type="text/javascript"></script>
<?php
$code = new Code($_GET['code'], $_GET['institution']);

// This form should only be available to authenticated users 
if (isset($_SESSION['AUTH'])) {
	
	// Check to see if current user is admin of code
	$is_admin = isAdmin($_GET['code'], $_GET['institution']);
	
	// This form is only available if user is a superadmin or admin
	if (isSuperAdmin($_SESSION['AUTH']->getId()) || $is_admin) {
		
		?>
		<div class="content">
			<div id="response"></div>
				
				<form action="update_process.php" method="post">
					<div>
					<input type="hidden" name="xsrfkey" value="<?php print $_SESSION['xsrfkey'] ?>" />
					<input type="hidden" name="code" value="<?php print $code->getName() ?>" />
					<input type="hidden" name="institution" value="<?php print $code->getInstitution() ?>" />
					<input type="hidden" name="url" value="<?php print urldecode($_GET['url']) ?>" />
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
							<input value="1" name="public" type="radio" <?php if($code->getPublic()) echo "checked=\"checked\""; ?> /> Show on GOtionary 
							<input value="0" name="public" type="radio" <?php if(!$code->getPublic()) echo "checked=\"checked\""; ?> /> Hide from GOtionary
						</p>
						<h3>Aliases</h3>
						<ul id="alias_list">
						<?php
							foreach($code->getAliases() as $name => $alias) {
								print "<li>" . $alias->getName();
								print " <input type='button' value='Delete' /></li>";
							}
						?>
						<!--This is required by doctype-->
						<li style="display: none;">This space is intentionally left blank</li>
						</ul>
						<input type="text" id="add_alias_text" maxlength="150" name="alias" /><input type="button" id="add_alias_button" name="add_alias" value="Add Alias"/>
						<h3>Admins</h3>
						<ul id="admin_list">
						<?php
							foreach($code->getUsers() as $cUser) {
								$username = $_SESSION["AUTH"]->getName($cUser->getName());
								print "<li id='" . $username . "'>". $username;
								print " <input type='button' value='Delete' /></li>";
							}
						?>
						<!--This is required by doctype-->
						<li style="display: none;">This space is intentionally left blank</li>
						</ul>
						<input type="text" id="add_admin_text"  maxlength="150" name="admin" /><input type="button" id="add_admin_button" name="add_admin" value="Add Admin"/>
						<p><input type="submit" name="update" value="Apply Changes" />
						<input type="submit" name="delete" value="Delete Shortcut" /></p>
					</div>
				</form> 
		<?php
	}
	else {
		die("You are not authorized to admin this code.");
	}
}

require_once "footer.php";