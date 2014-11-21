<?php

session_start();

include "mysqli.php";

$flash = null;

// logging in?
if (isset($_POST['password'])) {
	if ($_POST['password'] == "rotetomate") {
		$_SESSION['user'] = "admin";
		$_SESSION['time'] = time();
	}
	else {
		$flash = "Falsches Passwort.";
	}
}

$loggedin = isset($_SESSION['user']);
// timeout?
if ($loggedin) {
	$timeout = 24 * 60 * 60; // 24 hours
	$t = time();
	if ($t - $_SESSION['time'] > $timeout) {
		session_destroy();
		$loggedin = false;
		$flash = "Sitzung abgelaufen, bitte erneut einloggen.";
	}
}

if (isset($_GET['action']) && $_GET['action'] == "logout") {
	session_destroy();
	$loggedin = false;
}

// action processing

?><!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<meta charset="UTF-8" />
		<link rel="stylesheet" href="style.css" />
		<link rel="stylesheet" href="font-awesome/css/font-awesome.min.css">
	</head>
	<body>
		<div id="header">
			<a href="index.php"><img alt="logo" src="http://blindyouthclothing.com/newsletter/logo_200x56.png"/></a>
		</div>
		<div id="main" style="">
			<h2>Newsletter Management</h2>
<?php

if ($flash) echo "<div id='flash'>$flash</div>";

if ($loggedin) {

?>
			<a href="?action=logout" style="float: right">logout</a>
			<nav id="mainnav">
			</nav>
			<textarea id="editor" onkeyup="change_tas(1);"></textarea>
<?php
}
else {
?>
			<form method="post" action="index.php">
				<input id="loginfield" type="password" name="password" />
				<input type="submit" style="display: none" />
			</form>
			<script>
				var field = document.querySelector("#loginfield");
				field.value = "";
				field.focus();
			</script>
<?php
}
?>
		</div>
		<div id="overlay">
			<div id="prompt"></div>
		</div>
		<script src="script.js"></script>
	</body>
</html>