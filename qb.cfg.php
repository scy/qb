<?php

define('QB_URLBASE', '/qb/');

$qb_regex = array(
	'|<thought>(.+)</thought>|Uims' => '<em>$1</em>',
	'|<rem>(.+)</rem>|Uims' => '<em>$1</em>',
	'|<(/?)path>|Ui' => '<$1samp>',
	'|<shell>(.+)</shell>|Uims' => '<pre class=\'shell\'>$1</pre>',
	'|<w([a-z]{2}):([^ ]+)>|Ui' => '<w$1:$2 $2>',
	'|<w([a-z]{2}):([^ ]+) (.+)>|Ui' => '<<http://$1.wikipedia.org/wiki/$2 $3>>',
	'|<<([^ >]+) ([^>]+)>>|Uims' => '<a href=\'$1\'>$2</a>',
);

define('QB_TPL_PAGE', 'src/qb.template');
define('QB_TPL_ARTICLE', 'src/qb.article');
define('QB_NODOT', true);
define('QB_SRC', 'src/');
define('QB_META', 'meta/');
define('QB_SUF_CRE', '.cre');
define('QB_SUF_SRC', '.qb');

?>