<?php

require_once "go.php";

$name = "";
if (isset($_SESSION["AUTH"])) {
  try {
    $name = $_SESSION["AUTH"]->getName();
  } catch (Exception $e) {
    // We may have an expired proxy-ticket kept around. If so, regenerate the session
    // and log-in again.
    if ($e->getCode() == PHPCAS_SERVICE_PT_FAILURE) {
      session_destroy();
      header('Location: '.$_SERVER['REQUEST_URI']);
      exit;
    } else {
    	throw $e;
    }
  }
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
		<!--[if IE]>
		<link rel="stylesheet" media="screen" type="text/css" href="styles-ie.css" />
		<![endif]-->
	</head>
	<body>
		<div class="main">
			<div class="header">
				<div class="headerWelcome">
					<?php
					  if ($name) {
					    print "Welcome ".htmlentities($name)." &#160; | &#160; ";
					  }
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
					<h1><a href="admin.php">The GOtrol Panel</a></h1>
				</div>
				<div class="clear">
					&#160;
				</div>
			</div>

			