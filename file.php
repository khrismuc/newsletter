<?php
// database connection
include "mysqli.php";

$key = "rotetomate";
$previewID = 999;

function get($what) {
	return isset($_GET[$what]) ? filter_input(INPUT_GET, $what) : null;
}
function post($what) {
	return isset($_POST[$what]) ? filter_input(INPUT_POST, $what) : null;
}

$action = get("action");
if ($action) {
	if ($action === "load") {
		$id = get("id");
		$r = query("SELECT text FROM newsletters WHERE id = $id");
		if ($r) echo $r[0]["text"];
		else echo "fail";
	}
	if ($action === "save") {
		$id = get("id");
		$text = post("text");
		$r = query("UPDATE newsletters SET text = '$text', lastchange = NOW() WHERE id = $id");
	}
	if ($action === "saveas") {
		$name = post("name");
		$text = post("text");
		$r = query("INSERT INTO newsletters (name, text) VALUES ('$name', '$text')");
	}
	if ($action === "rename") {
		$id = get("id");
		$name = post("name");
		$r = query("UPDATE newsletters SET name = '$name' WHERE id = $id");
	}
	if ($action === "dir") {
		$r = query("SELECT * FROM newsletters WHERE sent IS NULL && id != $previewID ORDER BY lastchange DESC");
		print_dir($r);
	}

	if ($action === "savepreview") {
		$text = addslashes(post("text"));
		$r = query("UPDATE newsletters SET text = '$text' WHERE id = $previewID");
	}
}

// if ajax, we're done here
if (get("ajax")) die();

function print_dir($r) {
	echo "[";
	$first = true;
	foreach ($r as $file) {
		if ($first) $first = false;
		else echo "," . PHP_EOL;
		extract($file);
		echo "{ \"name\": \"$name\", \"lastchange\": \"$lastchange\" }";
	}
	echo "]";
}

?>
<form method="post" action="file.php?action=rename&id=2">
	Filename: <input type="text" name="name" />
	<input type="submit" />
</form>