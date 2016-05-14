<?php
require('../config.php');
headerHTML('Crawler Admin Panel');
if(isset($_GET['logout'])) {
    if(isset($_SESSION['username'])) {
        session_destroy();
        echo messageHTML('Logged out successfully.');
        echo redirectHTML('admin.php', 1);
        die();
    } else {
        redirectHTML($CONFIG_WEBSITE_URL.'admin.php', '0');
        die();
    }
}

if(!isset($_SESSION['username'])) {
    if(!isset($_POST['username']) && !isset($_POST['password'])) {
        echo loginHTML('admin.php');
        die();
    } else {
        if($_POST['password'] == $CONFIG_PASSWORD && $_POST['username'] == $CONFIG_USERNAME) {
            $_SESSION['username'] = $_POST['username'];
            redirectHTML('admin.php', '0');
            die();
        } else {
            echo loginHTML('admin.php');
            echo errorHTML('Invalid login credentials.', 'left');
            die();
        }
    }
}

?>
<h1>Welcome, <? echo $_SESSION['username']; ?>!</h1>
Thank you for logging in. To log out, click <a href="?logout">here</a>.<br />

<h2>Add URLs</h2>
To add URLs, click <a href="admin_add.php" target="_blank">here</a>.<br />

<h2>View URLs</h2>
To view URLs, click <a href="admin_view.php" target="_blank">here</a>.

<h2>Remove URLs</h2>
To remove URLs, click <a href="admin_remove.php" target="_blank">here</a>.

<h2>Crawl URLs</h2>
To crawl URLs, click <a href="admin_crawl.php" target="_blank">here</a>.
