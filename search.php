<?php

if (function_exists('xdebug_disable')) {
           xdebug_disable();
         }

error_reporting(E_ALL);
ini_set('display_errors', '1');

ob_start();
function return_me($return)
{
    return $return;
}
function spellcheck($word)
{
    $mysql = mysql_query("SELECT * FROM spell WHERE misspelled='" . $word . "'") or return_me('Error');
    $correct = @mysql_result($mysql, '0', 'correct');
    if (isset($correct) && $correct != NULL) {
        return $correct;
    } else {
        return NULL;
    }
}

if (isset($_SESSION['dangerous']))
    sleep('60');

/*
$_GET['ch'] - clear history
$_GET['dh'] - disable history
$_GET['eh'] - disable history
$_SESSION['history'] - array of the search history
*/

/* Time it takes the search query to load */
//K: better use microtime(true);
$mtime     = microtime();
$mtime     = explode(' ', $mtime);
$mtime     = $mtime[1] + $mtime[0];
$starttime = $mtime;

/* Load all information */
require('./config.php');

ob_start();


/* Check if user is requestiong the history deleted */
if (isset($_GET['ch']) && isset($_SESSION['history']) && $_SESSION['history'] != NULL) {
    unset($_SESSION['history']);
    echo '<b>History has been cleared.</b> <a href="?dh">Disable History</a>';
}

/* Check if the user is requesting to disable the history */
if (isset($_GET['dh'])) {
    $_SESSION['disable_history'] = true;
    echo '<b>History has been disabled.</b> <a href="?eh">Enable History</a>';
}
if (isset($_GET['eh'])) {
    unset($_SESSION['disable_history']);
    echo '<b>History has been disabled.</b> <a href="?dh">Disable History</a>';
}


if (!isset($_SESSION['mobile'])) {
    die('No session detected. Direct access is not allowed.');
}

$ip             = $_SERVER['REMOTE_ADDR'];
$custom_session = md5(date('m-d') . $ip);

if (isset($_GET['session']) && $_GET['session'] == $custom_session) {
    $_SESSION['bot'] = 'false';
} else {
    if (!isset($_SESSION['bot']) && $_SESSION['bot'] != 'false')
        die('Invalid session.');
}

$limit = 10;
if (!((is_numeric($limit)) && ($limit > 0) && ($limit <= 100))) {
    $limit = 10;
}

if ((!isset($_GET['p'])) || (!is_numeric($_GET['p'])) /*||
($_GET['p'] <= 0)*/ // exception handled later
    ) {
    $page = 1;
    
} else {
    $page = $_GET['p'];
}

