<?php
//gives us access to connection.
require_once "../go.php";

//header("Content-Type: application/rss+xml; charset=ISO-8859-1");

$select = $connection->prepare("
	(SELECT
  	*
	FROM
  	log
  INNER JOIN
  	code
  ON
  	log.code = code.name
  WHERE
  	log.description
  LIKE
  	'%Created%'
  AND
  	public = 1
  ORDER BY
  	tstamp
  DESC
  LIMIT
  	10)
");

$select->execute();
$results = $select->fetchAll();

print '<rss version="2.0">
	<channel>
		<title>Latest GO Shortcuts</title>
		<link>http://go.middlebury.edu/</link>
		<description>A feed for admins to keep track of new GO codes</description>';

foreach ($results as $item) {
	$decription = '<p>'.$item['tstamp'].'. Created by '.$item['user_display_name'].'.</p><p>'.$item['description'].'</p><p>Link: ('.$item['url'].')</p>';
	print '<item>
			<title>'.$item['code'].' (aliases: '.($item['alias'] != '' ? $item['alias'] : "none" ).')</title>
			<link>'.$item['url'].'</link>
			<description>'.htmlspecialchars($decription).'</description>
		</item>';
}

print '	</channel>
</rss>';