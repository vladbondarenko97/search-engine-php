<?php
require('../config.php');
if(!isset($_SESSION['username'])) {
    echo redirectHTML('admin.php', 0);
    die();
}
$url = @$_GET['url'];
echo headerHTML('Remove URLs');
echo '
    <div style="text-align:right;"><a href="admin.php">Home</a></div>
    <h1>Crawler Admin - Remove URLs</h1>
    <p>Here, you can remove any URL from the database.<br />
    <b>NOTE:</b> If you input <i>http://site.com/page</i>, all the results that go after <i>page</i>, like <i>page?1</i>, will be also deleted. This cannot be undone.</p>
    <form method="GET">
    <table>
    <tr>
    <td><b>Site\'s URL:</b></td>
    <th><input type="text" placeholder="http://www.google.com/" name="url" value="' . @$url . '" style="width:200px;" /></th>
    </tr>
    <tr>
    <td></td>
    <th align="right"><input type="submit" value="Preview & Delete" /></th>
    </tr>
    </table>
    </form>
';

if(isset($url) && $url != NULL && !isset($_GET['confirm'])) {
    mysql_connect($db_host, $db_user, $db_password);
    @mysql_select_db($db_database) or die('DB ERROR');
    $show_results = mysql_query("SELECT * FROM search WHERE url LIKE '$url%'");
    $number = mysql_numrows($show_results);
    $i = 0;
    $n = 0;
    if($number != NULL) { 
        echo 'The following results will be deleted PERMANENTLY from the database.<hr>';
        while($i < $number) {
            $n++;
            $db_url = mysql_result($show_results, $i, 'url');
            $db_title = mysql_result($show_results, $i, 'title');
            $db_description = mysql_result($show_results, $i, 'description');
            echo $n . '. <b>URL:</b> ' . htmlentities($db_url) . '<br />';
            echo '<b>Title:</b> ' . htmlentities($db_title) . '<br />';
            echo '<b>Description:</b> ' . htmlentities($db_description) . '<hr>';
            $i++;
        }
        echo 'Are you sure you want to remove these results? <a href="admin_remove.php?confirm&url=' . $url . '">CONFIRM</a>';
    } else {
        echo errorHTML('URLs were not found');
        die();
    }
    mysql_close();
} elseif(isset($url) && $url != NULL && isset($_GET['confirm'])) {
    mysql_connect($db_host, $db_user, $db_password);
    @mysql_select_db($db_database) or die('DB ERROR');
    $delete_results = mysql_query("DELETE FROM search WHERE url LIKE '$url%'");
    echo messageHTML('URLs successfuly removed from the database. Redirecting...');
    echo redirectHTML('admin_remove.php', '3');
    mysql_close();
    die();
}
?>