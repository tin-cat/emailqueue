<?php

	/*
		EMailqueue
		Delivery
	*/

	namespace Emailqueue;
	
	require_once(dirname(__FILE__)."/../common.inc.php");
	
	header("content-type: text/plain");
	set_time_limit(0);
	
	echo date("j/n/Y H:i.s")." Emailqueue:Delivery";
    if (IS_DEVEL_ENVIRONMENT) {
        echo " [Devel";
        if (!$devel_emails)
            echo " / No devel emails";
        else
            echo " / ".sizeof($devel_emails)." devel emails]";
    }

	if (isFlag("paused")) {
		echo " [Paused / Not sending]";
		$db->disconnect();
		die;
	}

    // Get blacklisted emails
    $db->query("select * from blacklist");
    if ($db->isanyresult()) {
        while($row = $db->fetchrow())
            $blacklisted_emails[] = $row["email"];
        $db->free();
    }

	$now = time();

	// If we're in the devel environment, get only emails addressed to the recipients listed on $devel_emails
	if (IS_DEVEL_ENVIRONMENT) {
		global $devel_emails;
		if (is_array($devel_emails)) {
			foreach ($devel_emails as $email)
				$develEmailsSqlArray[] = "emails.to = '".$email."'";
			$develEmailsWhere = " and (".implode(" or ", $develEmailsSqlArray).")";
		}
	}

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
		".($develEmailsWhere ? $develEmailsWhere : null)."
		order by
			is_immediate desc,
			priority asc,
			date_queued asc
		".(MAX_DELIVERS_A_TIME ? " LIMIT 0, ".MAX_DELIVERS_A_TIME : "")."
	");
	
	if (!$db->isanyresult()) {
		echo " [Empty queue]";
	}
	else {
		while ($row = $db->fetchrow())
			$emails[] = $row;
		$db->free();
		$mail = buildPhpMailer();
		deliver_emails($mail, $emails, true);
		$mail->SmtpClose();
	}
	
	$db->disconnect();

?>