<?php
//go_functions.php gives us access to the isSuperAdmin, isAdmin, and curPageURL()
require_once "go_functions.php";
require_once "go.php";
require_once "header.php";
require_once "admin_nav.php";
global $institutions;
?>

<!-- Include jQuery/JS to apply add remove behavior to the admin and alias lists -->
<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.4.2/jquery.min.js"></script>
<script src="addremove.js" type="text/javascript"></script>

		<div class="content">
			<div id="response"></div>
				
				<form action="process.php" method="post">
					<div> <!-- a block level element to hold the contents of the form -->
					<!-- Pass the current xss check value -->
					<input type="hidden" name="xsrfkey" value="<?php print $_SESSION['xsrfkey'] ?>" />
					<!-- Pass the current URL to be redirected to after processing -->
					<input type="hidden" name="url" value="<?php print curPageURL() ?>" />
					<!-- Since the currently logged in user is creating a new code
					set them as an admin of this code, but only if the form hasn't been submitted -->
					<?php if (!isset($_SESSION['form_values']['admin_list'])) { ?>
						<input type="hidden" name="admin_list[]" value="<?php print $_SESSION['AUTH']->getName() ?>" />
					<?php } ?>
					<!-- Pass the current URL --> 
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
						<!-- ADD SHORTCUT -->
						<h3>Shortcut</h3>
							<p>Shortcuts are the standard way to set up a GO URL.</p>
						<p>
							<label for="code">Shortcut</label>
								<!-- All the stuff in this input basically just says "If this
								field didn't validate on submission, then set a class that
								indicates this. Also set it's value to the previously submitted
								value. This is duplicated in the rest of the fields in this form -->
								<input class="<?php if (isset($_SESSION['field_id_in_error'])) { print errorCase($_SESSION['field_id_in_error'], 'code'); } ?>" id="code" name="code" type="text" size="50" value="<?php if (isset($_SESSION['form_values'])) { print htmlentities($_SESSION['form_values']['code']); } ?>" />
							<br />example: go/<b>shortcut</b> - don't start the shortcut with 'go/'
						</p>
						
						<!-- ADD URL -->
						<p>
							<label for="update_url">URL</label>
							<input class="<?php if (isset($_SESSION['field_id_in_error'])) { print errorCase($_SESSION['field_id_in_error'], 'update_url'); } ?>" id="update_url" name="update_url" type="text" size="62" value="<?php if (isset($_SESSION['form_values'])) { print htmlentities($_SESSION['form_values']['update_url']); } ?>" />
							<br />example: http://www.google.com - be sure to include http:// or https://
						</p>
						
						<!-- ADD INSTITUTION -->
						<p>
						<?php
				  		$i = 0;
				  		foreach (array_keys($institutions) as $domain) {
						?>
							<input id="create_inst_<?php echo $i; ?>" name="institution" value="<?php echo $domain; ?>" type="radio" <?php if ($domain == $institution) { echo "checked=\"checked\""; } ?> />
							<label for="create_inst_<?php echo $i; ?>"><?php echo $domain; ?></label>
						<?php
				    	$i++;
				  		}
						?>
						</p>
						
						<!-- ADD DESCRIPTION -->
						<p>
							Description<br />
							<textarea class="<?php if (isset($_SESSION['field_id_in_error'])) { print errorCase($_SESSION['field_id_in_error'], 'update_description'); } ?>" cols="50" rows="3" name="update_description" id="update_description"><?php if (isset($_SESSION['form_values'])) { print htmlentities($_SESSION['form_values']['update_description']); } ?></textarea>
						</p>
						<?php if (isSuperAdmin($_SESSION['AUTH']->getId())) { ?>
						<p>
							<input value="1" name="public" type="radio" checked="checked" /> Show on GOtionary 
							<input value="0" name="public" type="radio" /> Hide from GOtionary
						</p>
						<?php } ?>
						
						<!-- ADD ALIASES -->
						<h3>Aliases</h3>
						<ul id="alias_list">
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
						
						<!-- ADD ADMINS -->
						<h3>Admins</h3>
						<ul id="admin_list">
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
						<p><input type="submit" name="update" value="Create Shortcut" /><input type="submit" name="revert" value="Start Over" /></p>
						
					</div> <!-- end the block level element to hold the contents of the form -->
				</form> 
		<?php
		unset($_SESSION['field_id_in_error']);

require_once "footer.php";