<?php

// The base part for URLs. Should start with a slash, end with a slash and
// contain the directory in your Document Root where qb is installed.
define('QB_URLBASE', '/blog/');

// Regexes that are run over each article. See PHP's preg_match() documentation.
$qb_regex = array(
	'|<thought>(.+)</thought>|Uims' => '<em>$1</em>',
	'|<rem>(.+)</rem>|Uims' => '<em>$1</em>',
	'|<(/?)path>|Ui' => '<$1samp>',
	'|<shell>(.+)</shell>|Uims' => '<pre class=\'shell\'>$1</pre>',
	'|<w([a-z]{2}):([^ ]+)>|Ui' => '<w$1:$2 $2>',
	'|<w([a-z]{2}):([^ ]+) (.+)>|Ui' => '<<http://$1.wikipedia.org/wiki/$2 $3>>',
	'|<<([^ >]+) ([^>]+)>>|Uims' => '<a href=\'$1\'>$2</a>',
);

// File name prefix for full-page templates.
define('QB_TPL_PAGE', 'src/qb.template');
// File name prefix for single-article templates.
define('QB_TPL_ARTICLE', 'src/qb.article');
// Set to true to remove every dot and colon from the supplied request as a
// security measure.
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
