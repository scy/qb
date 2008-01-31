<?php

// QB_URLBASE is now obsolete and will be auto-detected.
// If you still need to set the base path manually, do it like this:
// qbURL::setHandler('/blog');

// Regexes that are run over each article. See PHP's preg_match() documentation.
$qb_regex = array(
	'|<thought>(.+)</thought>|Uims' => '<em>$1</em>',
	'|<rem>(.+)</rem>|Uims' => '<em>$1</em>',
	'|<(/?)path>|Ui' => '<$1samp>',
	'|<shell>(.+)</shell>|Uims' => '<pre class=\'shell\'>$1</pre>',
	'|<log>(.+)</log>|Uims' => '<pre class=\'log\'>$1</pre>',
	'|<w([a-z]{2}):([^ ]+)>|Ui' => '<w$1:$2 $2>',
	'|<w([a-z]{2}):([^ ]+) (.+)>|Ui' => '<<http://$1.wikipedia.org/wiki/$2 $3>>',
	'|<rfc:([0-9]+)>|Ui' => '<rfc:$1 RFC&nbsp;$1>',
	'|<rfc:([0-9]+) (.+)>|Ui' => '<<http://www.ietf.org/rfc/rfc$1.txt $2>>',
	'|<<([^ >]+) ([^>]+)>>|Uims' => '<a href=\'$1\'>$2</a>',
);

// File name prefix for full-page templates.
define('QB_TPL_PAGE', 'tpl/fullpage');
// File name prefix for single-article templates.
define('QB_TPL_ARTICLE', 'tpl/article');
// Set to true to remove every dot and colon from the supplied request as a
// security measure. DO NOT CHANGE THIS UNLESS YOU KNOW WHAT YOU ARE DOING.
define('QB_NODOT', true);
// Where the article source files live.
define('QB_SRC', 'src/');
// Where meta files generated by qb live.
define('QB_META', 'meta/');
// Suffix for creation timestamp files.
define('QB_SUF_CRE', '.cre');
// Suffix for article source files.
define('QB_SUF_SRC', '.qb');
// Maximum number of articles on one page. If you don't want to use the "pages"
// feature, comment this out.
define('QB_MAXITEMS', 17);
// Since you can't use pages together with non-default templates, should non-
// default templates display only the first page (true) or everything (false)?
define('QB_SHORTEN_FEEDS', true);
// What to send as 404 message. You can't use qb markup here, only normal HTML.
define('QB_FOUROHFOUR', '<p class=\'error\'>No such article.</p>');

?>
