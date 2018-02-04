<?php
function hs($string) {
	$h = '';
	for($i = 0; $i < strlen($string); $i++) {
		if($i % rand(1, 5) == 0) {
        		$h .= '<!--!@#$%^&*()'.rand().'asbeb-->';
    		}
		$h .= $string[$i];
	}
	return $h;
}


function getPage($url){
    $ch = curl_init();
    $timeout = 5;
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
    curl_setopt($ch, CURLOPT_USERAGENT, 'Crawler Search 0.5 alpha');
    $data = curl_exec($ch);
    curl_close($ch);
    return $data;
}

function getTitle($url){
    $str = getPage($url);
    if(strlen($str) > 0){
        preg_match("/\<title\>(.*)\<\/title\>/", $str, $title);
        return htmlentities($title[1]);
    }
}

function contains($needle, $haystack) {
	if(preg_match('/'.$needle.'/', $haystack)) {
		return true;
	} else {
		return false;
	}
}

function r2a($rel, $base){
	// http://toolspot.org/relative-path-into-absolute-url.php

	if(strpos($rel, '//') === 0) {
		return 'http:'.$rel;
	}

	if(parse_url($rel, PHP_URL_SCHEME) != ''){
		return $rel;
	}

	if(@$rel[0] == '#' || @$rel[0] == '?'){
		return $base.$rel;
	}

	extract(parse_url($base));
	$path = preg_replace('#/[^/]*$#',  '', $path);

	if(@$rel[0] ==  '/') $path = '';

	$abs = "$host$path/$rel";
	$re = array('#(/\.?/)#', '#/(?!\.\.)[^/]+/\.\./#');
	for($n=1; $n>0; $abs=preg_replace($re, '/', $abs, -1, $n)) {}
	return  $scheme.'://'.$abs;
}

function extractLinks($url, $type = '0'){
    $html = getPage($url);
    @$DOM = new DOMDocument();
    @$DOM->loadHTML($html);
    $a = @$DOM->getElementsByTagName('a');

    foreach($a as $link){
    	$first4 = substr($link->getAttribute('href'), 0, 4);

    	if($first4 != 'http' && $first4 != 'java'){
    		$output = r2a($link->getAttribute('href'), $url);
    	}else{
    		$output = $link->getAttribute('href');
    	}

    	if($type == 0){
    		if(!contains('javascript:', $output) && $output != NULL){
        		echo $output.'<br />';
        	}
        }elseif($type == 1){
                if(!contains('javascript:', $output) && $output != NULL){
        		echo '<a href="/pages/admin_add.php?url='.urlencode($output).'">'.htmlentities($output).'</a><br />';
        	}
        } else {
        	if(!contains('javascript:', $output) && $output != NULL) {
        		echo $output.'::';
        	}
        }
    }
}

function redirectHTML($url, $seconds = 0) {
    echo '
        <meta http-equiv="refresh" content="' . $seconds . ';url=' . $url . '">
    ';
}

function searchBoxHTML($query = '', $ex = 0) {
    echo '
        <div align="center">
        <h1><a href="index.php">Crawler Search</a></h2>
        <form method="GET" action="?l=10">
        <input type="text" placeholder="Enter keywords here..." value="' . $query . '" style="width:300px;" name="q" />
        <input type="submit" value="Search" />
    ';
    if($ex != 0) {
        echo '
            <br /><b>Results per page:</b><br />
            <input type="radio" name="l" value="10" checked />10 results
            <input type="radio" name="l" value="25" />25 results
            <input type="radio" name="l" value="50" />50 results
        ';
    }
    echo '
        </div>
        </form>
    ';
}

function errorHTML($msg = 'An error has occurred.', $align = 'center') {
    echo '
        <div align="' . $align . '" style="color:red;"><b>Error:</b> ' . $msg . '</div>
    ';
}

function messageHTML($msg, $title = 'Message:', $align = 'center', $color = 'lime') {
    echo '
        <div align="' . $align . '" style="color:' . $color . ';"><b>' . $title . '</b> ' . $msg . '</div>
    ';
}

