<?php

/* Copyright 2007 Tim Weber <scy-proj-qb@scytale.name>

   This file is part of qb <http://scytale.name/proj/qb/>.

   See the LICENSE file for legal stuff.
   */

// Include configuration file.
require_once('qb-0.2.conf.php');

// Define MIME types for the different templates.
$mime = array(
	'html'   => 'text/html',
	'atom10' => 'application/atom+xml',
);

// Assign the requested virtual file to $url, removing strange injection ideas.
$url = str_replace(array(':', '../'), '', qbURL::getVFile());

// Let $realpath be qb's source directory plus the requested file.
$realpath = QB_SRC.$url;
// Initialize the array of matches. It's two-dimensional. The first index is the
// "created" Unix timestamp, the second one is running from 0 to n-1, where n is
// the number of files created at that time. The actual value then is the file
// name without suffix.
$matches = array();
// TODO: Can this be rewritten to work without distinction between "single file"
// and "directory" case?
if (is_file($realpath.QB_SUF_SRC)) {
	// A single file has been requested. Add it to $matches.
	$matches[qb_created($url)][] = $url;
} elseif (is_dir($realpath)) {
	// A directory has been requested. Initialize $scanq with the directory name
	// and do a breadth first search.
	$scanq = array($realpath);
	while (count($scanq) > 0) { // As long as there are elements in the queue...
		// Set $curdir to the first $scanq value, remove it from $scanq.
		$curdir = array_shift($scanq);
		// Return an array containing all the files and directories in it.
		$scan = scandir($curdir);
		foreach ($scan as $hit) {
			$foo = "$curdir/$hit";
			if ((is_file($foo)) && (substr($foo, 0 - strlen(QB_SUF_SRC)) == QB_SUF_SRC)) {
				// It's a file and ends with qb's suffix. Remove the suffix...
				$path = substr($foo, strlen(QB_SRC), 0 - strlen(QB_SUF_SRC));
				// ...and add it to the matches.
				$matches[qb_created($path)][] = $path;
			} elseif ((is_dir($foo)) && ($hit != '.') && ($hit != '..')) {
				// It's a directory and not "." or "..", add it to the queue.
				$scanq[] = $foo;
			}
		}
	}
}

// Requested page defaults to 1.
$page = 1;
// Choose the template to use. Defaults to first in $mime.
$templates = array_keys($mime);
$template = $templates[0];
if (($qs = $_SERVER['QUERY_STRING']) != '') {
	// If there's a query string, check if it's a template name.
	if (array_key_exists($qs, $mime)) {
		// It's a template name, so choose this as the template.
		$template = $qs;
		// If feeds should be shortened, set $page to 1, else to -1.
		$page = (QB_SHORTEN_FEEDS) ? (1) : (-1);
	} elseif ((is_numeric($qs)) && ($qs > 0)) {
		// If it's a positive integer, use it as page number.
		$page = (int)$qs;
	}
}

// Sort by date created, newest first.
krsort($matches, SORT_NUMERIC);
$items = array();
// Flatten the matches, sorting "synchronous" files alphabetically.
foreach ($matches as $paths) {
	sort($paths, SORT_STRING);
	$items = array_merge($items, $paths);
}
// Default values for pages, if pagination is not used.
$meta['numpages'] = $meta['thispage'] = 1;
if (defined('QB_MAXITEMS') && $page != -1) { // If pagination is in use.
	// How many pages are there?
	$meta['numpages'] = ceil(count($items) / QB_MAXITEMS);
	// If there's more than one page, set "pages".
	if ($meta['numpages'] > 1) {
		$meta['pages'] = $meta['numpages'];
	}
	// What page is this?
	$meta['thispage'] = $page;
	// If not everything should be output:
	if ($page != -1) {
		// Is there a next page?
		if ($page < $meta['numpages']) {
			$meta['nextpage'] = $page + 1;
		}
		// Is there a previous page?
		if ($page > 1) {
			$meta['prevpage'] = $page - 1;
		}
		// Extract only those values that match the requested page.
		$items = array_slice($items, ($page - 1) * QB_MAXITEMS, QB_MAXITEMS);
	}
}

// If there has been at least one file selected:
if (count($items) > 0) {
	// $content will be the content the page template sees.
	$content = '';
	// $lastmtime will contain the latest mtime. Ya Rly.
	$lastmtime = 0;
	foreach ($items as $match) {
		// Add a single article to $content.
		$content .= qb_buildpage($match, $template);
		// Set $mtime to the file's mtime.
		$mtime = filemtime(QB_SRC.$match.QB_SUF_SRC);
		// Update $lastmtime, if necessary.
		if ($mtime > $lastmtime)
			$lastmtime = $mtime;
	}
	// Set template variables "content" and "modified".
	$meta['content'] = $content;
	if ($lastmtime != 0)
		$meta['modified'] = $lastmtime;
} else {
	// Show an error instead of simply sending zero bytes (which confuses some
	// browsers).
	$meta['content'] = QB_FOUROHFOUR;
	$meta['modified'] = time();
	@header('HTTP/1.0 404 Not Found');
}

