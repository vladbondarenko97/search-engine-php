<?php

require('./config.php');

$ip = $_SERVER['REMOTE_ADDR'];
$custom_session = md5(date('m-d') . $ip);
$_SESSION['mobile'] = 'true';

headerHTML('Crawler Search');

?>
<div align="center">
  <h1><a onclick="javascript:home();" title="Home">CRAWLER SEARCH</a></h1>
  <input type="search" id="q" value="<?=@htmlentities($_GET['q']);?>" placeholder="Enter keywords here..." style="width:300px;" name="q" onkeyup="search();" autofocus="true" required maxlength="50"/>
  <script>
    /* Detect if iDevice */
    if(navigator.userAgent.match(/iPad/i) || navigator.userAgent.match(/iPhone/i) || navigator.userAgent.match(/iPod/i)) {
      document.write('<input type="submit" value="Search" ontouchstart="submit(); return false;"/>');
    } else {
      document.write('<input type="submit" value="Search" onclick="submit(); return false;"/>');
    }

  </script>
  <input type="hidden" name="page"/>
</div>
<div id="error" align="center"></div>
<div id="page"><div align="center" style="margin:15%;">Thank you for trying Crawler Search. We are still in alpha development mode and there are not a lot of results in our database. Would you like to suggest a feature? Please e-mail the head-developer - <em>contactvlad1k@gmail.com</em>.<br/>

<?php
if(isset($_SESSION['history']) && $_SESSION['history'] != NULL) {
	$h_html = NULL;
	$h_html .= '<b>Recent Searches:</b> ';
	foreach($_SESSION['history'] as $history) {
		$h_html .= '<a title="Search for: '.strip_tags(htmlentities($history)).'" onclick="javascript:asearch(\''.strip_tags(htmlentities($history)).'\');">' . $history . '</a>, ';
	}
	$h_html = trim($h_html, ", ").'.';
} else {
	$h_html = 'You do not have any history.';
}
echo $h_html;
?>

<br/></div></div>
<script>
var fram = 0;
window.onload = checkHash();
var page = document.getElementById('page');
var error = document.getElementById('error');
function home() {
  document.getElementById('page').innerHTML = '<div align="center" style="margin:15%;">Thank you for trying Crawler Search. We are still in alpha development mode and there are not a lot of results in our database. Would you like to suggest a feature? Please e-mail the head-developer - <em>contactvlad1k@gmail.com</em>.</div>';
  document.title = 'Crawler Search';
  location.hash = '';
}
function strip_tags(html){
	if(arguments.length < 3) {
		html = html.replace(/<\/?(?!\!)[^>]*>/gi, '');
	} else {
		var allowed = arguments[1];
		var specified = eval("["+arguments[2]+"]");
		if(allowed) {
			var regex='</?(?!(' + specified.join('|') + '))\b[^>]*>';
			html=html.replace(new RegExp(regex, 'gi'), '');
		} else {
			var regex='</?(' + specified.join('|') + ')\b[^>]*>';
			html = html.replace(new RegExp(regex, 'gi'), '');
		}
	}
return html;
}

window.onfocus = function (){
	document.getElementById('q').focus();
}

function getHash(key) {
  if(location.hash.match(new RegExp(key+'=([^&]*)'))) {
    return location.hash.match(new RegExp(key+'=([^&]*)'))[1];
  } else {
    return false;
  }
}
function checkHash() {
   var page = document.getElementById('page');
   url_q = getHash('q');
   if(url_q != false) {
   	url_q = url_q.replace(/\+/g, ' ');
   	if(url_q != null) {
   	  document.getElementById('q').value = url_q;
   	  submit();
  	}
   }
}
function getPage(url) {
    var xmlHttp = null;

    xmlHttp = new XMLHttpRequest();
    xmlHttp.open("GET", url, false);
    xmlHttp.send(null);
    return xmlHttp.responseText;
}

function asearch(qword) {
	qword = qword.replace(/\+/g,' ');
	document.getElementById('q').value = qword;
	submit();
}

function search() {
	document.onkeypress = enter;
}

function enter(e) {
        if(e.which == 13) {
        	submit();
    	}
}

function autosize(ifrId){
  var ifr = document.getElementById(ifrId);
  var doc = ifr.contentDocument ? ifr.contentDocument : ifr.contentWindow.document;
  var obj = ifr.style ? ifr.style : ifr;
  obj.height = doc.body.scrollHeight + "px";
}

function submit() {
    var page = document.getElementById('page');
    var error = document.getElementById('error');
    var query = strip_tags(document.getElementById('q').value);
    query.trim();
    query = query.replace(/ +/g, ' ');
    document.getElementById('q').value = query;
    if(query == '' || query == ' ') {
    	return false;
    	document.getElementById('q').focus();
    } else if(query.length > 50) {
    	error.innerHTML = 'Your query is above 50 characters.';
    } else {
    	error.innerHTML = '';
    	if(fram == 0) {
    		page.innerHTML = '<iframe id="results" src="" frameborder="0" scrolling="no" style="overflow:hidden; overflow-x:hidden; overflow-y:hidden; height:150%; width:99%; position:absolute;" height="150%" width="99%" onload="autosize(\'results\');"/>';
    		fram = 1;
    	}
    	document.getElementById('results').src = 'search.php?q=' + query + '&session=<?=$custom_session;?>&no-cache=' + Math.random();
    	autosize('results');
    	document.title = query + ' - Crawler Search';
    	plushash = query.replace(/ /g, '+')
    	location.hash = '#?q=' + plushash;
    	document.getElementById('q').focus();
    }
}
</script>
</body>
</html>
