<?php
	namespace Emailqueue;

	use PHPMailer\PHPMailer\PHPMailer;
    use PHPMailer\PHPMailer\Exception;


	define("VERSION", "3.411");
	define("OFFICIAL_PAGE_URL", "https://github.com/tin-cat/emailqueue");

	require_once(dirname(__FILE__)."/config/db.config.inc.php");
	require_once(dirname(__FILE__)."/config/application.config.inc.php");
	
	date_default_timezone_set(DEFAULT_TIMEZONE);

	require_once(dirname(__FILE__)."/classes/database/dbsource.inc.php");
	require_once(dirname(__FILE__)."/classes/database/dbsource_mysqli.inc.php");

	global $db;
	$db = new dbsource_mysqli(
		EMAILQUEUE_DB_HOST,
		EMAILQUEUE_DB_UID,
		EMAILQUEUE_DB_PWD,
		EMAILQUEUE_DB_DATABASE
	);
	if (!$db->connect()) {
		throw new \Exception("Cannot connect to database");
		die;
	}
	
	$db->query("set names UTF8");
	
	require("classes/out.class.php");
	global $output;
	$output = new \Emailqueue\output;
	
	require("classes/html.class.php");
	global $html;
	$html = new \Emailqueue\html;
	
	require("classes/utils.class.php");
	global $utils;
	$utils = new \Emailqueue\utils;
	
	require("classes/messages.class.php");
	global $messages;
	$messages = new \Emailqueue\messages;

	require("classes/logger.class.php");
	global $logger;
	$logger = new \Emailqueue\logger;
	
	function checkemail($email) {
        return preg_match("/^[.\w-]+@([\w-]+\.)+[a-zA-Z]{2,15}$/", $email);
	}
	
	function add_incidence($email_id, $description) {
		global $db;
		
		$db->query
		("
			insert into
				incidences (
					email_id,
					date_incidence,
					description
				)
			values (
				".$email_id.",
				'".date("Y-n-j H:i:s")."',
				'".$db->safestring($description)."'
			)
		");
	}
	
	function mark_as_sent($email_id) {
		global $db;		
		$db->query("update emails set is_sent = 1 where id = ".$email_id);
	}
	
	function cancel($email_id) {
		global $db;		
		$db->query("update emails set is_cancelled = 1 where id = ".$email_id);
	}
	
	function uncancel($email_id) {
		global $db;		
		$db->query("update emails set is_cancelled = 0 where id = ".$email_id);
	}
	
	function block($email_id) {
		global $db;		
		$db->query("update emails set is_blocked = 1 where id = ".$email_id);
	}
	
	function unblock($email_id) {
		global $db;		
		$db->query("update emails set is_blocked = 0 where id = ".$email_id);
	}
	
	function setsendingnow($email_id) {
		global $db;		
		$db->query("update emails set is_sendingnow = 1 where id = ".$email_id);
	}
	
	function unsetsendingnow($email_id) {
		global $db;		
		$db->query("update emails set is_sendingnow = 0 where id = ".$email_id);
	}

	function update_send_count($email_id, $count) {
		global $db;		
		$db->query("update emails set send_count = ".$count." where id = ".$email_id);
	}
	
	function update_error_count($email_id, $count) {
		global $db;		
		$db->query("update emails set error_count = ".$count." where id = ".$email_id);
	}
	
	function update_sentdate($email_id, $timestamp) {
		global $db;		
		$db->query("update emails set date_sent = '".date("Y-n-j H:i:s", $timestamp)."' where id = ".$email_id);
	}

	function buildPhpMailer() {
		require_once(dirname(__FILE__)."/vendor/autoload.php");
		$mail = new \PHPMailer\PHPMailer\PHPMailer(true);
		$mail->CharSet = CHARSET;
        if (SEND_METHOD == "smtp") {
            $mail->IsSMTP();
            $mail->Host = SMTP_SERVER;
			$mail->Port = SMTP_PORT;
            $mail->SMTPKeepAlive = true;

            if (SMTP_IS_AUTHENTICATION) {
                $mail->SMTPAuth = true;
                $mail->Username = SMTP_AUTHENTICATION_USERNAME;
                $mail->Password = SMTP_AUTHENTICATION_PASSWORD;
            }
        }
        else if (SEND_METHOD == "sendmail")
        	$mail->IsSendmail();
		return $mail;
	}

	function deliver_emails(&$mail, $emails, $isOutputVerbose = false) {
		global $logger;
		$timecontrol_start = time();
		
		foreach ($emails as $email) {
			deliver_email($mail, $email, $isOutputVerbose);
			
			// Check if maximum delivery timeout have been reached
			if ((time() - $timecontrol_start) > MAXIMUM_DELIVERY_TIMEOUT) {
				if ($isOutputVerbose) {
                	echo " [Reached maximum delivery timeout]"; 
				}
                $logger->add_log_incidence(
                    array(
                        0,
                        "",
                        "Maximum delivery timeout reached",
                        "The delivery proccess have been automatically stopped before it finishes because of too many time spent on delivering. Time spent: ".(time() - $timecontrol_start)." seconds. Maximum time allowed: ".MAXIMUM_DELIVERY_TIMEOUT." seconds" 
                    )
                );
                break;
			}
		}
	}

	function deliver_email(&$mail, $email, $isOutputVerbose = false) {
		global $logger;
		global $devel_emails;
		global $db;
		
		$mail->clearAllRecipients();
		$mail->clearAddresses();
		$mail->clearCCs();
		$mail->clearBCCs();
		$mail->clearReplyTos();
		$mail->clearAttachments();
		$mail->clearCustomHeaders();

		if ($isOutputVerbose) {
			flush();
			echo " [#".$email["id"]." / ".$email["to"];
		}

		$isHasToBeSent = true;
		
		if ($email["is_sendingnow"]) {
			if ($isOutputVerbose)
				echo " / Already being sent";
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
			$isHasToBeSent = false;
		}
		
		if (!checkemail($email["to"])) {
			echo " / Bad recipient";
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
			$isHasToBeSent = false;
		}
		
		if (!checkemail($email["from"])) {
			if ($isOutputVerbose)
				echo " / Bad sender";
			add_incidence($email["id"], "Incorrect sender email address: ".$email["from"]);
			cancel($email["id"]);
			$logger->add_log_incidence
			(
				array
				(
					$email["id"],
					$email["from"],
					"Email cancelled",
					"Incorrect sender email address"
				)
			);
			$isHasToBeSent = false;
		}
		
		// Check black list
		$db->query("select id from blacklist where email = '".$db->safestring($email["to"])."'");
		if ($db->isanyresult()) {
			if ($isOutputVerbose)
				echo " / Black listed";
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
			$isHasToBeSent = false;
		}

		if ($isHasToBeSent) {

			if (!IS_DEVEL_ENVIRONMENT || (IS_DEVEL_ENVIRONMENT && is_array($devel_emails) && in_array($email["to"], $devel_emails))) {

				setsendingnow($email["id"]);

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
					if ($email["from_name"] != "")
						$mail->FromName = $email["from_name"];
					
					if ($email["sender"] != "")
						$mail->Sender = $email["sender"];

					$to = $email["to"];

					$mail->AddAddress($to);

					$mail->Subject = $email["subject"];

					$mail->WordWrap = 80;

					$body = $email["content"];
					$body = preg_replace('/\\\\/','', $body);

					if($email["is_html"]) {
						$mail->IsHTML(true);
							$mail->AltBody = "Please use an HTML compatible email viewer";
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
											throw new \Exception("Can't guess mimetype for ".$attachment["path"]);
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

				} catch (\PHPMailer\PHPMailer\Exception $e) {

					$isError = true;
					$errorText = $e->getMessage();

				} catch (\Exception $e) {

					$isError = true;
					$errorText = $e->getMessage();

				}

				if ($isError) {

					if ($isOutputVerbose)
						echo " / Error ".$errorText;
					
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
						if ($isOutputVerbose)
							echo " / No more attempts";
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
						if ($isOutputVerbose)
							echo " / Scheduled for one more try";
					}

				} else {
					
					mark_as_sent($email["id"]);
					update_send_count($email["id"], $email["send_count"]+1);
					update_sentdate($email["id"], time());
					$logger->add_log_delivery(array(
						$email["id"],
						"Email delivered",
						$email["from"],
						$email["to"],
						$email["subject"]
					));
					if ($isOutputVerbose)
						echo " / Processed";
					
					// Sleeping
					usleep((DELIVERY_INTERVAL/100));

				}

				unsetsendingnow($email["id"]);

			} else
				if ($isOutputVerbose)
					echo " / Not devel allowed recipient";
		
		}
				
		if ($isOutputVerbose)
			echo "]";
	}

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

	function getFlagsDir() {
		return dirname(__FILE__)."/flags";
	}

	function getFlagFileName($flagName) {
		return getFlagsDir()."/".$flagName;
	}

	function isFlag($flagName) {
		return file_exists(getFlagFileName($flagName));
	}

	function setFlag($flagName) {
		if (!file_exists(getFlagsDir())) {
			mkdir(getFlagsDir(), 0777, true);
		}
		if (!file_put_contents(getFlagFileName($flagName), 1)) {
			echo "Couldn't set flag ".$flagName."\n";
			return false;
		}
		return true;
	}

	function unsetFlag($flagName) {
		if (!unlink(getFlagFileName($flagName))) {
			echo "Couldn't unset flag ".$flagName."\n";
			return false;
		}
		return true;
	}

?>