// Set URL scheme and host and port for permalinks, atom and so on.
$scheme = 'http'; $schemedefaultport = 80;
if ((isset($_SERVER['HTTPS'])) && ($_SERVER['HTTPS'] == 'on')) {
	$scheme = 'https'; $schemedefaultport = 443;
}
$meta['urlbase'] = "$scheme://" . $_SERVER['SERVER_NAME'] . ((($port = $_SERVER['SERVER_PORT']) == $schemedefaultport) ? ('') : (":$port"));
// Set URL path for query string fun (pagination and stuff).
// FIXME: Workaround for root dir problem should go.
$meta['urlpath'] = preg_replace('|/+|', '/', qbURL::getHandler() . $url);
// "basepath" contains the base path for CSS and stuff, not ending with a slash.
$meta['basepath'] = qbURL::getBasePath();
// "handler" is the handler path.
$meta['handler'] = qbURL::getHandler();
// Author and site name.
$meta['author'] = QB_AUTHOR;
$meta['blogname'] = QB_BLOGNAME;
// Throw out a Content-type and charset, if we still can.
@header('Content-type: '.$mime[$template].'; charset=UTF-8');
// And now the final page. U can has cheezburger now.
echo(qb_template(QB_TPL_PAGE.'.'.$template, $meta));

// Oh, one final thing: If QB_END_CALLBACK is defined, call it.
if (defined('QB_END_CALLBACK'))
	call_user_func(QB_END_CALLBACK);

// Generate an article from an input file (supplied without prefix or suffix).
// $template can be set to the template to use, defaults to "html".
function qb_buildpage($path, $template = 'html') {
	// Set $filename to the real filename.
	$filename = QB_SRC.$path.QB_SUF_SRC;
	// Check whether that really is under the source directory.
	if (!qbString::startsWith(realpath(QB_SRC), realpath($filename), false))
		return ('');
	// Set $t to the contents of the file.
	if (($t = @file_get_contents($filename)) === false) {
		// If the file doesn't exist, return an error.
		// TODO: This never happens, because main logic already checks for the
		// existence of the file.
		return (qb_template(QB_TPL_PAGE.'.'.$template, array(
			'title'   => 'file not found',
			'content' => '<p class=\'qb_error\'>The file ' . htmlspecialchars($path) . ' could not be found.</p>',
		)));
	} else {
		// File exists. Let $page[0] be the first line, $page[1] be the rest.
		$page = explode("\n", $t, 2);
		// Throw all tags in the first line into $tokens.
		preg_match_all('|<(.*):(.*)>(?!>)|U', $page[0], $tokens, PREG_SET_ORDER);
		// Initialize $meta as empty array.
		$meta = array();
		foreach ($tokens as $token) {
			// Let $k be the name of the tag, $v its value.
			$k = $token[1]; $v = $token[2];
			if ($k == '') {
				// Special case: the <:...> tag is a shortcut for <title:...>
				$k = 'title';
			}
			if ($k == 'tags') {
				// Special case: expand the "tags" tag via a freaky regex that
				// splits them by spaces, multiword tags can be supplied by
				// using quotation marks. Should resemble Flickr's tag parsing
				// logic somehow.
				preg_match_all('%"(?! )([^"]+)(?<! )"|([^ ]+)%', $v, $tags, PREG_PATTERN_ORDER);
				// Initialize $parsedtags.
				$parsedtags = array();
				foreach ($tags[0] as $tag) {
					// Remove quotation marks and spaces.
					$tag = str_replace('"', '', trim($tag));
					// If there's something left, add it to $parsedtags array.
					if (strlen($tag) > 0)
						$parsedtags[] = $tag;
				}
				// Remove duplicate values, save the result in $v.
				$v = array_unique($parsedtags);
				if (count($v) > 0) {
					// If there are any tags left, create a "spantags" template
					// variable and put them in it. The "tags" variable stays
					// an array. No idea what's the use in it, but whatever.
					$meta['spantags'] = '<span class=\'tag\'>' . implode('</span> <span class=\'tag\'>', $v) . '</span>';
					// Create a funky "ultags" for people who prefer that.
					$meta['ultags'] = '<ul class=\'tag\'><li>' . implode("</li>\n<li>", $v) . '</li></ul>';
				}
			}
			// Create a template variable with that value.
			$meta[$k] = $v;
		}
		// "path" contains the handler plus $path, double slashes removed.
		$meta['path'] = preg_replace('|/+|', '/', qbURL::getHandler() . $path);
		// "content" contains the second and all following lines.
		$meta['content'] = $page[1];
		// "escapedcontent" is like "content", but with escaped HTML characters.
		$meta['escapedcontent'] = htmlspecialchars($meta['content']);
		// "created" is the date the file was created.
		if (!array_key_exists('created', $meta))
			$meta['created'] = qb_created($path);
		// "modified" is the modification date of the file.
		// "modified" and "created" can be overriden in the meta line.
		if (!array_key_exists('modified', $meta))
			$meta['modified'] = filemtime($filename);
		if (($meta['modified'] == '!') ||
		    (abs($meta['modified'] - $meta['created']) <= QB_OOPSTIME)
		   )
			// If set to ! or in oops tolerance, simulate an unmodified file.
			$meta['modified'] = $meta['created'];
		// If the two timestamps differ, set "wasmodified".
		if ($meta['created'] != $meta['modified'])
			$meta['wasmodified'] = $meta['modified'];
		// Run the template, return the result.
		return (qb_template(QB_TPL_ARTICLE.'.'.$template, $meta));
	}
}

