<?php
	// global config and database connection
	//include "../config.php";
	
	$useTemplate = false;
	
	include "emailbody.php";
	
	$lf = "\r\n";
	
	function send_mail($to, $name, $replyto, $subject, $message) {
		global $useTemplate;
		$from = "$name <$replyto>";
		
		if ($useTemplate) {
			// read templatefile
			$filename = "template.html";
			$file = fopen($filename, 'r');
			$body = fread($file, filesize($filename));
			fclose($file);
			
			$body = str_replace("[message]", $message, $body); 
		}
		else $body = $message;
		
		$headers =	'From: '.$from."\r\n".
					'Content-Type: text/html; charset=UTF-8'."\r\n".
					'X-Mailer: PHP/'.phpversion();
		if (mail($to, $subject, $body, $headers)) return true;
		else return false;
	}
	
	$message = "<p>Wir freuen uns über Deine Anmeldung zum Newsletter. Hier erhältst Du regelmäßig News bezüglich Sonderangeboten, Aktionen, neuen Artikeln und anderen Dingen rund um Deinen In-Shop <b>BlindYouth</b>!</p>";
	$message = <<<HTML
<p>Servus Flix, bin grade dran am Newsletter-System coden, wie Du siehst. Dauert aber noch ein Weilchen.</p>
<p>Das hier ist eine Test-Email, die vom Server verschickt wurde.</p>
HTML;
	
	$message = getHTML();
	
	$email = filter_input(INPUT_GET, "email");
	echo send_mail($email, "Blind Youth", "noreply@blindyouth.com", "Newsletter", $message) ? "success" : "failure";
?>