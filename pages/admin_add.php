<?php
require('../config.php');
require('../includes/htmlpurifier/library/HTMLPurifier.auto.php');

$replace_with_space = array('<br>', '<br/>', '<br />', '<div>', '<p>');
$replace_with_null = array('</div>', '</p>', '  ');

if(!isset($_SESSION['username'])) {
    echo redirectHTML('admin.php', 0);
    die();
}
if(isset($_GET['url'])) {
	$url = $_GET['url'];
	$url = get_final_url($url);
} else {
	$url = '';
}
echo headerHTML('Add URL');
echo '
    <div style="text-align:right;"><a href="admin.php">Home</a></div>
    <h1>Crawler Admin - Add URL</h1>
    <p>Here, you can add any URL to the database. All the redirects will be removed,</p>
    <form method="GET">
    <table>
    <tr>
    <td><b>Site\'s URL:</b></td>
    <th><input type="text" placeholder="http://www.google.com/" name="url" value="' . $url . '" style="width:200px;" /></th>
    </tr>
    <tr>
    <td></td>
    <th align="right"><input type="submit" value="Preview & Add" /></th>
    </tr>
    </table>
    </form>
';

if(isset($url) && $url != NULL && !isset($_GET['confirm'])) {
    mysql_connect($db_host, $db_user, $db_password);
    echo 'yea';
    @mysql_select_db($db_database) or die('DB ERROR');
    $show_results = mysql_query("SELECT * FROM search WHERE url LIKE '$url'");
    $number = mysql_numrows($show_results);
    $i = 0;
    $n = 0;
    if($number != NULL) { 
        echo 'This URL is already in the database.<hr>';
        while($i < $number) {
            $n++;
            $db_url = mysql_result($show_results, $i, 'url');
            $db_title = mysql_result($show_results, $i, 'title');
            $db_description = mysql_result($show_results, $i, 'description');
            echo '<b>URL:</b> ' . htmlentities($db_url) . '<br />';
            echo '<b>Title:</b> ' . htmlentities($db_title) . '<br />';
            echo '<b>Description:</b> ' . htmlentities($db_description) . '<hr>';
            $i++;
        }
       mysql_close();
    } else {
        if(getPage($url) == NULL) {
            echo errorHTML('This page is either empty or unreachable.');
            echo 'wtf';
	} else {
            $description = getPage($url);
            $config = array(
           	'indent'         => true,
           	'output-xhtml'   => true,
           	'wrap'           => 200);
	$tidy = new tidy;
	$tidy->parseString($description, $config, 'utf8');
	$tidy->cleanRepair();
	$description = $tidy;
            $meta = get_meta_tags($url);
            if(isset($meta['description']) && $meta['description'] != NULL && strlen($meta['description'])  <= 512 && strlen($meta['description']) >= 10) {
            	$top = htmlentities($meta['description']).' '; 	
            } else {
            	$top = '';
            }
            $config = HTMLPurifier_Config::createDefault();
            $config->set('Core.Encoding', 'UTF-8');
            $config->set('HTML.Allowed', 'div,br,p');
            $purifier = new HTMLPurifier($config);
            $clean_html = $purifier->purify($description);
            $clean_html = str_replace($replace_with_null, NULL, $clean_html);
            $clean_html = str_replace($replace_with_space, ' ', $clean_html);
            $clean_html = substr($clean_html, 0, 1024);

            echo '
                This URL will be added to the database. URL\'s info:<br />
                <b>URL:</b> ' . htmlentities($url) . '<br />
                <b>Title:</b> ' . htmlentities(getTitle($url)) . '<br />
                <b>Description:</b> ' . $top . htmlentities($clean_html) . '<br />
                <b>Are you sure you want to add this URL into the database?</b> <a href="?confirm&url=' . $url . '">CONFIRM</a>
            ';
        }
    }
} elseif(isset($url) && $url != NULL && isset($_GET['confirm'])) {
    $title = getTitle($url);
    $description = getPage($url);
    $meta = get_meta_tags($url);
    if(isset($meta['description']) && $meta['description'] != NULL && strlen($meta['description'])  <= 512 && strlen($meta['description']) >= 10) {
    	$top = htmlentities($meta['description']).' '; 	
    } else {
        $top = '';
    }
    $config = HTMLPurifier_Config::createDefault();
    $config->set('Core.Encoding', 'utf-8');
    $config->set('HTML.Allowed', 'div,br,p');
    $purifier = new HTMLPurifier($config);
    $clean_html = $purifier->purify($description);
    $clean_html = str_replace($replace_with_null, NULL, $clean_html);
    $clean_html = str_replace($replace_with_space, ' ', $clean_html);
    $clean_html = substr($clean_html, 0, 1024);

    mysql_connect($db_host, $db_user, $db_password);
    mysql_select_db($db_database) or die('DB ERROR');
    $add_results = mysql_query("INSERT INTO search VALUES ('','".mysql_real_escape_string($url)."', '".mysql_real_escape_string($title)."', '".mysql_real_escape_string($top).mysql_real_escape_string($clean_html)."')");
    echo messageHTML('URL was successfully added. Redirecting...');
    echo redirectHTML('admin_add.php', '3');
    mysql_close();
    die();
}
?>
