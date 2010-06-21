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
								<a href="gobacktionary.php?letter=[0-9]">#</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=a">A</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=b">B</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=c">C</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=d">D</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=e">E</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=f">F</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=g">G</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=h">H</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=i">I</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=j">J</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=k">K</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=l">L</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=m">M</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=n">N</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=o">O</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=p">P</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=q">Q</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=r">R</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=s">S</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=t">T</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=u">U</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=v">V</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=w">W</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=x">X</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=y">Y</a>
							</li>
							<li class="AspNet-Menu-Leaf">
								<a href="gobacktionary.php?letter=z">Z</a>
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

$select = $connection->prepare("SELECT code.name AS name, code.description AS description, code.url AS url, alias.name AS alias FROM code LEFT JOIN alias ON (code.name = alias.code AND alias.institution = :inst1) WHERE {$where} AND code.institution = :inst2 AND public = 1 ORDER BY code.description, code.url, code.name, alias.name");
$select->bindValue(":inst1", $institution);
$select->bindValue(":inst2", $institution);
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