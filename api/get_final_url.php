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

require('./config.php');
echo get_final_url(@$_GET['url']);
?>