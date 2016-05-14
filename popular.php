<?php
if(!isset($query) || $query == NULL) die();

function define_w($q) {
	$html = NULL;
	if(isset($q) && $q != NULL) {
		$q = trim(strtolower($q));
		$link = 'http://www.stands4.com/services/v2/defs.php?uid=3092&tokenid=YQt6wfuHvEAUW8hh&word=';
		$xml = simplexml_load_file($link.$q) or die();
		foreach($xml->result as $result) {
			$term = $result->term;
			$def = $result->definition;
			$example = $result->example;
			$pos = $result->partofspeech;
			
			$html .= '<h2>'.strip_tags($term).'</h1>';
			$html .= '<em>'.strip_tags($pos).'</em><br/>';
			$html .= '<span>'.strip_tags($def).'</span>';
			if($example != NULL) {
				$html .= '<br/><span style="color:grey;"><em>'.$example.'</span><hr>';
			} else {
				$html .= '<hr/>';
			}
			break;
		}
	}
	if(isset($html) && $html != NULL) {
		return $html;
	} else {
		return 0;
	}
}

function favicon($src) {
	return '<img width="16px" height="16px" src="'.$src.'"/>';
}
function fchar($string, $n) {
	return substr($string, 0, $n);
}
function popular($word, $prefix, $url, $title, $query) {
	$proxy = 'http://vladsproxy.appspot.com/'; // proxy server, user protection
	if(contains($word, strtolower($query))) {
		$tags = get_meta_tags($prefix.$url);
		if(!isset($tags['description']) || $tags['description'] == NULL) {
			$tags['description'] = 'This website does not include a description in their META tag.';
		}
		echo '
		<div>
		<b>OFFICIAL</b> '.favicon($proxy.$url.'favicon.ico').' <a href="'.$prefix.$url.'" target="_blank">'.$title.'</a><br/>
		'.htmlentities($tags['description']).'<br/>
		<i><span style="color:green;">'.$prefix.$url.'</span></i>
		</div>
		<hr/>';
	}
}
function wiki($s) {
	$url = 'http://en.wikipedia.org/w/api.php?action=parse&page='.strip_tags($s).'&format=json&prop=text&section=0';
	$ch = curl_init($url);
	curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt ($ch, CURLOPT_USERAGENT, 'CrawlerBot 1.1 WikiPedia');
	$c = curl_exec($ch);
	$json = json_decode($c);
	if(isset($json->{'parse'}->{'text'}->{'*'})) {
		$content = $json->{'parse'}->{'text'}->{'*'};
		$pattern = '#<p>(.*)</p>#Us';
		if(preg_match($pattern, $content, $matches)) {
			return strip_tags($matches[1]);
		}
	} else {
		return 'e1';
	}
}

$first10 = fchar($query, 10);
$first7 = fchar($query, 7);
if(strlen($query) > 10 && $first10 == 'wikipedia ') {
	$query_w = substr($query, 10);
	if(strlen($query_w) > 1) {
		$wiki = wiki($query_w);
		if($wiki != 'e1' && $wiki != NULL) {
			echo '<h2>'.ucfirst($query_w).'</h2>';
			$wiki = preg_replace('/\[.*\]/', '', $wiki);
			echo $wiki;
			echo ' <a href="http://en.wikipedia.org/wiki/'.urlencode(ucfirst($query_w)).'" target="_blank">More...</a> <i><font color="grey">from Wikipedia</font></i><hr/>';
		}
	}
}
if(strlen($query) > 7 && $first7 == 'define ') {
	$query_w = substr($query, 7);
	if(strlen($query_w) > 1) {
		$define = define_w(htmlentities($query_w));
		if($define != NULL) {
			echo $define;
		}
	}
}

popular('youtube', 'http://', 'www.youtube.com/', 'YouTube', $query);
popular('google', 'http://', 'www.google.com/', 'Google', $query);
popular('yahoo', 'http://', 'www.yahoo.com/', 'Yahoo', $query);
popular('facebook', 'https://', 'www.facebook.com/', 'Facebook', $query);
popular('twitter', 'https://', 'twitter.com/', 'Twitter', $query);

?>