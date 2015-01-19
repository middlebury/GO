<?php
//go_functions.php gives us access to the isSuperAdmin function 
require_once "go_functions.php";
require_once "config.php";
require_once "go.php";
require_once "header.php";
$letter = "a";
if (isset($_GET["letter"]) && preg_match("/^([A-Za-z0-9]|\[0-9\])$/", $_GET["letter"]) === 1) {
	$letter = $_GET["letter"];
}
?>
			<nav class="gonav">
				<ul>
					<li><a href="gotionary.php?letter=[0-9]">#</a></li>
					<li><a href="gotionary.php?letter=a">A</a></li>
					<li><a href="gotionary.php?letter=b">B</a></li>
					<li><a href="gotionary.php?letter=c">C</a></li>
					<li><a href="gotionary.php?letter=d">D</a></li>
					<li><a href="gotionary.php?letter=e">E</a></li>
					<li><a href="gotionary.php?letter=f">F</a></li>
					<li><a href="gotionary.php?letter=g">G</a></li>
					<li><a href="gotionary.php?letter=h">H</a></li>
					<li><a href="gotionary.php?letter=i">I</a></li>
					<li><a href="gotionary.php?letter=j">J</a></li>
					<li><a href="gotionary.php?letter=k">K</a></li>
					<li><a href="gotionary.php?letter=l">L</a></li>
					<li><a href="gotionary.php?letter=m">M</a></li>
					<li><a href="gotionary.php?letter=n">N</a></li>
					<li><a href="gotionary.php?letter=o">O</a></li>
					<li><a href="gotionary.php?letter=p">P</a></li>
					<li><a href="gotionary.php?letter=q">Q</a></li>
					<li><a href="gotionary.php?letter=r">R</a></li>
					<li><a href="gotionary.php?letter=s">S</a></li>
					<li><a href="gotionary.php?letter=t">T</a></li>
					<li><a href="gotionary.php?letter=u">U</a></li>
					<li><a href="gotionary.php?letter=v">V</a></li>
					<li><a href="gotionary.php?letter=w">W</a></li>
					<li><a href="gotionary.php?letter=x">X</a></li>
					<li><a href="gotionary.php?letter=y">Y</a></li>
					<li><a href="gotionary.php?letter=z">Z</a></li>
				</ul>
			</nav>
			<div class="content">
				<p><b>GO needs your help!</b> Find out how you can help by logging in to our <a href="admin.php">self-service shortcut creation interface</a>!</p>
				<p>You can also <a href="gobacktionary.php">view this list, sorted by the destination</a> 
				<?php
				// Let users determine if they see all codes or only public codes
				// basically show a different link depending on if the user has chosen
				// to see public or all this session
				// Assume public
				if (!isset($_SESSION['toggle_all'])) {
					$_SESSION['toggle_all'] = 'public';
				}
				// Depending on settings in config display
				// possibly change the "toggle_all" to "all"
				if (GO_SHOW_ALL_CODES_ACCESS == 'all' || (GO_SHOW_ALL_CODES_ACCESS == 'authenticated' && isset($_SESSION["AUTH"])) || (GO_SHOW_ALL_CODES_ACCESS == 'superadmin' && isSuperAdmin())) {
					// Set public or all depending on which link was clicked
					if (isset($_GET['display'])) {
						if ($_GET['display'] == 'public') {
							$_SESSION['toggle_all'] = 'public';
						}
						if ($_GET['display'] == 'all') {
							$_SESSION['toggle_all'] = 'all';
						}
					}
				
					// Show the appropriate link (include letter so it stays on the right page)
					if ($_SESSION['toggle_all'] == 'public') {
						print " or <a href=\"gotionary.php?display=all&amp;letter=".$letter."\">include all hidden codes</a>.";
					}
					if ($_SESSION['toggle_all'] == 'all') {
						print " or <a href=\"gotionary.php?display=public&amp;letter=".$letter."\">exclude all hidden codes</a>.";
					}
				} //end if (GO_SHOW_ALL_CODES_ACCESS == 'all' || (GO_SHOW_ALL_CODES_ACCESS == 'authenticated' && isset($_SESSION["AUTH"]) || (GO_SHOW_ALL_CODES_ACCESS == 'superadmin' && isSuperAdmin())) {
				?>
				</p>
<?php

global $connection;
 
