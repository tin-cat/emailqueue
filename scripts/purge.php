<?php

	/*
		EMailqueue
		Purge
	*/
	
	include_once dirname(__FILE__)."/../common.inc.php";
	
	header("content-type: text/plain");
	set_time_limit(0);
	
	echo "Emailqueue � Purge\n";	
	echo "Purge messages older than ".PURGE_OLDER_THAN_DAYS." days.\n";
	echo "Process started on: ".date("j/n/Y H:i.s")."\n";
	
	$db->query("
		select
			id
		from
			emails
		where (
				is_sent = 1
			or
				is_cancelled = 1
		)
		and
			is_sendingnow = 0
		and
			date_injected <= '".date("Y-n-j H:i:s", mktime()-(PURGE_OLDER_THAN_DAYS*24*60*60))."'
	");
	
	if (!$db->isanyresult()) {
		echo "No emails to purge.\n";
	}
	else {
		$count = 0;
		while ($row = $db->fetchrow()) {
			$email_ids[] = $row["id"];
			$count ++;
		}        
		echo $count." emails to be purged.\n";
		
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