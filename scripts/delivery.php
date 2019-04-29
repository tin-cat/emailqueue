<?php

	/*
		EMailqueue
		Delivery
	*/

	namespace Emailqueue;
	
	require_once(dirname(__FILE__)."/../common.inc.php");
	
	header("content-type: text/plain");
	set_time_limit(0);
	
	echo "Emailqueue · Delivery\n";
    if (IS_DEVEL_ENVIRONMENT) {
        echo "Reminder: Running in development environment, ";
        if (!$devel_emails)
            echo "no emails will be sent.\n";
        else
            echo "only emails to ".implode(", ", $devel_emails)." will be sent.\n";
    }
	echo "Maximum delivery timeout: ".(MAXIMUM_DELIVERY_TIMEOUT ? $utils->secondstohumantime(MAXIMUM_DELIVERY_TIMEOUT) : "unlimited")."\n";
	echo "Delivery interval: ".(DELIVERY_INTERVAL ? number_format((DELIVERY_INTERVAL/100), 2, ",", "")." seconds" : "none")."\n";
	echo "Maximum emails to deliver: ".(MAX_DELIVERS_A_TIME ? MAX_DELIVERS_A_TIME : "no limit")."\n";
	echo "Process started on: ".date("j/n/Y H:i.s")."\n";

    // Get blacklisted emails
    $db->query("select * from blacklist");
    if ($db->isanyresult()) {
        while($row = $db->fetchrow())
            $blacklisted_emails[] = $row["email"];
        $db->free();
    }

	$now = mktime();

    // Query emails to be sent
	$db->query("
		select
			*
		from
			emails
		where
			is_sent = 0
		and
			is_cancelled = 0
		and
			is_blocked = 0
		and (
			date_queued is null
			or
			(date_queued is not null and date_queued <= '".date("Y-n-j H:i:s", $now)."')
		)
		order by
			is_immediate desc,
			priority asc,
			date_queued asc
		".(MAX_DELIVERS_A_TIME ? " LIMIT 0, ".MAX_DELIVERS_A_TIME : "")."
	");
	
	if (!$db->isanyresult()) {
		echo "No emails on queue.\n";
	}
	else {
		while ($row = $db->fetchrow())
			$emails[] = $row;
		$db->free();
		$mail = buildPhpMailer();
		deliver_emails($mail, $emails, true);
		$mail->SmtpClose();
	}
	
	echo "Process ended on: ".date("j/n/Y H:i.s")."\n";
	
	$db->disconnect();

?>