$where = "name LIKE '{$letter}%'";
if ($letter == "[0-9]") {
	$where = "(name LIKE '0%' OR name LIKE '1%' OR name LIKE '2%' OR name LIKE '3%' OR name LIKE '4%' OR name LIKE '5%' OR name LIKE '6%' OR name LIKE '7%' OR name LIKE '8%' OR name LIKE '9%')";
}
// We need a different query for getting all codes and only the public codes
// All
if ($_SESSION['toggle_all'] == 'all') {
	$select = $connection->prepare("SELECT name, description, url FROM code WHERE {$where} AND institution = :institution ORDER BY name");
}
// Public
else {
	$select = $connection->prepare("SELECT name, description, url FROM code WHERE {$where} AND institution = :institution AND public = 1 ORDER BY name");
}

$select->bindValue(":institution", $institution);
$select->execute();

$lines = array();

while($row = $select->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
	$line = "\n\t<p>";
	$line .= "<a href=\"info.php?code=".$row->name."\" class='info_link' title='Show Shortcut Information'>";
	if (Code::isUrlValid($row->url))
		$line .= "<img src='icons/info.png' alt='info'/>";	
	else
		$line .= "<img src='icons/alert.png' alt='alert'/>";	
	$line .= "</a> &nbsp; &nbsp; ";
	//add rel=nofollow and extrnal class to external links
	$host_url = parse_url($row->url, PHP_URL_HOST);
	$internal_host = false;
	foreach ($internal_hosts as $host) {
		if (preg_match($host, $host_url)) {
			$internal_host = true;
		}
	}
	if (!$internal_host) {
		$line .= "<a class='external' rel='nofollow' href=\"".Go::getShortcutUrl($row->name, $institution)."\">go/".htmlentities($row->name)."</a>";
	} else {
		$line .= "<a href=\"".Go::getShortcutUrl($row->name, $institution)."\">go/".htmlentities($row->name)."</a>";
	}

	if($row->description != "") {
		$line .= " - ".htmlentities($row->description);
	}
		
	$line .= "</p>";
	$lines[$row->name] = $line;
}

$where = str_replace("name", "alias.name", $where);
// We need a different query for getting all codes and only the public
// All
if ($_SESSION['toggle_all'] == 'all') {
	$alias = $connection->prepare("SELECT alias.name AS name, code.description AS description, code.url FROM alias JOIN code ON (alias.code = code.name) WHERE {$where} AND alias.institution = :institution ORDER BY alias.name");
}
// Public
else {
	$alias = $connection->prepare("SELECT alias.name AS name, code.description AS description, code.url FROM alias JOIN code ON (alias.code = code.name) WHERE {$where} AND alias.institution = :institution AND code.public = 1 ORDER BY alias.name");
}
$alias->bindValue(":institution", $institution);
$alias->execute();
while($row = $alias->fetch(PDO::FETCH_LAZY, PDO::FETCH_ORI_NEXT)) {
	$line = "\n\t<p>";
	$line .= "<a href=\"info.php?code=".$row->name."\" class='info_link' title='Show Shortcut Information'>";
	if (Code::isUrlValid($row->url))
		$line .= "<img src='icons/info.png' alt='info'/>";	
	else
		$line .= "<img src='icons/alert.png' alt='alert'/>";	
	$line .= "</a> &nbsp; &nbsp; ";
	//add rel=nofollow and extrnal class to external links
	$host_url = parse_url($row->url, PHP_URL_HOST);
	$internal_host = false;
	foreach ($internal_hosts as $host) {
		if (preg_match($host, $host_url)) {
			$internal_host = true;
		}
	}
	if (!$internal_host) {
		$line .= "<a class='external' rel='nofollow' href=\"".Go::getShortcutUrl($row->name, $institution)."\">go/".htmlentities($row->name)."</a>";
	} else {
		$line .= "<a href=\"".Go::getShortcutUrl($row->name, $institution)."\">go/".htmlentities($row->name)."</a>";
	}

	if($row->description != "") {
		$line .= " - ".htmlentities($row->description);
	}

	$line .= "</p>";
	$lines[$row->name] = $line;
}

// Sort the lines using case-insensitive sorting.
$sortKeys = array();
$tempLines = array();
foreach ($lines as $name => $line) {
	$tempLines[] = $line;
	$sortKeys[] = strtolower($name);
}
array_multisort($sortKeys, SORT_ASC, SORT_STRING, $tempLines);
$lines = $tempLines;

?>
				<table border="0" width="100%" class="gotionary">
<?php
$i = 0;
$count = count($lines) / 2;

for ($i = 0; $i <= $count; $i++) {
	$left = !empty($lines[$i]) ? $lines[$i] : '';
	$right = !empty($lines[$i+$count]) ? $lines[$i+$count] : '';
  print "<tr><td>" . $left . "</td><td>" . $right . "</td></tr>";
}
?>
				</table>
<?php
	require_once "footer.php";