function headerHTML($title) {
    echo '
        <!DOCTYPE html>
        <html>
        <head>
        <title>' . $title . '</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no" />
        <link rel="stylesheet" type="text/css" href="./style.css"/>
        </head>
        <body style="margin:5px;">
    ';
}

function newsHTML() {
    echo '
        <br /><div align="center" style="background-color:white; position: fixed; bottom: 0px; left: 0; right: 0;">Developed by <a href="http://vlad.tk/">Vlad Bondarenko</a></div><br />
    ';
}

function currentURL() {
    $pageURL = 'http';
    if($_SERVER["HTTPS"] == "on") {
        $pageURL .= "s";
    }
    $pageURL .= "://";
    if($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"].":".$_SERVER["SERVER_PORT"].$_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"].$_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

function searchResultsHTML($query, $number = 'Unknown number of', $time = 'Unknown') {
    echo '
        <br /><div style="color:grey">Showing results for <b>' . $query . '</b>. ' . $number .' results in '.$time.' seconds:</div><hr/>
    ';
}

function loginHTML() {
    echo headerHTML('Crawler Admin');
    echo '
        <h1>Crawler Admin - Login</h1>
        <form method="POST">
        <table>
        <tr>
        <td><b>Username:</b></td>
        <th><input type="text" placeholder="Username" name="username" value="" style="width:200px;" /></th>
        </tr>
        <tr>
        <td><b>Password:</b></td>
        <th><input type="password" placeholder="Password" name="password" value="" style="width:200px" /></th>
        </tr>
        <tr>
        <td></td>
        <th align="right"><input type="submit" value="Login" /></th>
        </tr>
        </table>
        </form>
        </body>
        </html>
    ';
}

function get_redirect_url($url) {
    $redirect_url = null;

    $url_parts = @parse_url($url);
    if(!$url_parts) return false;
    if(!isset($url_parts['host'])) return false;
    if(!isset($url_parts['path'])) $url_parts['path'] = '/';

    $sock = fsockopen($url_parts['host'], (isset($url_parts['port']) ? (int)$url_parts['port'] : 80), $errno, $errstr, 30);
    if(!$sock) return false;

    $request = "HEAD " . $url_parts['path'] . (isset($url_parts['query']) ? '?'.$url_parts['query'] : '') . " HTTP/1.1\r\n";
    $request .= 'Host: ' . $url_parts['host'] . "\r\n";
    $request .= "Connection: Close\r\n\r\n";
    fwrite($sock, $request);
    $response = '';
    while(!feof($sock)) $response .= fread($sock, 8192);
    fclose($sock);

    if(preg_match('/^Location: (.+?)$/m', $response, $matches)) {
        if(substr($matches[1], 0, 1) == "/") {
            return $url_parts['scheme'] . "://" . $url_parts['host'] . trim($matches[1]);
        } else {
            return trim($matches[1]);
        }

    } else {
        return false;
    }
}

function get_all_redirects($url){
    $redirects = array();
    while($newurl = get_redirect_url($url)) {
        if(in_array($newurl, $redirects)) {
            break;
        }
        $redirects[] = $newurl;
        $url = $newurl;
    }
    return $redirects;
}

function get_final_url($url) {
    $redirects = get_all_redirects($url);
    if(count($redirects)>0) {
        return array_pop($redirects);
    } else {
        return $url;
    }
}

function hide_string($string) {
    $s='';
    foreach(str_split($string)as$l)
    {
        switch(rand(1,3))
        {
            case 1:$s.='&#'.ord($l).';';break;
            case 2:$s.='&#x'.dechex(ord($l)).';';break;
            case 3:$s.=$l;
        }
    }
    return $s;
}

function dump_result($psearchs, $res, $page, $limit, $query = 0){
	$n = ($page - 1) * $limit;
	foreach($res as $e) {
		++$n;
		$query='';
		$desc=$e['description'];
		$id = $e['id'];
		$encrypt = base64_encode(mcrypt_ecb(MCRYPT_DES, '13371337', $id, MCRYPT_ENCRYPT));
		$url = 'http://localhost/search/a.php?a='.urlencode($encrypt);
		echo '
			<div>
				<b>'.$n.'.</b> <a href="'.$url.'" target="_blank" onclick="return false; window.location.href='.$url.'">'.highlight_res($psearchs, $e['title']).'</a><br/>
				'.highlight_res($psearchs, strip_tags($query.$desc)).' <b>...</b><br/>
				<i><span style="color:green;">'.highlight_res($psearchs, $e['url'], true).'</span></i>
			</div>
			<hr/>
		';
	}
}

function dump_pages($page, $limit, $number){
	$pages = ceil($number / $limit);

	// its pointless to echo pages numbers when theres only one page
	if($pages == 1) {
		echo '<br/>Try to reduce the number of keywords to increase the number of results.';
		return 0;
	}

	// checking if echo < with link or without
	if($page > 1){
		echo '<div><b><a href="?q='.htmlentities($_GET['q']).'&l='.$limit.'&p='.($page - 1).'">previous</a></b> ';
	}else{
		echo '<div>'; //.'<b>previous</b> ';
	}

	$num_arm = 5;

	$ret = '';
	$fn = false; // first shown number
	$ln; // last shown number
	for($i = $page - $num_arm; $i < $page + $num_arm; ++$i){
		if($i < 1) continue;
		if($i > $pages) continue;

		if($fn === false) $fn = $i;
		$ln = $i;

		if($i == $page){
			$ret .= ' <b>'.$i.'</b> ';
		}else{
			$ret .= ' <a href="?q='.htmlentities($_GET['q']).'&l='.$limit.'&p='.($i).'">'.$i.'</a> ';
		}
	}

	// if theres less pages than shown, echos "..."
	if($fn > 1){
		echo '...';
	}

	echo $ret;

	// if theres more pages than shown, echos "..."
	if($ln < $pages){
		echo '...';
	}

	// checking if echo > with link or without
	if($page < $pages){
		echo ' <b><a href="?q='.htmlentities($_GET['q']).'&l='.$limit.'&p='.($page + 1).'">next</a></b></div>';
	}else{
		echo /*' <b>next</b>'.*/'</div>';
	}
}

function similarity($psearchs, $r){
	$s = 0;

	foreach($psearchs as $p){
		if($p == null) continue; //for spaces
		$s += substr_count($r['url'], $p);
		$s += substr_count($r['title'], $p);
		$s += substr_count($r['description'], $p);
	}

	return $s;
}

function strlensort($a, $b){
    return strlen($b) - strlen($a);
}


function highlight_res($psearchs, $text, $address = false){
	if($address){
		$text = strtolower($text);

		// use this instead:
		usort($psearchs, 'strlensort');
		$ret = '';

		for($i = 0; $i < strlen($text); ++$i){
			$taken = false;
			foreach($psearchs as $s){
				if(substr($text, $i, strlen($s)) == $s){
					$taken = true;
					$ret .= '<b>';
					$ret .= $s;
					$ret .= '</b>';
					$i += strlen($s) - 1;
				}
			}

			if(!$taken){
				$ret .= $text{$i};
			}
		}

		return $ret;
	}

	$els = explode(' ', $text);
	$ret = '';

	foreach($els as $el){
		$taken = false;

		foreach($psearchs as $s){
			if((stripos($el, $s) !== false) && (strlen($s) * 2 > strlen($el))){
				$ret .= '<b>'.$el.'</b>'.' ';
				$taken = true;
				break;
			}
		}

		if(!$taken) {
			$ret .= $el.' ';
		}
	}

	return $ret;
}

function sort_result($psearchs, $res){
	$tmp = array();
	$ret = array();

	foreach($res as $r){
		$s = similarity($psearchs, $r);
		a:
		if(isset($tmp[$s])){
			$s += 0.0001;
			goto a;
		}else{
			$tmp[$s] = $r;
		}
	}

	krsort($tmp);

	foreach($tmp as $r) {
		$ret[] = $r;
	}

	return $ret;
}

?>
