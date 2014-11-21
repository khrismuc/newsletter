<?php

//$db = new mysqli("localhost", "root", "rampage", "general");

include "creds.php";

function connect_db($params) {
	extract($params);
	$db = new mysqli($server, $username, $password, $database);
	$db->set_charset("utf8");
	return $db;
}

$db = connect_db($dbconn);
if ($db->connect_error) die();

function query($query) {
	global $db;
	$result = $db->query($query);
	if (is_bool($result)) return false;
	if ($result->num_rows == 0) return null;
	while ($row = $result->fetch_assoc()) {
		$rows[] = $row;
	}
	return $rows;
}

?>