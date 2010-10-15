<?php
//go_functions.php gives us access to the isSuperAdmin function as well as isAdmin
require_once "go_functions.php";
require_once "go.php";
require_once "header.php";
require_once "admin_nav.php";
?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script src="addremove.js" type="text/javascript"></script>
<script src="md5.js" type="text/javascript"></script>
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
				
				<form action="process.php" method="post">
					<div>
					<input type="hidden" name="xsrfkey" value="<?php print $_SESSION['xsrfkey'] ?>" />
					<input type="hidden" name="code" value="<?php print $code->getName() ?>" />
					<input type="hidden" name="institution" value="<?php print $code->getInstitution() ?>" />
					<input type="hidden" name="url" value="<?php print urldecode($_GET['url']) ?>" />
					<input type="hidden" name="form_url" value="<?php print htmlentities(curPageURL()) ?>" />
					
					<?php
						// If an update message was set prior to a redirect
						// to this page display it and clear the message.
						if (isset($_SESSION['update_message'])) {
							foreach ($_SESSION['update_message'] as $message) {
								print $message;
							}
							unset($_SESSION['update_message']);
						}
					?>
					
						<h2><?php print $code->getName() ?> (<?php print $code->getInstitution() ?>)</h2>
						<p>
					URL: <input class="<?php if (isset($_SESSION['field_id_in_error'])) { print errorCase($_SESSION['field_id_in_error'], 'update_url'); } ?>" name="update_url" type="text" size="<?php print strlen($code->getUrl()); ?>" value="<?php if (isset($_SESSION['form_values'])) { print htmlentities($_SESSION['form_values']['update_url']); } else { print $code->getUrl(); } ?>" />
						</p>
						<p>
							Description<br />
							<textarea class="<?php if (isset($_SESSION['field_id_in_error'])) { print errorCase($_SESSION['field_id_in_error'], 'update_description'); } ?>" cols="50" rows="3" name="update_description"><?php if (isset($_SESSION['form_values'])) { print htmlentities($_SESSION['form_values']['update_description']); } else { echo $code->getDescription(); } ?></textarea>
						</p>
						<?php if (!$code->getPublic() || isSuperAdmin($_SESSION['AUTH']->getId())) { ?>
							<p>
								<input value="1" name="public" type="radio" <?php if($code->getPublic()) echo "checked=\"checked\""; ?> /> Show on GOtionary
								<input value="0" name="public" type="radio" <?php if(!$code->getPublic()) echo "checked=\"checked\""; ?> /> Hide from GOtionary
							</p>
						<?php } ?>
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
						<?php 
						// If we've submitted the form with values but it was not yet
						// processed, repopulate the aliases if needed
						if (isset($_SESSION['form_values']['alias_list'])) {
							foreach ($_SESSION['form_values']['alias_list'] as $current_alias) {
								print "<li class='".$current_alias."_alias_list'>".$current_alias." <input type='button' value='Delete'></li>";
								print "<input class='".$current_alias."_alias_list' type='hidden' value='".$current_alias."' name='alias_list[]'";
							}
						}
						?>
						</ul>
						<input class="<?php if (isset($_SESSION['field_id_in_error'])) { print errorCase($_SESSION['field_id_in_error'], 'add_alias_text'); } ?>" type="text" id="add_alias_text" maxlength="150" name="alias" /><input type="button" id="add_alias_button" name="add_alias" value="Add Alias"/>
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
						<?php 
						// If we've submitted the form with values but it was not yet
						// processed, repopulate the admins if needed
						if (isset($_SESSION['form_values']['admin_list'])) {
							foreach (array_unique($_SESSION['form_values']['admin_list']) as $current_admin) {
								print "<li class='".$current_admin."_admin_list'>".$current_admin." <input type='button' value='Delete'></li>";
								print "<input class='".$current_admin."_admin_list' type='hidden' value='".$current_admin."' name='admin_list[]'";
							}
						}
						?>
						</ul>
						<input class="<?php if (isset($_SESSION['field_id_in_error'])) { print errorCase($_SESSION['field_id_in_error'], 'add_admin_text'); } ?>" type="text" id="add_admin_text"  maxlength="150" name="admin" /><input type="button" id="add_admin_button" name="add_admin" value="Add Admin"/>
						<p><input type="submit" name="update" value="Apply These Changes" />
							<input type="submit" name="revert" value="Revert These Changes" />
						<input type="submit" name="delete" value="Delete Shortcut" /></p>
					</div>
				</form> 
		<?php
	}
	else {
		die("You are not authorized to admin this code.");
	}
}

unset($_SESSION['field_id_in_error']);

require_once "footer.php";