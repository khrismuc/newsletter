<?php

// database connection
include "mysqli.php";

$key = "rotetomate";

function get($what) {
	return isset($_GET[$what]) ? filter_input(INPUT_GET, $what) : null;
}

function email_valid($email) {
	return preg_match('/\b[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,4}\b/i', $email) === 1;
}


// check GET params
$getkey = get('key');
if ($getkey != $key) die("FBI contacted.");

$action = get('action');
if (!$action) die("No action specified.");

if ($action == "add") {
	$email = get('email');
	if (!email_valid($email)) die("Bad email format.");
	else {
		$query = "SELECT * FROM subscribers WHERE email = '$email'";
		$r = query($query);
		if ($r) {
			if (get('ajax')) die("already");
			die("Address already in list.");
		}
		$query = "INSERT INTO subscribers (email, valid) VALUES ('$email', '1')";
		$r = query($query);
		$e = $db->error;
		if (get('ajax')) echo "done";
		else echo "success: $email added";
	}
}

if ($action == "delete") {
	$email = get('email');
	$query = "SELECT * FROM subscribers WHERE email = '$email'";
	$r = query($query);
	if (!$r) die("Removal failed, address not in list.");
	$query = "DELETE FROM subscribers WHERE email = '$email'";
	$r = query($query);
	$e = $db->error;
	if (get('ajax')) echo "done";
	echo "success: $email removed";
}
