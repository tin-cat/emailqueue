<?php

	namespace Emailqueue;

	class home {
		function getinfo() {
		  	global $db;
			global $messages;
			global $devel_emails;
			
			// Get statistical data
			// Get total number of sent messages
			$db->query("select count(*) as numberof from emails where is_sent = 1");
			$row = $db->fetchrow();
			$messages_sent = $row["numberof"];
			
			// Get total number of cancelled messages
			$db->query("select count(*) as numberof from emails where is_cancelled = 1");
			$row = $db->fetchrow();
			$messages_cancelled = $row["numberof"];
			
			// Get total number of messages injected today
			$db->query("select count(*) as numberof from emails where YEAR(date_injected) = ".date("Y")." and MONTH(date_injected) = ".date("n")." and DAYOFMONTH(date_injected) = ".date("j")."");
			$row = $db->fetchrow();
			$messages_injected_today = $row["numberof"];
			
			// Get total number of messages injected last hour
			$db->query("select count(*) as numberof from emails where date_injected >= '".date("Y-n-j H:i:s", time()-(60*60))."'");
			$row = $db->fetchrow();
			$messages_injected_lasthour = $row["numberof"];
			
			// Get total number of messages in queue
			$db->query("select count(*) as numberof from emails where is_sent = 0 and is_cancelled = 0 and is_blocked = 0");
			$row = $db->fetchrow();
			$messages_in_queue = $row["numberof"];
			
			$retr = "
                <div class=block>
                    <div class=block_title>Status</div>
		  			<div class=pairs>
                        <div class=pair><div class=key>Environment</div><div class=value>".(IS_DEVEL_ENVIRONMENT ? "⚙️Development" : "✅Production")."</div></div>
						<div class=pair><div class=key>Delivery</div><div class=value>".(isFlag("paused") ? "⚠️Paused" : "✅Delivering")."</div></div>
                        ".(IS_DEVEL_ENVIRONMENT && is_array($devel_emails) ? "<div class=pair><div class=key>Deliver only to</div><div class=value>".implode(", ", $devel_emails)."</div></div>" : "")."
                        <div class=pair><div class=key>Maximum delivery timeout</div><div class=value>".number_format(MAXIMUM_DELIVERY_TIMEOUT, 0, ".", ",")." seconds</div></div>
                        <div class=pair><div class=key>Delivery interval</div><div class=value>".number_format(DELIVERY_INTERVAL*10, 0, ".", ",")." ms.</div></div>
                        <div class=pair><div class=key>Maximum retry attempts</div><div class=value>".SENDING_RETRY_MAX_ATTEMPTS."</div></div>
                        <div class=pair><div class=key>Maximum delivers per call</div><div class=value>".number_format(MAX_DELIVERS_A_TIME, 0, ".", ",")."</div></div>
                        <div class=pair><div class=key>SMTP server</div><div class=value>".(SMTP_SERVER == "127.0.0.1" || SMTP_SERVER == "localhost" ? "Same server" : SMTP_SERVER)."</div></div>
                        <div class=pair><div class=key>Purge messages older than</div><div class=value>".PURGE_OLDER_THAN_DAYS." days</div></div>
                    </div>
                    <div class=pairs>
                        <div class=pair><div class=key>Messages sent</div><div class=value>".number_format($messages_sent, 0, ".", ",")."</div></div>
                        <div class=pair><div class=key>Messages cancelled</div><div class=value>".number_format($messages_cancelled, 0, ".", ",")."</div></div>
                        <div class=pair><div class=key>Messages injected today</div><div class=value>".number_format($messages_injected_today, 0, ".", ",")."</div></div>
                        <div class=pair><div class=key>Messages injected last hour</div><div class=value>".number_format($messages_injected_lasthour, 0, ".", ",")."</div></div>
                        <div class=pair><div class=key>Messages in queue</div><div class=value>".number_format($messages_in_queue, 0, ".", ",")."</div></div>
		  			</div>
                </div>
			";
		  
		  	// Waiting messages list
		  	$retr .= "
				<div class=block>
		  			<div class=block_title>First ".QUEUED_MESSAGES." messages waiting to be delivered</div>
		  	";
		  	
		  	$db->query("
		  		select			*
		  		from			emails
		  		where			is_sent = 0
		  		and				is_cancelled = 0
		  		order by		is_immediate desc, priority asc, (date_queued is null) asc, date_injected desc
		  		limit           0, ".QUEUED_MESSAGES."
		  	");
		  	$list = $messages->get_list();
		  	if(!$list)
		  		$list = "No messages waiting";
		  	
		  	$retr .= "
		  			".$list."
		  		</div>
		  	";
		  	
		  	// Latest delivered messages
		  	$retr .= "
				<div class=block>
		  			<div class=block_title>Last ".LATEST_DELIVERED_MESSAGES." delivered messages</div>
		  	";
		  	
		  	$db->query("
		  		select			*
		  		from			emails
		  		where			is_sent
		  		order by		date_sent desc
		  		limit			0, ".LATEST_DELIVERED_MESSAGES."
		  	");
		  	$list = $messages->get_list();
		  	if(!$list)
		  		$list = "No delivered messages";
		  	
		  	$retr .=
		  	"
		  			".$list."
		  		</div>
		  	";
		  	
		  	// Cancelled
		  	$retr .= "
				<div class=block>
		  			<div class=block_title>Last ".LATEST_CANCELLED_MESSAGES." cancelled messages</div>
		  	";
		  	
		  	$db->query("
		  		select			*
		  		from			emails
		  		where			is_cancelled = 1
		  		order by		date_sent desc
		  		limit           0, ".LATEST_CANCELLED_MESSAGES."
		  	");
		  	$list = $messages->get_list();
		  	if(!$list)
		  		$list = "None";
		  	
		  	$retr .="
		  			".$list."
		  		</div>
		  	";
		  	
			return $retr;
		}
	}

?>
