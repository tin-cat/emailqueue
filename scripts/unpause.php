<?php

	/*
		EMailqueue
		Pause
	*/

	namespace Emailqueue;

	include_once dirname(__FILE__)."/../common.inc.php";
	
	header("content-type: text/plain");
	set_time_limit(0);
	
	echo date("j/n/Y H:i.s")." Emailqueue:Unpause";

	if (!isFlag("paused")) {
		echo " [Not paused]\n";
		die;
	}
	
	if (!unsetFlag("paused"))
		die;
	
	echo " [Unpaused]\n";

	$db->disconnect();

?>