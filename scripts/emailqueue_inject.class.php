<?php

    /*
		EMailqueue
		Inject
	*/

    namespace Emailqueue;
	
	class emailqueue_inject {
        var $db_host;
        var $db_user;
        var $db_password;
        var $db_name;
        var $db;
        var $avoidpersistence;
        var $default_priority = 10;

        private $connectionid;
        
        function __construct($db_host, $db_user, $db_password, $db_name, $avoidpersistence = false, $emailqueue_timezone = false) {
            $this->db_host = $db_host;
            $this->db_user = $db_user;
            $this->db_password = $db_password;
            $this->db_name = $db_name;
            $this->avoidpersistence = $avoidpersistence;
            if(!$emailqueue_timezone)
                $this->emailqueue_timezone = DEFAULT_TIMEZONE;
            else
                $this->emailqueue_timezone = $emailqueue_timezone;
        }
        
        function db_connect() {
            if(!$this->connectionid = mysqli_connect($this->db_host, $this->db_user, $this->db_password)) {
                $this->error("Cannot connect to database on ".$this->db_host." for user ".$this->db_user);
                die;
            }
			if (!mysqli_select_db($this->connectionid, $this->db_name)) {
				$this->error("Cannot select database ".$this->db_name);
                die;
			}
			if (!mysqli_query($this->connectionid, "set names utf8mb4")) {
				$this->error("Cannot set names to UTF8");
                die;
			}
        }
        
        function db_disconnect() {
            mysqli_close($this->connectionid);
        }
        
        function inject($p) {
            $parameters = [
                "foreign_id_a"=> ["default" => false],
                "foreign_id_b"=> ["default" => false],
                "priority" => ["default" => 10],
                "is_immediate" => ["default" => true],
                "date_queued"=> ["default" => false],
                "is_html"=> ["default" => true],
                "from"=> ["default" => false],
                "from_name"=> ["default" => false],
                "to"=> ["default" => false],
                "replyto"=> ["default" => false],
                "replyto_name"=> ["default" => false],
                "sender" => ["default" => false],
                "subject"=> ["default" => false],
                "content"=> ["default" => false],
                "content_nonhtml"=> ["default" => false],
                "list_unsubscribe_url"=> ["default" => false],
                "attachments"=> ["default" => false],
                "custom_headers"=> ["default" => false],
                "is_send_now"=> ["default" => false],
				"is_embed_images"=> ["default" => false]
            ];

			foreach ($parameters as $key => $parameter) {
                if (isset($parameter["default"]) && !isset($p[$key]))
                    $p[$key] = $parameter["default"];
            }

			// Cleaning
			foreach (["from", "to", "replyto", "sender"] as $key)
				$p[$key] = trim($p[$key]);

            $this->db_connect();

            if ($p["is_send_now"])
                $p["is_immediate"] = true;
        
            $p["subject"] = str_replace("\\'", "'", $p["subject"]);
            $p["subject"] = str_replace("'", "\'", $p["subject"]);

            // Some recommendations have been found about not sending emails longer than 63k bytes, it seems that triggers lots of spam-detection alarms.
            if(strlen($p["content"]) > 63000)
                $p["content"] = substr($p["content"], 0, 63000);
            
            $p["content"] = str_replace("\\'", "'", $p["content"]);
            $p["content"] = str_replace("'", "\'", $p["content"]);
            
            $p["content_nonhtml"] = str_replace("\\'", "'", $p["content_nonhtml"]);
            $p["content_nonhtml"] = str_replace("'", "\'", $p["content_nonhtml"]);

            // Prepare and check attachments array
            if ($p["attachments"]) {
                if (!is_array($p["attachments"])) {
                    $this->error("Attachments parameter must be an array.");
                    return false;
                }
                foreach ($p["attachments"] as $attachment) {
                    if (!is_array($attachment)) {
                        $this->error("Each attachment specified on the attachments array must be a hash array.");
                        return false;
                    }
                    if (!file_exists($attachment["path"])) {
                        $this->error("Can't open attached file for reading.");
                        return false;
                    }
                }
            }

            if ($p["custom_headers"]) {
                if (!is_array($p["custom_headers"])) {
                    $this->error("Custom headers parameter must be an array.");
                    return false;
                }
            }

            $result = mysqli_query(
				$this->connectionid,
				"
					insert into emails
					(
                        foreign_id_a,
                        foreign_id_b,
						priority,
						is_immediate,
						is_sent,
						is_cancelled,
						is_blocked,
						is_sendingnow,
						send_count,
						error_count,
						date_injected,
						date_queued,
						date_sent,
						is_html,
						`from`,
						from_name,
						`to`,
						replyto,
                        replyto_name,
                        sender,
						subject,
						content,
						content_nonhtml,
						list_unsubscribe_url,
                        attachments,
                        is_embed_images,
                        custom_headers
					)
					values
					(
						".($p["foreign_id_a"] ? $p["foreign_id_a"] : "null").",
						".($p["foreign_id_b"] ? $p["foreign_id_b"] : "null").",
						".($p["priority"] ? $p["priority"] : $this->default_priority).",
						".($p["is_immediate"] ? "1" : "0").",
						0,
						0,
						0,
						0,
						0,
						0,
						'".date("Y-n-j H:i:s", $this->timestamp_adjust(time(), $this->emailqueue_timezone))."',
						".($p["date_queued"] ? "'".date("Y-n-j H:i:s", $this->timestamp_adjust($p["date_queued"], $this->emailqueue_timezone))."'" : "null").",
						null,
						".($p["is_html"] ? "1" : "0").",
						".($p["from"] ? "'".$p["from"]."'" : "null").",
						".($p["from_name"] ? "'".$p["from_name"]."'" : "null").",
						".($p["to"] ? "'".$p["to"]."'" : "null").",
						".($p["replyto"] ? "'".$p["replyto"]."'" : "null").",
                        ".($p["replyto_name"] ? "'".$p["replyto_name"]."'" : "null").",
                        ".($p["sender"] ? "'".$p["sender"]."'" : "null").",
						'".$p["subject"]."',
						'".$p["content"]."',
						'".$p["content_nonhtml"]."',
						'".$p["list_unsubscribe_url"]."',
                        ".($p["attachments"] ? "'".serialize($p["attachments"])."'" : "null").",
                        ".($p["is_embed_images"] ? "1" : "0").",
                        ".($p["custom_headers"] ? "'".serialize($p["custom_headers"])."'" : "null")."
					)
				"
			);

			if (!$result) {
				$this->error("Error inserting message in the queue DB");
                die;
			}
            
            if ($p["is_send_now"]) {
                $email_id = mysqli_insert_id($this->connectionid);
                if (!$result = mysqli_query($this->connectionid, "select * from emails where id = ".$email_id)) {
                    $this->error("Couldn't retrieve the recently inserted email for 'send now' delivery.");
                    return false;
                }
                $email = $result->fetch_assoc();
                $result->free();

                require_once(dirname(__FILE__)."/../common.inc.php");
                
                $mail = buildPhpMailer();
                deliver_email($mail, $email, false);
                $mail->SmtpClose();
            }

            $this->db_disconnect();
            
            return $result ? true : false;
        }

        function timestamp_adjust($timestamp, $to_timezone) {
            $datetime_object = new \DateTime("@".$timestamp);

            $from_timezone_object = new \DateTimeZone(date_default_timezone_get());
            $to_timezone_object = new \DateTimeZone($to_timezone);

            $offset = $to_timezone_object->getOffset($datetime_object) - $from_timezone_object->getOffset($datetime_object);

            return $timestamp+$offset;
        }

        /**
         * Deletes all emails from the database that have already been delivered.
         * Useful if for some reason you need to clean your queue without losing any emails that have yet to be sent.
         * @return boolean True on success, false otherwise
         */
        function empty_delivered() {
            $this->db_connect();
            $result = mysqli_query($this->connectionid, "
                delete from
                    emails
                where
                    is_sent = 1
            ");
            $this->db_disconnect();
            return $result ? true : false;
        }

        /**
         * Deletes all emails that are in the queue waiting to be sent
         * Useful if for some reason you need to clean your outgoing queue. This will cause the loss of some emails that should be sent.
         * @return boolean True on success, false otherwise
         */
        function empty_queued() {
            $this->db_connect();
            $result = mysqli_query($this->connectionid, "
                delete from
                    emails
                where
                    is_sent = 0
            ");
            $this->db_disconnect();
            return $result ? true : false;
        }

        /**
         * Deletes all emails on the queue
         * Useful if for some reason you need to completely clean your queue. This will cause the loss of some emails that should be sent.
         * @return boolean True on success, false otherwise
         */
        function empty_all() {
            $this->db_connect();
            $result = mysqli_query($this->connectionid, "delete from emails");
            $this->db_disconnect();
            return $result ? true : false;
        }

        function error($description) {
			throw new \Exception($description);
		}
	}
	
?>
