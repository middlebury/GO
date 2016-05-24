<?php
// go_functions provides access to the function curPageURL()
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
					<li><a href="gobacktionary.php?letter=[0-9]">#</a></li>
					<li><a href="gobacktionary.php?letter=a">A</a></li>
					<li><a href="gobacktionary.php?letter=b">B</a></li>
					<li><a href="gobacktionary.php?letter=c">C</a></li>
					<li><a href="gobacktionary.php?letter=d">D</a></li>
					<li><a href="gobacktionary.php?letter=e">E</a></li>
					<li><a href="gobacktionary.php?letter=f">F</a></li>
					<li><a href="gobacktionary.php?letter=g">G</a></li>
					<li><a href="gobacktionary.php?letter=h">H</a></li>
					<li><a href="gobacktionary.php?letter=i">I</a></li>
					<li><a href="gobacktionary.php?letter=j">J</a></li>
					<li><a href="gobacktionary.php?letter=k">K</a></li>
					<li><a href="gobacktionary.php?letter=l">L</a></li>
					<li><a href="gobacktionary.php?letter=m">M</a></li>
					<li><a href="gobacktionary.php?letter=n">N</a></li>
					<li><a href="gobacktionary.php?letter=o">O</a></li>
					<li><a href="gobacktionary.php?letter=p">P</a></li>
					<li><a href="gobacktionary.php?letter=q">Q</a></li>
					<li><a href="gobacktionary.php?letter=r">R</a></li>
					<li><a href="gobacktionary.php?letter=s">S</a></li>
					<li><a href="gobacktionary.php?letter=t">T</a></li>
					<li><a href="gobacktionary.php?letter=u">U</a></li>
					<li><a href="gobacktionary.php?letter=v">V</a></li>
					<li><a href="gobacktionary.php?letter=w">W</a></li>
					<li><a href="gobacktionary.php?letter=x">X</a></li>
					<li><a href="gobacktionary.php?letter=y">Y</a></li>
					<li><a href="gobacktionary.php?letter=z">Z</a></li>
				</ul>
			</nav>
			<div class="content">
				<p><b>GO needs your help!</b> Find out how you can help by logging in to our <a href="admin.php">new self-service shortcut creation interface</a>!</p>
				<p>You can also <a href="gotionary.php">view this list, sorted by the shortcut</a>.</p>
<?php

global $connection;

$where = "url LIKE 'http://{$letter}%' OR url LIKE 'https://{$letter}%' OR url LIKE 'http://www.{$letter}%' OR url LIKE 'https://www.{$letter}%'";
if ($letter == "[0-9]") {
	$where = "description LIKE '0%' OR description LIKE '1%' OR description LIKE '2%' OR description LIKE '3%' OR description LIKE '4%' OR description LIKE '5%' OR description LIKE '6%' OR description LIKE '7%' OR description LIKE '8%' OR description LIKE '9%'";
}

$select = $connection->prepare("SELECT code.name AS name, code.description AS description, code.url AS url, alias.name AS alias FROM code LEFT JOIN alias ON (code.name = alias.code AND alias.institution = :inst1) WHERE {$where} AND code.institution = :inst2 AND public = 1 ORDER BY code.url, alias.name, code.name, code.description");
$select->bindValue(":inst1", $institution);
$select->bindValue(":inst2", $institution);
$select->execute();

// Collect all the rows for manipulation
$rows = $select->fetchAll(PDO::FETCH_ASSOC);

$sort = array();

// Strip the protocol and www from urls
foreach ($rows as $key => &$row) {
    $urlParts = parse_url($row['url']);
    $url = preg_replace('/^www\./', '', $urlParts['host']);
    if(isset($urlParts['path'])) {
        $url = $url . $urlParts['path'];
    }
    if(isset($urlParts['query'])) {
        $url = $url . $urlParts['query'];
    }
    $row['print_url'] = $url;
    $sort[$key] = $row['print_url'];
}

array_multisort($sort, SORT_ASC, $rows);

$lines = array();
$current_url = "";
print "<p>&nbsp;";

foreach ($rows as $row) {
  
  if ($current_url != $row['url']) {
    print "</p>";
    print "\n<p class='gobacktionary_info'>";
    print "<a href=\"info.php?code=".$row['name']."\" class='info_link' title='Show Shortcut Information'>";
	if (Code::isUrlValid($row['url']))
		print "<img src='icons/info.png' alt='info'/>";	
	else
		print "<img src='icons/alert.png' alt='alert'/>";	
	print "</a> &nbsp; ";    
    print "</p>\n<p class='gobacktionary_shortcut'>";

    print htmlentities($row['description']);
  
  //add rel=nofollow and external class to external links
	$host_url = parse_url($row['url'], PHP_URL_HOST);
	$internal_host = false;  
	foreach ($internal_hosts as $host) {
		if (preg_match($host, $host_url)) {
			$internal_host = true;
		}
	}
	if (!$internal_host) {
		print "<br />&nbsp;&nbsp;&nbsp;<a class='external' rel='nofollow' href=\"".Go::getShortcutUrl($row['name'], $institution)."\">go/".htmlentities($row['name'])."</a> (". $row['print_url'] .")";
	} else {
		print "<br />&nbsp;&nbsp;&nbsp;<a href=\"".Go::getShortcutUrl($row['name'], $institution)."\">go/".htmlentities($row['name'])."</a> (". $row['print_url'] .")";
	}
    
  }
  
  if ($row['alias']) {
    print "<br />&nbsp;&nbsp;&nbsp;<a href=\"".Go::getShortcutUrl($row['name'], $institution)."\">go/" . $row['alias'] . "</a>";
  }

  $current_url = $row['url'];
}
?>
				</p>
			</div>
		</div>
<?php
	require_once "footer.php";