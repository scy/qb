<?php

// Include configuration file.
require_once('qb.cfg.php');

// Define MIME types for the different templates.
$mime = array(
	'html'   => 'text/html',
	'atom10' => 'application/atom+xml',
);

// Make $requri a tuple consisting of "everything before the first question
// mark" and "everything after it". (The host fragment is NOT included.)
$requri = explode('?', $_SERVER['REQUEST_URI'], 2);
// Assign "everything before the first question mark" to $url.
$url = $requri[0];
// If $url begins with qb's base directory, remove it. (It really should.)
if (substr($url, 0, strlen(QB_URLBASE)) == QB_URLBASE)
	$url = substr($url, strlen(QB_URLBASE));
// Remove duplicate slashes.
$url = preg_replace('|/+|', '/', $url);
// If the last character is a slash, remove it.
if (substr($url, -1) == '/')
	$url = substr($url, 0, -1);
// If QB_NODOT is set, remove all dots and colons from the string for security.
if (QB_NODOT)
	$url = str_replace(array('.', ':'), '', $url);

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

// Choose the template to use. Default is "html".
// TODO: Rewrite this to support all templates in $mime automatically.
$template = 'html';
// If there's a query string and it's "atom10", set the template accordingly.
if ((count($requri) > 1) && ($requri[1] == 'atom10'))
	$template = $requri[1];

// If there has been at least one file found:
if (count($matches) > 0) {
	// Sort by date created, newest first.
	krsort($matches);
	// $content will be the content the page template sees.
	$content = '';
	// $lastmtime will contain the latest mtime. Ya Rly.
	$lastmtime = 0;
	foreach ($matches as $ctime=>$paths) {
		// Iterate through the first dimension, $ctime is the creation time,
		// $paths an array of matches, which will be...
		foreach ($paths as $match) {
			// ...iterated as well. Add a single article to $content.
			$content .= qb_buildpage($match, $template);
			// Set $mtime to the file's mtime.
			$mtime = filemtime(QB_SRC.$match.QB_SUF_SRC);
			// Update $lastmtime, if necessary.
			if ($mtime > $lastmtime)
				$lastmtime = $mtime;
		}
	}
	// Throw out a Content-type and charset.
	header('Content-type: '.$mime[$template].'; charset=UTF-8');
	// Set template variables "content" and "modified".
	$meta['content'] = $content;
	if ($lastmtime != 0)
		$meta['modified'] = $lastmtime;
	// Throw out the final page and be done. U can has cheezburger now.
	echo(qb_template(QB_TPL_PAGE.'.'.$template, $meta));
}

function qb_buildpage($path, $template = 'html') {
	$filename = QB_SRC.$path.QB_SUF_SRC;
	if (($t = @file_get_contents($filename)) === false) {
		return (qb_template(QB_TPL_PAGE.'.'.$template, array(
			'title'   => 'file not found',
			'content' => '<p class=\'qb_error\'>The file ' . htmlspecialchars($path) . ' could not be found.</p>',
		)));
	} else {
		$page = explode("\n", $t, 2);
		preg_match_all('|<(.*):(.*)>(?!>)|U', $page[0], $tokens, PREG_SET_ORDER);
		$meta = array();
		foreach ($tokens as $token) {
			$k = $token[1]; $v = $token[2];
			if ($k == '')
				$k = 'title';
			if ($k == 'tags') {
				preg_match_all('%"(?! )([^"]+)(?<! )"|([^ ]+)%', $v, $tags, PREG_PATTERN_ORDER);
				foreach ($tags[0] as $tag) {
					$tag = str_replace('"', '', $tag);
					if (strlen(trim($tag)) > 0)
						$parsedtags[] = $tag;
				}
				$v = array_unique($parsedtags);
				if (count($v) > 0)
					$meta['spantags'] = '<span class=\'tag\'>' . implode('</span> <span class=\'tag\'>', $v) . '</span>';
			}
			$meta[$k] = $v;
		}
		$meta['path'] = preg_replace('|/+|', '/', QB_URLBASE . $path);
		$meta['content'] = $page[1];
		$meta['escapedcontent'] = htmlspecialchars($meta['content']);
		$meta['modified'] = filemtime($filename);
		$meta['created'] = filemtime(QB_META.$path.QB_SUF_CRE);
		if ($meta['created'] != $meta['modified'])
			$meta['wasmodified'] = $meta['modified'];
		return (qb_template(QB_TPL_ARTICLE.'.'.$template, $meta));
	}
}

function qb_created($path) {
	$cfile = QB_META.$path.QB_SUF_CRE;
	if (!file_exists($cfile)) {
		$ctime = time();
		$mtime = filemtime(QB_SRC.$path.QB_SUF_SRC);
		if ($mtime < $ctime)
			$ctime = $mtime;
		if (!file_exists(dirname($cfile)))
			mkdir(dirname($cfile), 0775, true);
		touch($cfile, $ctime);
		return ($ctime);
	} else {
		return (filemtime($cfile));
	}
}

function qb_runpattern($pattern, $string) {
	foreach ($pattern as $k => $v)
		if (count($v) == 1)
			$string = preg_replace($k, $v, $string);
	return ($string);
}

function qb_template($template, $data) {
	global $qb_regex;
	$regex = array();
	foreach ($data as $k => $v) {
		$nk = preg_replace('[^a-z0-9]', '', strtolower($k));
		$data2[$nk] = $v;
		$regex['|<qb:' . $nk . ' */ *>|U'] = $v;
	}
	foreach ($data2 as $k => $v)
		$regex['|<qb:ifset:' . $k . ' *>(.*)</qb:ifset:' . $k .' *>|Us'] = '$1';
	$regex['|<qb:ifset:[a-z0-9]+ *>.*</qb:ifset:[a-z0-9]+ *>|Us'] = '';
	$regex['|<qb:date>([0-9]+) *([^<>]+)</qb:date>|me'] = "date('\$2', \$1)";
	$regex = array_merge($regex, $qb_regex);
	$t = file_get_contents($template);
	$t = qb_runpattern($regex, $t);
	return ($t);
}

?>