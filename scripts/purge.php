<?php

	/*
		EMailqueue
		Purge
	*/
	
	include_once dirname(__FILE__)."/../common.inc.php";
	
	header("content-type: text/plain");
	set_time_limit(0);
	
	echo date("j/n/Y H:i.s")." Emailqueue:Purge";
	
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
			date_injected <= '".date("Y-n-j H:i:s", time()-(PURGE_OLDER_THAN_DAYS*24*60*60))."'
	");
	
	if (!$db->isanyresult()) {
		echo " [No emails to purge]\n";
	}
	else {
		$count = 0;
		while ($row = $db->fetchrow()) {
			$email_ids[] = $row["id"];
			$count ++;
		}        
		
		$count = 0;
		foreach ($email_ids as $email_id) {
			$db->query("delete from emails where id = ".$email_id);
			$db->query("delete from incidences where email_id = ".$email_id);
			$count ++;
		}
		echo " [".$count." emails purged]\n";
	}
	
	$db->disconnect();

?>