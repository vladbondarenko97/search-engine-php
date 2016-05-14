<?php
if(isset($_GET['key']) && isset($_GET['url'])) {
	$key = $_GET['key'];
	$url = $_GET['url'];
	if($key != 'key') {
		die('Authorization failed.');
	}
} else {
	die('Missing arguments. (KEY || URL)');
}

require('../config.php');
require('../includes/htmlpurifier/library/HTMLPurifier.auto.php');

$replace_with_space = array('<br>', '<br/>', '<br />', '<div>', '<p>');
$replace_with_null = array('</div>', '</p>', '  ');

$description = getPage($url);
$config = HTMLPurifier_Config::createDefault();
$config->set('Core.Encoding', 'UTF-8');
$config->set('HTML.Allowed', 'div,br,p');
$purifier = new HTMLPurifier($config);
$clean_html = $purifier->purify($description);
$clean_html = str_replace($replace_with_null, NULL, $clean_html);
$clean_html = str_replace($replace_with_space, ' ', $clean_html);
$clean_html = substr($clean_html, 0, 1024);

echo $clean_html;
?>