if ((!is_numeric($limit)) || ($limit <= 0) || ($limit > 100)) {
    $limit = 10;
}
echo '<link rel="stylesheet" type="text/css" href='style.css" />';
if (isset($_GET['q']) && $_GET['q'] != NULL) {
    $query = strtolower(trim($_GET['q']));
    
    //kamil: im not sure if "while(strpos($s, '  ') !== false) str_replace($s, '  ', ' ');" isn't faster
    $query = preg_replace('/\\s+/', ' ', $query);
    
    $dc = array(
        '/',
        '"',
        '\'',
        '!',
        '@',
        '#',
        '$',
        '%',
        '^',
        '&',
        '*',
        '(',
        ')',
        '_',
        '='
    );
    
    $i;
    $query = str_replace($dc, '', $query, $i);
    if ($i > 0) {
        $message .= 'Some special characters such as "!", "@", "#", etc. were left out. (' . $i . ')';
    } else {
        $message = '';
    }
    $query = trim($query);
    $query = htmlentities($query);
    
    if ($query == NULL || strlen($query) <= 0 || strlen($query) >= 50) {
        if ($message != '') {
            echo errorHTML('Your query is empty.', 'center');
        }
        
        die();
    } else {
        mysql_connect($db_host, $db_user, $db_password);
        //kamil: try not to use "@"
        @mysql_select_db($db_database) or die('DB ERROR');
        
        $squery = '';
        
        /*$squerys = array();
        $squerys[] = str_replace(' ', '%', $query);
        
        $els = explode(' ', $query);
        foreach($els as $el){
        if(trim($el) == null) continue;
        $squerys[] = $el;
        }
        
        foreach($squerys as $q){
        $q = str_replace('%', '!%', $q);
        $q = str_replace('_', '!_', $q);
        $squery .= " description LIKE '%$q' OR";
        $squery .= " description LIKE '%$q%' OR";
        $squery .= " description LIKE '$q%' OR";
        }
        
        foreach($squerys as $q){
        $q = str_replace('%', '!%', $q);
        $q = str_replace('_', '!_', $q);
        $squery .= " title LIKE '%$q%' OR";
        $squery .= " title LIKE '%$q' OR";
        $squery .= " title LIKE '$q%' OR";
        }
        
        foreach($squerys as $q){
        $q = str_replace('%', '!%', $q);
        $q = str_replace('_', '!_', $q);
        $squery .= " url LIKE '%$q%' OR";
        $squery .= " url LIKE '%$q' OR";
        }
        
        $squery = substr($squery, 0, -3);*/
        
        $keywords  = explode(' ', $query);
        $dym       = '';
        $dym_clean = '';
        foreach ($keywords as $spell) {
            $correct = spellcheck($spell);
            if ($correct != NULL) {
                $dym .= ' <b>' . $correct . '</b> ';
                $dym_clean .= ' ' . $correct . ' ';
            } else {
                $dym .= $spell . ' ';
                $dym_clean .= $spell . ' ';
            }
        }
        $dym       = trim($dym);
        $dym_clean = trim($dym_clean);
        
        $dym       = preg_replace('!\s+!', ' ', $dym);
        $dym_clean = preg_replace('!\s+!', ' ', $dym_clean);
        $dym_clean = str_replace(' ', '+', $dym_clean);
        $fields    = array(
            'title',
            'url',
            'description'
        );
        
        $keywords = array_unique($keywords);
        $squery .= '(';
        foreach ($fields as $field) {
            $squery .= '(';
            foreach ($keywords as $keyword) {
                $squery .= '(';
                
                $keyword = str_replace('%', '!%', $keyword);
                $keyword = str_replace('_', '!_', $keyword);
                
                /*for($i = 0; $i < strlen($keyword); ++$i){
                $key = $keyword;
                $key{$i} = '_';
                $squery .= " $field LIKE '%$key' OR";
                }*/
                
                $squery .= " $field LIKE '%$keyword' OR";
                $squery .= " $field LIKE '%$keyword%' OR";
                $squery .= " $field LIKE '$keyword%'";
                $squery .= ') AND';
            }
            $squery = substr($squery, 0, -4); // deleting last " AND"
            $squery .= ') OR';
        }
        $squery = substr($squery, 0, -3); // deleting last " OR"
        $squery .= ')';
        
        /*$squery .= '(';
        
        foreach($els as $q){
        $squery .= '(';
        $q = str_replace('%', '!%', $q);
        $q = str_replace('_', '!_', $q);
        $squery .= " description LIKE '%$q' OR";
        $squery .= " description LIKE '%$q%' OR";
        $squery .= " description LIKE '$q%'";
        $squery .= ') AND';
        }
        $squery = substr($squery, 0, -4);
        
        $squery .= ') OR (';
        
        foreach($els as $q){
        $squery .= '(';
        $q = str_replace('%', '!%', $q);
        $q = str_replace('_', '!_', $q);
        $squery .= " title LIKE '%$q' OR";
        $squery .= " title LIKE '%$q%' OR";
        $squery .= " title LIKE '$q%'";
        $squery .= ') AND';
        }
        $squery = substr($squery, 0, -4);
        
        $squery .= ') OR (';
        
        foreach($els as $q){
        $squery .= '(';
        $q = str_replace('%', '!%', $q);
        $q = str_replace('_', '!_', $q);
        $squery .= " url LIKE '%$q' OR";
        $squery .= " url LIKE '%$q%' OR";
        $squery .= " url LIKE '$q%'";
        $squery .= ') AND';
        }
        $squery = substr($squery, 0, -4);
        
        $squery .= ')';*/
        
        $result = mysql_query("SELECT * FROM search WHERE $squery");
        $number = @mysql_numrows($result);
        
        echo '<!--' . $squery . '!-->';
        
        /*if($page > ceil($number / $limit)){
        $page = ceil($number / $limit);
        }*/
        include('./popular.php');
        if ($message != '') {
            echo '<b>Info:</b> ' . $message . '<br/>';
        }
        
        $show_results = true;
        echo '<span id="qtime"></span>';
        
        if ($number == 0) {
            if ($message != '') {
                echo '<b>Info:</b> ' . $message . '<br/>';
            }
            if ($dym != $query) {
                echo 'Did you mean: <a href="?q=' . $dym_clean . '" onclick="javascript:parent.asearch(\'' . $dym_clean . '\');">' . $dym . '</a>';
            }
            echo '
				<div>
					<b>No search results were found for <em><u>'.strip_tags($query).'</u></em>.</b><br />
					<p>Please remove any extra keywords from the query such as "http://", "www.", "and", "how", etc. Make sure only important keywords stay inside the query.</p>
					<hr/>
				</div>
			';
            
            $show_results = false;
        } elseif ($page > ceil($number / $limit) || $page < 1) {
            if ($message != '') {
                echo '<b>Info:</b> ' . $message . '<br/>';
            }
            
            echo '
				<div>
					<b>Invalid page number given</b><br/>
					<p>Please go to <a href="?q=' . @$_GET['q'] . '&l=' . $limit . '&p=1">first page</a> of results</p>
					<hr/>
				</div>
			';
            
            $show_results = false;
        }
        
        mysql_close();
        
        $ret = array();
        
        if ($show_results) {
            for ($i = ($page - 1) * $limit; $i < $number && $i < $limit + (($page - 1) * $limit); ++$i) {
                $db_id          = mysql_result($result, $i, 'id');
                $db_url         = mysql_result($result, $i, 'url');
                $db_title       = mysql_result($result, $i, 'title');
                $db_description = mysql_result($result, $i, 'description');
                
                $db_url         = str_replace('&amp;', '&', $db_url);
                $biggest = max($keywords);
                $db_description = str_replace('&nbsp;', ' ', $db_description);
                $db_description = str_replace('&amp;', '&', $db_description);
                $db_description = str_replace('&amp;', '&', $db_description);
                $non_rel = $db_description;
                $db_description = rel_desc($db_description, $biggest).' <b>...</b> '.$non_rel;
                $db_description = substr($db_description, 0, 255);
                $db_title       = str_replace('&amp;', '&', $db_title);
                
                $ret[] = array(
                    'url' => $db_url,
                    'title' => $db_title,
                    'description' => $db_description,
                    'id' => $db_id
                );
            }
            
            $ret = sort_result(explode(' ', $query), $ret);
            dump_result(explode(' ', $query), $ret, $page, $limit, $query);
            dump_pages($page, $limit, $number);
            $mtime     = microtime();
            $mtime     = explode(" ", $mtime);
            $mtime     = $mtime[1] + $mtime[0];
            $endtime   = $mtime;
            $totaltime = ($endtime - $starttime);
            $output    = ob_get_contents();
            ob_end_clean();
            if (!isset($_SESSION['history'])) {
                $_SESSION['history'] = array();
            }
            array_push($_SESSION['history'], $query);
            $_SESSION['history'] = array_unique(array_reverse($_SESSION['history']));
            $otime               = substr($totaltime, 0, 4);
            if ($otime == '0.00') {
                $otime == '0.01';
            }
            searchResultsHTML($query, $number, $otime);
            
            if($dym != $query) echo $dym;
            echo $output;
            
        }
    }
}
function rel_desc($str, $search)
{
    $pos     = 0;
    $offset  = 0;
    $length  = strlen($str);
    $limit   = 64;
    $results = array();
    while (($pos = stripos($str, $search, $offset)) !== false) {
        $result   = substr($str, $pos);
        $keywords = explode(' ', $result);
        $result   = '';
        foreach ($keywords as $keyword) {
            if (strlen($result . $keyword) > $limit) {
                break;
            }
            $result .= $keyword . ' ';
        }
        $results[] = $result;
        $offset    = $pos + strlen($result);
        if ($offset > $length) {
            break;
        }
    }
    return @$results[0];
}


?>