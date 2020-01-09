<?php

	/*
		EMailqueue
		Flush
	*/
	
	include_once dirname(__FILE__)."/../common.inc.php";
	
	header("content-type: text/plain");
	set_time_limit(0);
	
	echo "Emailqueue · Flush\n";	
	echo "Removes all emails from the queue.\n";
	echo "Process started on: ".date("j/n/Y H:i.s")."\n";
	
	$db->query("
		select
			id
		from
			emails
		where
			is_sendingnow = 0
	");
	
	if (!$db->isanyresult()) {
		echo "No emails to flush.\n";
	}
	else {
		$count = 0;
		while ($row = $db->fetchrow()) {
			$email_ids[] = $row["id"];
			$count ++;
		}        
		echo $count." emails to be flushed.\n";
		
		$count = 0;
		foreach ($email_ids as $email_id) {
			$db->query("delete from emails where id = ".$email_id);
			$db->query("delete from incidences where email_id = ".$email_id);
			$count ++;
		}
		echo $count." emails and related incidences purged.\n";
	}
	
	echo "Process ended on: ".date("j/n/Y H:i.s")."\n";
	
	$db->disconnect();

?>