<?

	/*
		EMailqueue
		Delivery component
	*/
	
	define("LIB_DIR", "lib");
	define("APP_DIR", "../");
	
	include APP_DIR."common.inc.php";
	
	include APP_DIR."lib/phpmailer/class.phpmailer.php";
	if (SEND_METHOD == "smtp")
		include APP_DIR."lib/phpmailer/class.smtp.php";
	
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

    // Query emails to be sent
	$db->query("
		select			*
		from			emails
		where			is_sent = 0
		and				is_cancelled = 0
		and				is_blocked = 0
		and
		(
			date_queued is null
			or
			(date_queued is not null and date_queued <= '".date("Y-n-j H:i:s", $now)."')
		)
		order by		is_inmediate desc, priority asc, date_queued asc
		".(MAX_DELIVERS_A_TIME ? " LIMIT 0, ".MAX_DELIVERS_A_TIME : "")."
	");
	
	if (!$db->isanyresult()) {
		echo "No emails on queue.\n";
	}
	else {
		while ($row = $db->fetchrow())
			$emails[] = $row;
		
		$timecontrol_start = mktime();

		$mail = new PHPMailer(true);

		$mail->CharSet = CHARSET;

        if (SEND_METHOD == "smtp") {
            $mail->IsSMTP();
            $mail->Host = SMTP_SERVER;
            $mail->SMTPKeepAlive = true;

            if (SMTP_IS_AUTHENTICATION) {
                $mail->SMTPAuth = true;
                $mail->Port = SMTP_PORT;
                $mail->Username = SMTP_AUTHENTICATION_USERNAME;
                $mail->Password = SMTP_AUTHENTICATION_PASSWORD;
            }
        }
        else if (SEND_METHOD == "sendmail")
        	$mail->IsSendmail();
		
		foreach ($emails as $email) {

			$mail->clearAllRecipients();
			$mail->clearAddresses();
			$mail->clearCCs();
			$mail->clearBCCs();
			$mail->clearReplyTos();
			$mail->clearAttachments();
			$mail->clearCustomHeaders();

			flush();
			echo $email["id"].": Sending email to ".$email["to"]." ... ";
			
			setsendingnow($email["id"]);
			
			if ($email["is_sendingnow"]) {
                echo "already being sent.";
                add_incidence($email["id"], "Try to send an email that is already being sent");
                $logger->add_log_incidence(
					array
					(
						$email["id"],
						$email["to"],
						"Email skipped",
						"Try to send an email that is already being sent"
					)
				);
			}
			
			if (!checkemail($email["to"])) {
				echo "bad recipient email address.";
				add_incidence($email["id"], "Incorrect recipient email address: ".$email["to"]);
				cancel($email["id"]);
				$logger->add_log_incidence(
					array(
						$email["id"],
						$email["to"],
						"Email cancelled",
						"Incorrect recipient email address"
					)
				);
			}
			
			if (!checkemail($email["from"])) {
				echo "bad addressee email address.";
				add_incidence($email["id"], "Incorrect addressee email address: ".$email["from"]);
				cancel($email["id"]);
				$logger->add_log_incidence
				(
					array
					(
						$email["id"],
						$email["to"],
						"Email cancelled",
						"Incorrect addressee email address"
					)
				);
			}
			
			// Check black list
			if (isset($blacklisted_emails))
	            if (is_array($blacklisted_emails) and in_array(strtolower(trim($email["to"])), $blacklisted_emails)) {
					echo "recipient is on the black list.";
					add_incidence($email["id"], "Recipient is on the black list: ".$email["to"]);
					cancel($email["id"]);
					$logger->add_log_incidence(
						array(
							$email["id"],
							$email["to"],
							"Email cancelled",
							"Recipient is on the black list"
						)
					);
				}

            if (!IS_DEVEL_ENVIRONMENT || (IS_DEVEL_ENVIRONMENT && in_array($email["to"], $devel_emails))) {

                $isError = false;
                try {

                        if ($email["custom_headers"]) {
                            $custom_headers = unserialize($email["custom_headers"]);

                            if (is_array($custom_headers)) {

                                if (array_key_exists("Content-Transfer-Encoding", $custom_headers)) {
                                    $mail->Encoding = $custom_headers["Content-Transfer-Encoding"];
                                    // We don't want to iterate over this header again in the foreach cicle.
                                    unset($custom_headers["Content-Transfer-Encoding"]);
                                } else {
                                    $mail->Encoding = CONTENT_TRANSFER_ENCODING;
                                }

                                foreach ($custom_headers as $header => $value) {
                                    $mail->AddCustomHeader($header, $value);
                                }
                            }
                        } else {
                            $mail->Encoding = CONTENT_TRANSFER_ENCODING;
                        }

	                if ($email["replyto"] != "") {
	                    if($email["replyto_name"] != "")
	                        $mail->AddReplyTo($email["replyto"], $email["replyto_name"]);
	                    else
	                        $mail->AddReplyTo($email["replyto"]);
	                }
	                else {
	                    $mail->AddReplyTo($email["from"]);
	                }

	                $mail->From = $email["from"];
	                if($email["from_name"] != "")
	                    $mail->FromName = $email["from_name"];

	                $to = $email["to"];

	                $mail->AddAddress($to);

	                $mail->Subject = $email["subject"];

	                $mail->WordWrap = 80;

	                $body = $email["content"];
	                $body = preg_replace('/\\\\/','', $body);

	                if($email["is_html"]) {
	                    $mail->IsHTML(true);
                            $mail->AltBody = "Please use an HTML compatible email viewer!";
                            $mail->MsgHTML($body);
                        } else {
                            $mail->Body = $body;
                        }

	                if($email["is_embed_images"])
	                	embed_images($body, $mail);


	                if($email["content_nonhtml"] != "")
	                    $mail->AltBody = $email["content_nonhtml"];

	                if($email["list_unsubscribe_url"] != "")
	                    $mail->AddCustomHeader("List-Unsubscribe: <".$email["list_unsubscribe_url"].">");

	                // Add attachments
	                if($email["attachments"]) {
	                	$attachments = unserialize($email["attachments"]);
	                	if (is_array($attachments)) {
	                    	foreach ($attachments as $attachment) {

	                    		if (!is_array($attachment))
	                    			continue;

	                    		if (!isset($attachment["fileName"]))
			                        $attachment["fileName"] = basename($attachment["path"]);

			                    if (!isset($attachment["encoding"]))
			                        $attachment["encoding"] = "base64";

			                    if (!isset($attachment["type"])) {
			                        if ($finfo = finfo_open(FILEINFO_MIME_TYPE)) {
			                            if (!$mimeType = finfo_file($finfo, $attachment["path"]))
			                            	throw new Exception("Can't guess mimetype for ".$attachment["path"]);
			                            finfo_close($finfo);
			                            $attachment["type"] = $mimeType;
			                        }
			                    }

	                    		$mail->AddAttachment(
	                    			$attachment["path"],
	                    			$attachment["fileName"],
	                    			$attachment["encoding"],
	                    			$attachment["type"]
	                    		);
	                    	}
	                    }
	                }
                
                	$mail->Send();

                } catch (phpmailerException $e) {

                	$isError = true;
                	$errorText = $e->getMessage();

                } catch (Exception $e) {

                	$isError = true;
                	$errorText = $e->getMessage();

                }

                if ($isError) {

                	echo "Error while sending email: ".$errorText.", ";
					
					if ($email["error_count"] == SENDING_RETRY_MAX_ATTEMPTS-1) {
						update_error_count($email["id"], $email["error_count"]+1);
						$incidence_text = "Error while sending email: [".$errorText."] Cancelled: No more sending attempts allowed";
						add_incidence($email["id"], $incidence_text);
						cancel($email["id"]);
						$logger->add_log_incidence(
							array(
								$email["id"],
								$email["to"],
								"Email cancelled",
								"No more sending attempts allowed"
							)
						);
						echo "No more attempts allowed, cancelled";
					}
					else {
						update_error_count($email["id"], $email["error_count"]+1);
						$incidence_text = "Error while sending email: [".$errorText."] Scheduled for one more try";
						add_incidence($email["id"], $incidence_text);
						$logger->add_log_incidence(array(
							$email["id"],
							$email["to"],
							"Email rescheduled",
							$incidence_text
						));
						echo "Scheduled for one more try";
					}

                } else {
					
					mark_as_sent($email["id"]);
					update_send_count($email["id"], $email["send_count"]+1);
					update_sentdate($email["id"], $now);
					$logger->add_log_delivery(array(
						$email["id"],
						"Email delivered",
						$email["from"],
						$email["to"],
						$email["subject"]
					));
					echo "Email processed";
					
					// Sleeping
					usleep((DELIVERY_INTERVAL/100));

				}

            } else
                echo "Running in devel environment, the recipient email isn't on the allowed devel emails. ";
			
			echo "\n";
			
			unsetsendingnow($email["id"]);
			
			// Check if maximum delivery timeout have been reached
			if ((mktime() - $timecontrol_start) > MAXIMUM_DELIVERY_TIMEOUT) {
                echo "Delivery proccess automatically stopped before it finished because of too many time spent on delivering. Time spent: ".(mktime() - $timecontrol_start)." seconds. Maximum time allowed: ".MAXIMUM_DELIVERY_TIMEOUT." seconds\n"; 
                $logger->add_log_incidence(
                    array(
                        0,
                        "",
                        "Maximum delivery timeout reached",
                        "The delivery proccess have been automatically stopped before it finishes because of too many time spent on delivering. Time spent: ".(mktime() - $timecontrol_start)." seconds. Maximum time allowed: ".MAXIMUM_DELIVERY_TIMEOUT." seconds" 
                    )
                );
                break;
			}
		}

		$mail->SmtpClose();
	}
	
	echo "Process ended on: ".date("j/n/Y H:i.s")."\n";
	
	$db->disconnect();

	// Function embed_images below by Emmanuel Alves http://stackoverflow.com/users/3821744/emmanuel-alves
	function embed_images(&$body, $mailer) {
	    // get all img tags
	    preg_match_all('/<img[^>]*src="([^"]*)"/i', $body, $matches);

	    if (!isset($matches[0]))
	        return;

	    foreach ($matches[0] as $index => $img) {
	        $src = $matches[1][$index];

	        if (preg_match("/\.jpg/", $src)) {
	            $dataType = "image/jpg";
	        } elseif (preg_match("/\.png/", $src)) {
	            $dataType = "image/jpg";
	        } elseif (preg_match("/\.gif/", $src)) {
	            $dataType = "image/gif";
	        } else {
	            // use the oldfashion way
	            $id = 'img' . $index;            
	            $mailer->AddEmbeddedImage($src, $id);
	            $body = str_replace($src, 'cid:' . $id, $body);
	        }

	        if($dataType) { 
	            $urlContent = file_get_contents($src);            
	            $body = str_replace($src, 'data:'. $dataType .';base64,' . base64_encode($urlContent), $body);
	        }
	    }
	}

?>