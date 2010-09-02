<?php
require_once "functions.php";
require_once "go.php";
require_once "header.php";
require_once "admin_nav.php";
?>

<div class="content">
	<div id="response"></div>
	
<?php
global $connection;
$user = new User($_SESSION["AUTH"]->getId());

global $institutions;

$codes = $user->getCodes();
?>
<input type='hidden' id='xsrfkey' value='<?php echo $_SESSION['xsrfkey']; ?>'/>
<div id="update">
	<h2>Update</h2>
	<table id="codes" width="100%" border="1">
<?php
foreach($codes as $name => $code) {
	$codename = $code->getInstitution() . "/" . $code->getName();
?>
		<tr id="<?php echo $codename; ?>">
			<th>Shortcut</th>
			<th>Admins</th>
			<th>Options</th>
		</tr>
		<tr>
			<td>
				<p>
					<?php 
					echo $code->getName();

					if ($code->getCreator() == $user->getName()) {
					?>
					<input type="button" value="Delete" onclick="deleteCode('<?php echo $code->getName(); ?>', '<?php echo $code->getInstitution(); ?>');" />
					<?php
					}
					?>
				</p>
			</td>
			<td rowspan="3">
				<ul id="users_<?php echo $codename; ?>">
<?php
foreach($code->getUsers() as $cUser) {
	$username = $_SESSION["AUTH"]->getName($cUser->getName());
?>
					<li id="<?php echo $username . "_" . $codename; ?>">
						<p>
<?php
							echo $_SESSION["AUTH"]->getName($cUser->getName());
							
							if ($cUser->getName() == $code->getCreator()) {
								echo " (creator)";
							} else {
?>
							<input type="button" value="Delete" onclick="deleteUser('<?php echo $code->getName(); ?>', '<?php echo $code->getInstitution(); ?>', '<?php echo $username; ?>');" />
<?php
							}
?>
						</p>
					</li>
<?php
}
?>
				</ul>
				<p>
					<input id="adduser_<?php echo $codename; ?>" name="adduser_user" type="text" />
					<input type="button" value="Add User" onclick="addUser('<?php echo $code->getName(); ?>', '<?php echo $code->getInstitution(); ?>');" />
				</p>
			</td>
			<td rowspan="3">
				<p>
					<label for="upurl_<?php echo $codename; ?>">URL</label>&nbsp;
					<input id="upurl_<?php echo $codename; ?>" name="update_url" type="text" value="<?php echo $code->getUrl(); ?>" size="62" />
				</p>
				<p>
				<?php
				  $i = 0;
				  foreach (array_keys($institutions) as $domain) {
				?>
					<input id="inst_<?php echo $i . "_" . $codename; ?>" name="inst_<?php echo $codename; ?>" value="<?php echo $domain; ?>" type="radio" <?php if($code->getInstitution() == $domain) echo "checked=\"checked\""; ?> disabled="disabled" />
					<label for="inst_<?php echo $i . "_" . $codename; ?>"><?php echo $domain; ?></label>
				<?php
				    $i++;
				  }
				?>
				</p>
				<p>
					<label for="updesc_<?php echo $codename; ?>">Description</label><br />
					<textarea id="updesc_<?php echo $codename; ?>" cols="50" rows="3" name="update_description"><?php echo $code->getDescription(); ?></textarea>
				</p>
				<p>
					<input id="public_yes_<?php echo $codename; ?>" name="public_<?php echo $codename; ?>" value="1" type="radio" <?php if($code->getPublic()) echo "checked=\"checked\""; ?> />
					<label for="public_yes_<?php echo $codename; ?>">Show on GOtionary</label>
					<input id="public_no_<?php echo $codename; ?>" name="public_<?php echo $codename; ?>" value="0" type="radio" <?php if(!$code->getPublic()) echo "checked=\"checked\""; ?> />
					<label for="public_no_<?php echo $codename; ?>">Hide from GOtionary</label>
					<br /><input type="button" value="Update" onclick="update('<?php echo $code->getName(); ?>', '<?php echo $code->getInstitution(); ?>');" />
				</p>
			</td>
		</tr>
		<tr>
			<th>Aliases</th>
		</tr>
		<tr>
			<td>
				<ul id="aliaslist_<?php echo $codename; ?>">
<?php
	foreach($code->getAliases() as $name => $alias) {
?>
					<li id="<?php echo $codename . "_" . $alias->getName(); ?>"><?php echo $alias->getName(); ?>
<?php
		if ($code->getCreator() == $user->getName()) {
?>
					<input type="button" value="Delete" onclick="deleteAlias('<?php echo $alias->getName(); ?>', '<?php echo $alias->getInstitution(); ?>', '<?php echo $alias->getCode(); ?>');" /></li>
<?php
		}
	}
?>
				</ul>
				<form action='alias.php' method='post' onsubmit="addAlias('<?php echo $code->getName(); ?>', '<?php echo $code->getInstitution(); ?>'); return false;">
					<input type="text" name="alias" id="addalias_<?php echo $codename; ?>"/>
					<input type="submit" value="Add Alias"/>
				</form>
			</td>
		</tr>
<?php
}
?>
	</table>
</div>
<?php
require_once "footer.php";
?>