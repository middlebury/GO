<?php

require_once "go.php";

$name = "";
if (isset($_SESSION["AUTH"])) {
  $name = $_SESSION["AUTH"]->getName();
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>The GOtrol Panel</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="robots" content="follow,index" />
		<link rel="stylesheet" media="screen" type="text/css" href="styles.css" />
		<link rel="stylesheet" media="screen" type="text/css" href="https://web.middlebury.edu/development/tools/2d/Stylesheets/2dFlex.css" title="flex" />
		<link rel="alternate stylesheet" media="screen" type="text/css" href="https://web.middlebury.edu/development/tools/2d/Stylesheets/2dFixed.css" title="fixed" />
		<script type="text/javascript" src="https://web.middlebury.edu/development/tools/2d/JavaScript/StyleSwitcher.js"></script>
		<script type="text/javascript" src="scripts.js"></script>
	</head>
	<body>
		<div class="main">
			<div class="header">
				<div class="headerWelcome">
					<?php
					  if ($name) {
					    print "Welcome ".htmlentities($name)." &#160; | &#160; ";
					  }
					  foreach($institutions as $inst => $applicationPath) {
					    if ($inst == $institution)
					  		print "<strong>";
					    print "<a href=\"".equivalentUrl($inst)."\">" . $inst . "</a> &#160; | &#160;";
					    if ($inst == $institution)
					  		print "</strong>";
					  }
					?>
					<a href="#" onclick="setActiveStyleSheet('fixed'); return false;">Fixed</a> or
					<a href="#" onclick="setActiveStyleSheet('flex'); return false;">Flex</a>
				</div>
				<div class="clear">&#160;</div>
				<a href="http://www.middlebury.edu">
					<img class="headerLogo" src="https://web.middlebury.edu/development/tools/2d/Images/mclogo.gif" alt="Click here to return to Middlebury College home page" />
				</a>
				<div class="headerSite">
					<h1><a href="admin.php">The GOtrol Panel</a></h1>
				</div>
				<div class="clear">
					&#160;
				</div>
			</div>
			<div class="headerNavigation">
				<div class="CssMenu">
					<div class="AspNet-Menu-Horizontal">
						<ul class="AspNet-Menu">
							<li class="AspNet-Menu-Leaf">
								<a href="create.php">Create</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="update.php">View / Update</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="notify.php">Notify</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="clear">&#160;</div>
			</div>
			<div class="content">
				<div id="response"></div>