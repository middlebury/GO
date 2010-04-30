<?php

require_once "go.php";

if (isset($_POST["username"]) && isset($_POST["password"])) {
  try {
    $_SESSION["AUTH"] = new GoAuthLdap($_POST["username"], $_POST["password"]);
    header("Location: " . $_POST["r"]);
  } catch(Exception $ex) {
    $error = $ex->getMessage();
  }
}

require_once "header.php";
?>  
<form action="login.php" method="post">
    <?php if (isset($error)) { print "<p>" . $error . "</p>"; } ?>
	<p>
		<label for="username">Username</label>
		<input name="username" type="text" id="username" />
	</p>
	<p>
		<label for="password">Password</label>
		<input name="password" type="password" id="password" />
	</p>
	<p>
		<input type="hidden" name="r" value="<?php echo $_GET["r"]; ?>" />
		<input type="submit" value="Submit" />
	</p>
</form>
<?php
require_once "footer.php";
?>