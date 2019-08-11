<?php

	/*
		EMailqueue
		Frontend
	*/

	namespace Emailqueue;
	
	include_once dirname(__FILE__)."/../common.inc.php";

	if (!($_SERVER['PHP_AUTH_USER'] == FRONTEND_USER && $_SERVER['PHP_AUTH_PW'] == FRONTEND_PASSWORD)) {
		header("WWW-Authenticate: Basic realm=\"Emailqueue frontend\"");
		header("HTTP/1.0 401 Unauthorized");
		echo "Access restricted";
		exit;
	}
	
	$a = $utils->getglobal("a");
	if (!$a || $a == "")
		$a = "home";
	
	switch ($a) {
		case "home":
			include_once dirname(__FILE__)."/../classes/home.class.php";
			$home = new home();
			$output->add($home->getinfo());
			break;
		
		case "manager":
            include_once dirname(__FILE__)."/../classes/manager.class.php";
            $manager = new manager();
            $output->add($manager->run());
            break;

		case "servicetools":
			include_once dirname(__FILE__)."/../classes/servicetools.class.php";
			$servicetools = new servicetools();
			$output->add($servicetools->run());
			break;
	}
	
	// Control cases wich don't need head nor footer
	if ($utils->getglobal("aa") != "view_iframe_body") {
	   $output->add_tobeggining($html->head());
	   $output->add($html->foot());
	}
	
	$output->dump();
	
	$db->disconnect();

?>
