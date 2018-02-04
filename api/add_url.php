<?php
if(isset($_POST['key']) && isset($_POST['url']) && isset($_POST['title']) && isset($_POST['desc'])) {
	$key = $_POST['key'];
	$url = urldecode($_POST['url']);
	$title = urldecode($_POST['title']);
	$desc = urldecode($_POST['desc']);
	if($key != '13371337') {
		die('Authorization failed.');
	}
} else {
	die('Missing arguments. [POST] (KEY || URL || TITLE || DESC[RIPTION])');
}

require('../config.php');
require('../includes/htmlpurifier/library/HTMLPurifier.auto.php');

$replace_with_space = array('<br>', '<br/>', '<br />', '<div>', '<p>');
$replace_with_null = array('</div>', '</p>', '  ');

$f4 = substr($url, 0, 4);
if($f4 != 'http') {
    die('0');
}

$url = get_final_url(trim($url));
$title = substr(trim($title), 0, 128);
$desc = substr(trim(htmlentities($desc)), 0, 2048);

$config = HTMLPurifier_Config::createDefault();
$config->set('Core.Encoding', 'UTF-8');
$config->set('HTML.Allowed', 'div,br,p');
$purifier = new HTMLPurifier($config);
$desc = $purifier->purify($desc);
$desc = str_replace($replace_with_null, NULL, $desc);
$desc = str_replace($replace_with_space, ' ', $desc);

mysql_connect($db_host, $db_user, $db_password);
mysql_select_db($db_database) or die('DB ERROR');
$show_results = mysql_query("SELECT * FROM search WHERE url LIKE '".mysql_real_escape_string($url)."'");
$number = mysql_numrows($show_results);
$i = 0;
if($number == 0) {
	$add_results = mysql_query("INSERT INTO search VALUES ('','".mysql_real_escape_string($url)."', '".htmlentities(mysql_real_escape_string($title))."', '".htmlentities(mysql_real_escape_string($desc))."')");
	echo '1';
} else {
	echo '0';
}
mysql_close();
?>
