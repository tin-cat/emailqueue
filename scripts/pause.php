<?php

	/*
		EMailqueue
		Pause
	*/

	namespace Emailqueue;

	include_once dirname(__FILE__)."/../common.inc.php";
	
	header("content-type: text/plain");
	set_time_limit(0);
	
	echo date("j/n/Y H:i.s")." Emailqueue:Pause";

	if (isFlag("paused")) {
		echo " [Already paused]\n";
		die;
	}
	
	if (!setFlag("paused"))
		die;
	
	echo " [Paused]\n";

	$db->disconnect();

?>