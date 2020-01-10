<?php

	/*
		EMailqueue
		Pause
	*/

	namespace Emailqueue;

	include_once dirname(__FILE__)."/../common.inc.php";
	
	header("content-type: text/plain");
	set_time_limit(0);
	
	echo "Emailqueue · Pause\n";	
	echo "Pauses email delivery.\n";

	if (isFlag("paused")) {
		echo "Already paused.\n";
		die;
	}
	
	if (!setFlag("paused"))
		die;
	
	echo "Done. No emails will be delivered from now on.\n";

	$db->disconnect();

?>