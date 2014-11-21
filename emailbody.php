<?php
// database connection
require_once "mysqli.php";
include "markdown/Michelf/Markdown.inc.php";

$previewID = 999;

function get($what) {
	return isset($_GET[$what]) ? filter_input(INPUT_GET, $what) : null;
}

// read markdown from db, convert to HTML
$r = query("SELECT text FROM newsletters WHERE id = $previewID");
if ($r === false) die("loading preview from db failed"); 
$text = \Michelf\Markdown::defaultTransform($r[0]["text"]);

$css = <<<CSS
html {
	background-color: #ddd;
}
body {
	font-family: "Helvetica";
	width: 760px;
	margin: 30px auto;
	background-color: #fff;
	box-shadow: 0 0 5px 0 rgba(0,0,0,0.5);
	border-radius: 5px
}

#header {
	padding: 10px 20px;
	border-bottom: 1px solid #bbb
}

#logo {
	display: block;
	width:200px;
	height:56px;
}

#main {
	padding: 0 20px 10px
}
h1 {
	font-style: italic;
        color: lightblue;
	text-shadow: 1px 1px 0 blue;
}
CSS;

$html ='<html><body><style>' . $css . '</style><div id="header">';
$html .= '<img alt="logo" src="http://blindyouthclothing.com/newsletter/logo_200x56.png"/></div><div id="main">';
$html .= $text . '</div></body></html>';

if (get("action") === "echo") echo $html;

// for including
function getHTML() {
	global $html;
	return $html;
}

function getInnerHTML() {
	global $text;
	return $text;
}