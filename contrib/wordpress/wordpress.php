<?php

// Exports entries from a wordpress installation to qb's data format.
// Place a copy or symlink of wp-config.php into the directory you run
// this software from. It will access your data for reading only.
// Please also add an empty wp-settings.php!

// Author: Uli 'psychon' Schlachter, based on s9y2qb.php from Scytale.
// Public domain, no warranty, all that stuff, you know.

// Directory to export to.
$dir = 'export';
// Suffix of the data files.
$suf = '.qb';
// Suffix of the meta files.
$muf = '.cre';

error_reporting(E_ALL);

if (php_sapi_name() != 'cli')
	die("This needs to be run from the command line!\n");

if (@filesize('wp-settings.php') !== 0)
	die("Please don't run this in your wordpress dir and ".
		"add an empty wp-settings.php file here\n");

require_once('wp-config.php');

$db = mysql_connect(DB_HOST, DB_USER, DB_PASSWORD)
	or die("Could not connect to database server.\n");

mysql_select_db(DB_NAME, $db)
	or die("Could not select database.\n");

echo "Connected to database.\n";

function query($q)
{
	global $db;

	$r = mysql_query($q, $db)
		or die("MySQL query failed!\n");

	return $r;
}

function make_dir($dir)
{
	if (!is_dir($dir))
		mkdir($dir, 0777, true)
			or die("Could not create directory '$dir'.\n");
}

// This removes extra <br /> tags inside a <pre>
function remove_br($matches)
{
	return str_replace(array('<br />', '<p>', '</p>'), '', $matches[0]);
}

// Turn a MySQL datetime field into an unix timestamp
function datetime($date)
{
	$hour = substr($date, 11, 2);
	$min = substr($date, 14, 2);
	$sec = substr($date, 17, 2);
	$mon = substr($date, 5, 2);
	$day = substr($date, 8, 2);
	$year = substr($date, 0, 4);
	return mktime($hour, $min, $sec, $mon, $day, $year);
}

// If a tag contains a space, it needs quotes
function tag_quotes($string)
{
	if (strpos($string, ' ') === false)
		return $string;
	return '"'.htmlspecialchars($string, ENT_NOQUOTES).'"';
}

query("SET CHARACTER SET '".DB_CHARSET."';");

// TODO DB_COLLATE setzen? wie?

echo "Reading tags and categories... ";

$res = query("SELECT terms.name, rel.object_id ".
	"FROM ".$table_prefix."terms AS terms, ".
	$table_prefix."term_relationships AS rel, ".
	$table_prefix."term_taxonomy AS tax ".
	"WHERE terms.term_id = tax.term_id ".
	"AND tax.term_taxonomy_id = rel.term_taxonomy_id");

$tags = array();
while (($r = mysql_fetch_assoc($res)) !== false) {
	$id = $r['object_id'];
	$tag = $r['name'];

	if (!isset($tags[$id]))
		$tags[$id] = array();

	$tags[$id][] = tag_quotes($tag);
}
echo "Done.\n";

make_dir($dir.'/src');
make_dir($dir.'/meta');

$res = query("SELECT id, post_date, post_content, post_title, post_name, ".
	"post_modified FROM ".$table_prefix."posts WHERE post_type='post';");
chdir($dir) or die("Could not change into directory '$dir'.\n");
echo "Exporting to '$dir' with suffix $suf and meta-suffix $muf...\n";

while (($r = mysql_fetch_assoc($res)) !== false) {
	echo $r['id'] . ': "' . $r['post_title'] . '"... ';

	$content = $r['post_content'];
	$content = str_replace("\r\n\r\n", "</p>\n<p>", $content);
	$content = str_replace("\r\n", "<br />\n", $content);
	$content = "<p>$content</p>\n";

	// The above inserts extra <br /> inside of some tags, fix that
	$content = preg_replace_callback('|<pre>.*</pre>|Uims', 'remove_br',
		$content);
	$content = preg_replace_callback('|<ul>.*</ul>|Uims', 'remove_br',
		$content);
	$content = preg_replace_callback('|<ol>.*</ol>|Uims', 'remove_br',
		$content);

	$text = '<:'.htmlspecialchars($r['post_title']).'>';

	if (isset($tags[$r['id']])) {
		array_unique($tags[$r['id']]);
		$text .= '<tags:'.implode(' ', $tags[$r['id']]).'>';
	}

	$text .= "\n";
	$text .= $content;

	$src_name  = 'src/' . $r['post_name'] . $suf;
	$meta_name = 'meta/'. $r['post_name'] . $muf;

	file_put_contents($src_name, $text)
		or die("Could not write to '$src_name'\n");
	file_put_contents($meta_name, (int)$r['post_date'])
		or die("Could not write to '$meta_name'\n");

	touch($src_name, datetime($r['post_modified']))
		or die ("Could not change mod time of '$src_name'\n");
	touch($meta_name, datetime($r['post_date']))
		or die ("Could not change mod time of '$src_name'\n");

	echo "done.\n";
}

echo "Export finished\n";

echo "\nPlease note: Links for downloads, you own blog entries and images\n".
	"still point at your old blog!\n";

?>
