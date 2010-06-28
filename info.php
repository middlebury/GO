<?php
require_once "config.php";
require_once "go.php";


$name = str_replace(" ", "+", $_GET["code"]);
$name = trim($name, '/');

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>Shortcut Info</title>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />
		<meta name="robots" content="follow,index" />
		<link rel="stylesheet" media="screen" type="text/css" href="styles.css" />
		<link rel="alternate stylesheet" media="screen" type="text/css" href="https://web.middlebury.edu/development/tools/2d/Stylesheets/2dFixed.css" title="fixed" />
		<script type="text/javascript" src="https://web.middlebury.edu/development/tools/2d/JavaScript/StyleSwitcher.js"></script>
	</head>
	<body>
		<div class="main">
			<div class="header">
				<div class="headerWelcome">
					<?php
					  foreach(array_keys($institutions) as $inst) {
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
				<?php print $institutions[$institution]['logo_html']; ?>
				<div class="headerSite">
					<h1>The GOtionary</h1>
				</div>
				<div class="clear">
					&#160;
				</div>
			</div>
			<div class="headerNavigation">
				<div class="CssMenu">
					<div class="AspNet-Menu-Horizontal">
						<ul class="AspNet-Menu">
						</ul>
					</div>
				</div>
				<div class="clear">&#160;</div>
			</div>
			<div class="content">
			
				<p>This page describes the details for a single GO shortcut and its aliases. To view a list of all GO shortcuts, please see the <a href="gotionary.php">GOtionary</a>.</p>
				<p>GO shortcuts are managed by the people who created them. If you are one of the administrators for this shortcut, please log into the <a href="update.php">self-service admin</a> page to change or update this shortcut.</p>
				<p>If you are not an administrator of this shortcut, please contact one of the shortcut administrators listed below for any problems, changes, or updates related to this shortcut. Be sure to refer to the URL of this page when contacting them.</p>
<?php

try {
	if (Code::exists($name, $institution)) {
		$code = new Code($name, $institution);
	} else if (Alias::exists($name, $institution)) {
		$alias = new Alias($name, null, $institution);
		$code = new Code($alias->getCode(), $alias->getInstitution());
	} else {
		throw new Exception('Unknown Code "'.$name.'".');
	}
?>
				<dl>
					<dt>Code</dt>
					<dd><?php print htmlentities($code->getName()); ?></dd>
					<dt>Destination</dt>
					<dd><?php
						if (strlen($code->getUrl()))
							print '<a href="'.$code->getUrl().'">'.htmlentities($code->getUrl()).'</a>';
						else 
							print 'Error: No destination is set for this code.';
					?></dd>
					<?php
					$aliases = $code->getAliases();
					if (count($aliases)) {
						print "<dt>Aliases</dt>";
						print "<dd>".implode(' <br/>', array_keys($aliases))."</dd>";
					}
					?>
					<dt>Creator of this Code</dt>
					<dd><?php
						if ($code->getCreator()) {
							print Go::getUserDisplayName($code->getCreator());
						} else {
							print "None -- Contact <a href='mailto:go@middlebury.edu'>go@middlebury.edu</a> to claim this code.";
						}
					?></dd>
					<dt>Administrators of this code</dt>
					<dd><?php
						if (count($code->getUsers())) {
							$userStrings = array();
							foreach ($code->getUsers() as $user) {
								$userStrings[] = Go::getUserDisplayName($user->getName());
							}
							print implode (' <br/>', $userStrings);
							print "<br/><br/>Please contact one of these people for changes to this shortcut. ";
						} else {
							print "None -- Contact <a href='mailto:go@middlebury.edu'>go@middlebury.edu</a> to claim this code.";
						}
					?></dd>
					<dt>Display In GOtionary?</dt>
					<dd><?php print ($code->getPublic()? "yes":"no"); ?></dd>

				</dl>
<?php
} catch (Exception $e) {
	print "<div class='error'>Error: ".htmlentities($e->getMessage())."</div>";
}
?>
			</div>
		</div>
	</body>
</html>
