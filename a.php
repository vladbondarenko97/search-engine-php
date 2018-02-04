<?php
// Encrypt = mcrypt_ecb(MCRYPT_DES, 'key', $id, MCRYPT_ENCRYPT);
function get_link($hash) {
	require('./config.php');
	$key = '13371337';
	$id = mcrypt_ecb(MCRYPT_DES, $key, $hash, MCRYPT_DECRYPT);
	mysql_connect($db_host, $db_user, $db_password);
	@mysql_select_db($db_database) or die('DB ERROR');
	$mysql = mysql_query("SELECT * FROM search WHERE id='".mysql_real_escape_string($id)."' LIMIT 1");
	$url = mysql_result($mysql, 0, 'url');
	header('Location: '.$url);
	$mysql = mysql_query("UPDATE search
SET    clicks = clicks + 1
WHERE  url = '".$url."' ");
	mysql_close();
}

if(isset($_GET['a'])) {
	get_link(base64_decode($_GET['a']));
	die();
}
?>
