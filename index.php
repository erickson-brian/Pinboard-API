<?php

DEFINE("USER", "");
DEFINE("PASS", "");

require_once("pinboard.php");
require_once("pinboard_post.php");

// testing saving a post
$data = array(
		"url" 	=>	"http://www.google.com",
		"description"	=>	"my api test post"
	);
$pb = new PinboardPost(USER, PASS, $data);
$posts = $pb->addPost();

// need to verify success here //

echo $pb->getLastHttpCode();
echo "<pre>";
var_dump($posts);
echo "</pre>";
exit();


// testing a delete
$data = array("url" => "http://www.google.com");
$pb = new PinboardPost(USER, PASS, $data);
$posts = $pb->deletePost();

// verify success here //

echo($pb->getLastHttpCode());
echo "<pre>";
var_dump($posts);
echo "</pre>";
exit();

// testing class connection
$pb = new Pinboard(USER, PASS);
$posts = $pb->lastUpdate();

// validate success here //

echo($pb->getLastHttpCode());
echo "<pre>";
var_dump($posts);
echo "</pre>";
exit();

?>