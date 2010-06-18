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
		<title>The GOtionary</title>
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
					  foreach($institutions as $inst => $applicationPath) {
					    if ($inst == $institution)
					  		print "<strong>";
					    print "<a href=\"".$applicationPath."gotionary.php\">" . $inst . "</a> &#160; | &#160;";
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
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=[0-9]">#</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=a">A</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=b">B</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=c">C</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=d">D</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=e">E</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=f">F</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=g">G</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=h">H</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=i">I</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=j">J</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=k">K</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=l">L</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=m">M</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=n">N</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=o">O</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=p">P</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=q">Q</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=r">R</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=s">S</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=t">T</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=u">U</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=v">V</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=w">W</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=x">X</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=y">Y</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=z">Z</a>
							</li>
						</ul>
					</div>
				</div>
				<div class="clear">&#160;</div>
			</div>
			<div class="content">
				<p><b>GO needs your help!</b> Find out how you can help by logging in to our <a href="admin.php">new self-service shortcut creation interface</a>!</p>
				<p>You can also <a href="gobacktionary.php">view this list, sorted by the destination</a>.</p>
<?php

global $connection;

$where = "name LIKE '{$letter}%'";
if ($letter == "[0-9]") {
	$where = "name LIKE '0%' OR name LIKE '1%' OR name LIKE '2%' OR name LIKE '3%' OR name LIKE '4%' OR name LIKE '5%' OR name LIKE '6%' OR name LIKE '7%' OR name LIKE '8%' OR name LIKE '9%'";
}

$select = $connection->prepare("SELECT name, description FROM code WHERE {$where} AND institution = :institution AND public = 1 ORDER BY name");
$select->bindValue(":institution", $institution);
$select->execute();

$lines = array();

while($row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
	$line = "<p><a href=\"".Go::getShortcutUrl($row->name, $institution)."\">go/{$row->name}</a>";

	if($row->description != "") {
		$line .= " - {$row->description}";
	}
	
	$line .= "</p>";
	$lines[$row->name] = $line;
}

$where = str_replace("name", "alias.name", $where);
$alias = $connection->prepare("SELECT alias.name AS name, code.description AS description FROM alias JOIN code ON (alias.code = code.name) WHERE {$where} AND alias.institution = :institution AND code.public = 1 ORDER BY alias.name");
$alias->bindValue(":institution", $institution);
$alias->execute();

while($row = $alias->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
	$line = "<p><a href=\"".Go::getShortcutUrl($row->name, $institution)."\">go/{$row->name}</a>";

	if($row->description != "") {
		$line .= " - {$row->description}";
	}
	
	$line .= "</p>";
	$lines[$row->name] = $line;
}

ksort($lines);

?>
				<table border="0" width="100%">
					<tr>
						<td>
<?php
$i = 0;
$count = count($lines);

while($i < $count / 2) {
  print current($lines);
  next($lines);
  $i++;
}
?>
						</td>
						<td>
<?php
while($i < $count) {
  print current($lines);
  next($lines);
  $i++;
}
?>
						</td>
					</tr>
				</table>
			</div>
		</div>
	</body>
</html>