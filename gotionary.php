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
					Welcome &#160; | &#160;
					<?php
					  foreach($institutions as $inst => $applicationPath) {
					    print "<a href=\"gotionary.php?institution=" . $inst . "\">" . $inst . "</a> &#160; | &#160;";
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
								<a href="gotionary.php?letter=[0-9]&institution=<?php echo $institution; ?>">#</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=a&institution=<?php echo $institution; ?>">A</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=b&institution=<?php echo $institution; ?>">B</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=c&institution=<?php echo $institution; ?>">C</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=d&institution=<?php echo $institution; ?>">D</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=e&institution=<?php echo $institution; ?>">E</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=f&institution=<?php echo $institution; ?>">F</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=g&institution=<?php echo $institution; ?>">G</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=h&institution=<?php echo $institution; ?>">H</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=i&institution=<?php echo $institution; ?>">I</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=j&institution=<?php echo $institution; ?>">J</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=k&institution=<?php echo $institution; ?>">K</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=l&institution=<?php echo $institution; ?>">L</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=m&institution=<?php echo $institution; ?>">M</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=n&institution=<?php echo $institution; ?>">N</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=o&institution=<?php echo $institution; ?>">O</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=p&institution=<?php echo $institution; ?>">P</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=q&institution=<?php echo $institution; ?>">Q</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=r&institution=<?php echo $institution; ?>">R</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=s&institution=<?php echo $institution; ?>">S</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=t&institution=<?php echo $institution; ?>">T</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=u&institution=<?php echo $institution; ?>">U</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=v&institution=<?php echo $institution; ?>">V</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=w&institution=<?php echo $institution; ?>">W</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=x&institution=<?php echo $institution; ?>">X</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=y&institution=<?php echo $institution; ?>">Y</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gotionary.php?letter=z&institution=<?php echo $institution; ?>">Z</a>
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