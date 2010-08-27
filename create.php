<?php
require_once "go.php";
require_once "header.php";
require_once "admin_nav.php";
global $institutions;
?>

<div class="content">
	<div id="response"></div>
	
<input type='hidden' id='xsrfkey' value='<?php echo $_SESSION['xsrfkey']; ?>'/>
<div id="create">
	<h2>Create</h2>
	<table width="100%">
		<tr>
			<td style="width: 50%">
				<h3>Shortcut</h3>
				<p>Shortcuts are the standard way to set up a GO URL.</p>
				<p>
					<label for="create_code">Shortcut</label>
					<input id="create_code" name="create_code" type="text" size="50" />
					<br />example: go/<b>shortcut</b> - don't start the shortcut with 'go/'
				</p>
				<p>
					<label for="create_url">URL</label>
					<input id="create_url" name="create_url" type="text" size="62" />
					<br />example: http://www.google.com - be sure to include http:// or https://
				</p>
				<p>
				<?php
				  $i = 0;
				  foreach (array_keys($institutions) as $domain) {
				?>
					<input id="create_inst_<?php echo $i; ?>" name="create_inst" value="<?php echo $domain; ?>" type="radio" <?php if($domain == $institution) { echo "checked=\"checked\""; } ?> />
					<label for="create_inst_<?php echo $i; ?>"><?php echo $domain; ?></label>
				<?php
				    $i++;
				  }
				?>
				</p>
				<p>
					<label for="create_description">Description</label><br />
					<textarea id="create_description" name="create_description" rows="5" cols="50"></textarea>
				</p>
				<p>
					<input id="public_yes" name="public" value="1" type="radio" checked="checked" />
					<label for="public_yes">Show on GOtionary</label>
					<input id="public_no" name="public" value="0" type="radio" />
					<label for="public_no">Hide from GOtionary</label>
				</p>
				<p>
					<input id="create_action" name="action" type="hidden" value="create" />
					<input id="create_submit" type="button" value="Submit" onclick="createCode();" />
				</p>
			</td>
			<td>
				<h3>Alias</h3>
				<p>Aliases allow you to provide a different path to your GO shortcuts. For example, 'bw' is an alias for 'bannerweb' so you can type go/bw to get to BannerWeb.</p>
				<p>
					<label for="alias_code">Existing Shortcut:</label>
					<input id="alias_code" name="alias_code" type="text" />
				</p>
				<p>
					<label for="alias_name">New Alias to add:</label>
					<input id="alias_name" name="alias_name" type="text" />
				</p>
				<p>
				<?php
				  $i = 0;
				  foreach (array_keys($institutions) as $domain) {
				?>
					<input id="alias_inst_<?php echo $i; ?>" name="alias_inst" value="<?php echo $domain; ?>" type="radio" <?php if($domain == $institution) { echo "checked=\"checked\""; } ?> />
					<label for="alias_inst_<?php echo $i; ?>"><?php echo $domain; ?></label>
				<?php
				    $i++;
				  }
				?>
				</p>
				<p>
					<input id="alias_action" name="action" type="hidden" value="alias" />
					<input id="alias_submit" type="button" value="Submit" onclick="createAlias();" />
				</p>
			</td>
		</tr>
	</table>
</div>
<?php
require_once "footer.php";
?>