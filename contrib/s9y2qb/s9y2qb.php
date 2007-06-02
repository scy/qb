<?php

// Exports entries from a s9y installation to qb's data format.
// Place a copy or symlink of serendipity_config_local.inc.php into
// the directory you run this software from. It will access your
// data for reading only. Specifiy the numeric user ID as command
// line parameter. Yes, this means that this is a CLI script.

// Author: Tim 'Scytale' Weber <scy-dev-qb@scytale.de> on 2007-05-23.
// Public domain, no warranty, all that stuff, you know.

// Directory to export to.
$dir = 's9y2qb-export';
// Suffix of the data files.
$suf = '.qb';
// Suffix of the meta files.
$muf = '.cre';

function addquotes($string) {
	return ((strpos($string, ' ') === false)?
	        ($string):
		('"' . htmlspecialchars($string, ENT_NOQUOTES) . '"'));
}

$uid = (int)$_SERVER['argv'][1] or die("Please supply a user ID.\n");

require_once('serendipity_config_local.inc.php');
$p = $serendipity['dbPrefix'];

$m = mysql_connect($serendipity['dbHost'], $serendipity['dbUser'], $serendipity['dbPass']) or die("Cannot connect to database server.\n");
echo("Connected to database server.\n");

mysql_select_db($serendipity['dbName']) or die("Could not select database.\n");
echo("Database selected.\n");

$q = mysql_query("SET CHARACTER SET 'utf8'") or die("Could not set character set to UTF8.\n");

$q = mysql_query("SELECT realname FROM ${p}authors WHERE authorid=$uid") or die("Query error while getting author information.");
if (mysql_num_rows($q) != 1) die("No author with ID $uid.\n");
$r = mysql_fetch_assoc($q);
echo("Will export entries from author '".$r['realname']."'.\n");

$q = mysql_query("SELECT categoryid AS id, category_name AS name, parentid AS parent FROM ${p}category") or die("Query error while getting categories.\n");
if (mysql_num_rows($q) == 0) echo("Warning: No categories found.\n");
while ($r = mysql_fetch_assoc($q)) {
	$cat[$r['id']] = array('name' => $r['name'], 'parent' => $r['parent']);
}
foreach ($cat as $k => $v) {
	$cat[$k]['tags'] = array($v['name']);
	$parent = $v['parent'];
	while ($parent != 0) {
		$cat[$k]['tags'][] = $cat[$parent]['name'];
		$parent = $cat[$parent]['parent'];
	}
}
echo("Categories read.\n");

if (!is_dir($dir)) mkdir($dir, 0700, true) or die("Could not create directory '$dir'.\n");
chdir($dir) or die("Could not change into directory '$dir'.\n");
echo("Exporting to $dir with suffix $suf and meta-suffix $muf...\n");
$i = 0;
while (true) {
	$q = mysql_query("SELECT id, title, timestamp AS created, body, extended, last_modified AS modified, (SELECT GROUP_CONCAT(categoryid SEPARATOR ' ') FROM ${p}entrycat WHERE entryid = id) AS cats FROM ${p}entries WHERE authorid=$uid AND isdraft='false' LIMIT $i,1") or die("Query error while getting article. Export the article manually by LIMITing a SELECT on ${p}entries to $i,1.\n");
	if (mysql_num_rows($q) == 0) break;
	$r = mysql_fetch_assoc($q);
	echo($r['id'] . ': "' . $r['title'] . '"... ');
	$txt = '<:' . htmlspecialchars($r['title']) . '><tags:';
	$acats = explode(' ', $r['cats']);
	$tags = array();
	foreach ($acats as $acat) {
		if ($acat != 0)
			$tags = array_merge($tags, $cat[$acat]['tags']);
	}
	$tags = array_unique($tags);
	$txt .= implode(' ', array_map('addquotes', $tags)) . ">\n";
	$txt .= $r['body'] . "\n" . (($r['extended'])?($r['extended'] . "\n"):(''));
	$fname = $r['id'] . $suf;
	$mname = $r['id'] . $muf;
	file_put_contents($fname, str_replace("\r", '', $txt)) or die("Could not write to '$fname'.\n");
	touch($fname, (int)$r['modified']) or die("Could not change mod time of '$fname'.\n");
	touch($mname, (int)$r['created']) or die("Could not change mod time of '$mname'.\n");
	echo("done.\n");
	$i++;
}

?>
