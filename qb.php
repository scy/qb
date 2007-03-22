<?php

require_once('qb.cfg.php');

$mime = array(
	'html'   => 'text/html',
	'atom10' => 'application/atom+xml',
);

$url = $_SERVER['REQUEST_URI'];
if (substr($url, 0, strlen(QB_URLBASE)) == QB_URLBASE)
	$url = substr($url, strlen(QB_URLBASE));
$url = preg_replace('|/+|', '/', $url);
if (substr($url, -1) == '/')
	$url = substr($url, 0, -1);
if (QB_NODOT)
	$url = str_replace(array('.', ':'), '', $url);

$realpath = QB_SRC.$url;
$matches = array();
if (is_file($realpath.QB_SUF_SRC)) {
	$matches[qb_created($url)][] = $url;
} elseif (is_dir($realpath)) {
	$scanq = array($realpath);
	while (count($scanq) > 0) {
		$curdir = array_shift($scanq);
		$scan = scandir($curdir);
		foreach ($scan as $hit) {
			$foo = "$curdir/$hit";
			if ((is_file($foo)) && (substr($foo, 0 - strlen(QB_SUF_SRC)) == QB_SUF_SRC)) {
				$path = substr($foo, strlen(QB_SRC), 0 - strlen(QB_SUF_SRC));
				$matches[qb_created($path)][] = $path;
			} elseif ((is_dir($foo)) && ($hit != '.') && ($hit != '..')) {
				$scanq[] = $foo;
			}
		}
	}
}

$template = 'html';
if ($_SERVER['QUERY_STRING'] == 'atom10')
	$template = $_SERVER['QUERY_STRING'];

if (count($matches) > 0) {
	krsort($matches);
	$content = '';
	$lastmtime = 0;
	foreach ($matches as $ctime=>$paths)
		foreach ($paths as $match) {
			$content .= qb_buildpage($match, $template);
			$mtime = filemtime(QB_SRC.$path.QB_SUF_SRC);
			if ($mtime > $lastmtime)
				$lastmtime = $mtime;
		}
	header('Content-type: '.$mime[$template].'; charset=UTF-8');
	$meta['content'] = $content;
	if ($lastmtime != 0)
		$meta['modified'] = $lastmtime;
	echo(qb_template(QB_TPL_PAGE.'.'.$template, $meta));
}

/*if ($_SERVER['REDIRECT_STATUS'] == 404)
	header($_SERVER['SERVER_PROTOCOL'] . ' 200 Generated For You');*/

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

///// STUFF AFTER HERE IS (HOPEFULLY) NOT USED ANYMORE AND WILL BE REMOVED SOON /////

define('QB_TITLE_PRE', '<h3>');
define('QB_TITLE_SUF', '</h3>');
define('QB_META', 'meta/');
define('QB_SUF_REP', '.regexed');
define('QB_SUF_SPT', '.spot');

function qb_die($msg) {
	die("<strong>qb died:</strong> $msg\n");
}

function qb_404($path) {
	die('quoth the server, 404.');
}

// $path needs to be sane
function qb_article($path) {
	$cachefile = QB_META.$path.QB_SUF_REP;
	$sourcefile = QB_SRC.$path.QB_SUF_SRC;
	$spotfile = QB_META.$path.QB_SUF_SPT;
	$regen = false;
	$m = false;
	if (file_exists($cachefile)) {
		// Cache file exists, compare modification dates. If source file doesn't
		// exist, regeneration is triggered and the error is handled there.
		$m = @filemtime($sourcefile);
		if ((@filemtime($cachefile) < $m) || ($m === false))
			$regen = true;
	} else
		$regen = true;
	if ($regen) {
		$t = @file($sourcefile);
		if ($t === false) {
			@unlink($cachefile);
			@unlink($spotfile);
			qb_404($path);
			return (false);
		} else {
			$time = time();
			if (($m !== false) && ($m < $time))
				$time = $m;
			if (!file_exists($spotfile))
				if (!@file_put_contents($spotfile, $time))
					qb_die('could not spot '.htmlspecialchars($path));
			$title = QB_TITLE_PRE . htmlspecialchars(trim($t[0])) . QB_TITLE_SUF;
			unset($t[0]);
			$t = "$title\n" . implode('', $t);
			$t = qb_regex($t);
			if (!file_put_contents($cachefile, $t))
				qb_die('could not cache '.htmlspecialchars($path));
		}
	}
	return (file_get_contents($cachefile));
}

function qb_page($text) {
	$title = preg_replace('|<.+>|U', '', substr($text, 0, strpos($text, "\n")));
	qb_page_header('<title>'.htmlspecialchars($title).QB_HEADTITLE_SUF.'</title>'."\n");
	echo($text);
	qb_page_footer();
}

function qb_page_header($text = '') {
	echo("<?xml version='1.0' encoding='UTF-8' ?".">\n");
	echo("<html>\n<head>\n");
	echo($text);
	echo("</head>\n<body>\n");
}

function qb_page_footer($text = '') {
	echo($text);
	echo("</body>\n</html>\n");
}

function qb_regex($text) {
	global $qb_regex;
	foreach ($qb_regex as $pattern => $replacement) {
		$text = preg_replace($pattern, $replacement, $text);
	}
	return ($text);
}

function qb_serve() {
	$url = $_SERVER['SCRIPT_URL'];
	if (substr($url, 0, strlen(QB_PATH)) == QB_PATH)
		$url = substr($url, strlen(QB_PATH));
	if (QB_NODOT)
		$url = str_replace('.', '', $url);
	if (file_exists(QB_SRC.$url.QB_SUF_SRC))
		qb_page(qb_article($url));
	else
		qb_404($url);

}


?>