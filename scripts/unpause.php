<?php

	/*
		EMailqueue
		Pause
	*/

	namespace Emailqueue;

	include_once dirname(__FILE__)."/../common.inc.php";
	
	header("content-type: text/plain");
	set_time_limit(0);
	
	echo "Emailqueue · Unpause\n";	
	echo "Unpauses email delivery.\n";

	if (!isFlag("paused")) {
		echo "Not paused.\n";
		die;
	}
	
	if (!unsetFlag("paused"))
		die;
	
	echo "Done. Emails will be delivered from now on.\n";

	$db->disconnect();

?>