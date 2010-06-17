<?php
require_once "config.php";
require_once "go.php";

$letter = "a";

if (isset($_GET["letter"]) && preg_match("/^[A-Za-z]|\[0-9\]$/", $_GET["letter"]) === 1) {
	$letter = $_GET["letter"];
}

?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en">
	<head>
		<title>The GOBacktionary</title>
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
					Welcome &#160; | &#160;
					<?php
					  foreach($institutions as $inst => $applicationPath) {
					    print "<a href=\"gobacktionary.php?institution=" . $inst . "\">" . $inst . "</a> &#160; | &#160;";
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
					<h1>The GOBacktionary</h1>
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
								<a href="gobacktionary.php?letter=[0-9]&institution=<?php echo $institution; ?>">#</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=a&institution=<?php echo $institution; ?>">A</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=b&institution=<?php echo $institution; ?>">B</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=c&institution=<?php echo $institution; ?>">C</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=d&institution=<?php echo $institution; ?>">D</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=e&institution=<?php echo $institution; ?>">E</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=f&institution=<?php echo $institution; ?>">F</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=g&institution=<?php echo $institution; ?>">G</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=h&institution=<?php echo $institution; ?>">H</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=i&institution=<?php echo $institution; ?>">I</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=j&institution=<?php echo $institution; ?>">J</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=k&institution=<?php echo $institution; ?>">K</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=l&institution=<?php echo $institution; ?>">L</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=m&institution=<?php echo $institution; ?>">M</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=n&institution=<?php echo $institution; ?>">N</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=o&institution=<?php echo $institution; ?>">O</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=p&institution=<?php echo $institution; ?>">P</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=q&institution=<?php echo $institution; ?>">Q</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=r&institution=<?php echo $institution; ?>">R</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=s&institution=<?php echo $institution; ?>">S</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=t&institution=<?php echo $institution; ?>">T</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=u&institution=<?php echo $institution; ?>">U</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=v&institution=<?php echo $institution; ?>">V</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=w&institution=<?php echo $institution; ?>">W</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=x&institution=<?php echo $institution; ?>">X</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=y&institution=<?php echo $institution; ?>">Y</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=z&institution=<?php echo $institution; ?>">Z</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="clear">&#160;</div>
			</div>
			<div class="content">
				<p><b>GO needs your help!</b> Find out how you can help by logging in to our <a href="admin.php">new self-service shortcut creation interface</a>!</p>
				<p>You can also <a href="gotionary.php">view this list, sorted by the shortcut</a>.</p>
<?php

global $connection;

$where = "description LIKE '{$letter}%'";
if ($letter == "[0-9]") {
	$where = "description LIKE '0%' OR description LIKE '1%' OR description LIKE '2%' OR description LIKE '3%' OR description LIKE '4%' OR description LIKE '5%' OR description LIKE '6%' OR description LIKE '7%' OR description LIKE '8%' OR description LIKE '9%'";
}

$select = $connection->prepare("SELECT code.name AS name, code.description AS description, code.url AS url, alias.name AS alias FROM code LEFT JOIN alias ON (code.name = alias.code AND alias.institution = :institution) WHERE {$where} AND code.institution = :institution AND public = 1 ORDER BY code.description, code.url, code.name, alias.name");
$select->bindValue(":institution", $institution);
$select->execute();

$lines = array();
$current_url = "";
print "<p>&nbsp;";

while($row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
  
  if ($current_url != $row->url) {
    print "</p><p>$row->description";
    
    print "<br />&nbsp;&nbsp;&nbsp;<a href=\"".Go::getShortcutUrl($row->name, $institution)."\">go/{$row->name}</a>";
  }
  
  if ($row->alias) {
    print "<br />&nbsp;&nbsp;&nbsp;<a href=\"".Go::getShortcutUrl($row->name, $institution)."\">go/" . $row->alias . "</a>";
  }

  $current_url = $row->url;
}
?>
			</div>
		</div>
	</body>
</html>