// Unix has no file creation times, bitches. Therefore we need an ugly hack.
function qb_created($path) {
	// $cfile is the "creation file" of the qb file in $path.
	$cfile = QB_META.$path.QB_SUF_CRE;
	if (!file_exists($cfile)) {
		// If it doesn't exist yet, we'll create it. First set $ctime to "now".
		$ctime = time();
		// Then, set $mtime to the modification date of the file.
		$mtime = filemtime(QB_SRC.$path.QB_SUF_SRC);
		// If it has been modified before "now" (should be the case), set $ctime
		// to $mtime.
		if ($mtime < $ctime)
			$ctime = $mtime;
		// Create the directory for the $cfile (recursively), if needed.
		if (!file_exists(dirname($cfile)))
			mkdir(dirname($cfile), 0775, true);
		// Create the $cfile and put the timestamp inside as a backup.
		file_put_contents($cfile, $ctime);
		// Finally set its modification date, which serves as the creation date
		// from now on.
		touch($cfile, $ctime);
		// Return the time we set.
		return ($ctime);
	} else {
		// The cfile exists, return its modification time.
		return (filemtime($cfile));
	}
}

// Runs a bunch of patterns (regex=>replacement array) over a string.
function qb_runpattern($pattern, $string) {
	foreach ($pattern as $k => $v) {
		if (!is_array($v)) {
			// This !is_array() is to catch the "tags" variable.
			$string = (is_object($v) && $v instanceof \Closure)
				? preg_replace_callback($k, $v, $string)
				: preg_replace($k, $v, $string);
		}
	}
	return ($string);
}

// This is the funky template engine. Supply a template filename and template
// variable array.
function qb_template($template, $data) {
	// Access the regexes from the config file.
	global $qb_regex;
	// $regex will hold the regexes generated from the supplied variables.
	$regex = array();
	// Iterate over each supplied template variable ($k) and its value ($v):
	foreach ($data as $k => $v) {
		// $nk is the "normalized key", making sure that template variables
		// consist only of a-z0-9.
		$nk = preg_replace('[^a-z0-9]', '', strtolower($k));
		// Add a regex that will replace a template tag with the value.
		$regex['|<qb:' . $nk . ' */ *>|U'] = str_replace(
			array('\\', '$'), array('\\\\', '\\$'), $v);
		// Create regexes for the corresponding <qb:ifset:...> tags.
		$regex['|<qb:ifset:(' . $nk . ') *>(.*)</qb:ifset:\\1 *>|Us'] = '$2';
		// Create regexes for the corresponding <qb:ifnotset:...> tags.
		$regex['|<qb:ifnotset:(' . $nk . ') *>.*</qb:ifnotset:\\1 *>|Us'] = '';
	}
	// Create a regex that will eat all other <qb:ifset:...> tags.
	$regex['|<qb:ifset:([a-z0-9]+) *>.*</qb:ifset:\\1 *>|Us'] = '';
	// Create a regex that will apply to all other <qb:ifnotset:...> tags.
	$regex['|<qb:ifnotset:([a-z0-9]+) *>(.*)</qb:ifnotset:\\1 *>|Us'] = '$2';
	// Create a regex for the date magic.
	$regex['|<qb:date>([0-9]+) *([^<>]+)</qb:date>|m'] = function ($m) { return date($m[2], $m[1]); };
	// Create a regex for the path magic.
	$regex['|<qb:cleanpath>([^<>]+)</qb:cleanpath>|m'] = function ($m) { return preg_replace('|/+|', '/', $m[1]); };
	// Merge the generated and the configured regexes. Configured ones overwrite
	// generated ones.
	$regex = array_merge($regex, $qb_regex);
	// Pour the template file into $t.
	$t = file_get_contents($template);
	// Run all the patterns...
	$t = qb_runpattern($regex, $t);
	// ...and return the result.
	return ($t);
}

?>
