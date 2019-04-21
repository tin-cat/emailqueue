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
                echo "Emailqueue Inject class component: Cannot connect to database";
                die;
            }
			mysqli_select_db($this->connectionid, $this->db_name);
			mysqli_query($this->connectionid, "set names utf8");
        }
        
        function db_disconnect() {
            mysqli_close($this->connectionid);
        }
        
        function inject($p) {
            $parameters = [
                "foreign_id_a"=> [],
                "foreign_id_b"=> [],
                "priority" => ["default" => 10],
                "is_immediate" => ["default" => true],
                "date_queued"=> [],
                "is_html"=> [],
                "from"=> [],
                "from_name"=> [],
                "to"=> [],
                "replyto"=> [],
                "replyto_name"=> [],
                "subject"=> [],
                "content"=> [],
                "content_nonhtml"=> [],
                "list_unsubscribe_url"=> [],
                "attachments"=> [],
                "custom_headers"=> [],
                "is_send_now"=> []
            ];

            while (list($key, $parameter) = each($parameters)) {
                if ($parameter["default"] && !isset($p[$key]))
                    $p[$key] = $parameter["default"];
                if ($p[$key])
                    $$key = $p[$key];
            }

            $this->db_connect();

            if ($is_send_now)
                $is_immediate = true;
        
            $subject = str_replace("\\'", "'", $subject);
            $subject = str_replace("'", "\'", $subject);

            // Some recommendations have been found about not sending emails longer than 63k bytes, it seems that triggers lots of spam-detection alarms.
            if(strlen($content) > 63000)
                $content = substr($content, 0, 63000);
            
            $content = str_replace("\\'", "'", $content);
            $content = str_replace("'", "\'", $content);
            
            $content_nonhtml = str_replace("\\'", "'", $content_nonhtml);
            $content_nonhtml = str_replace("'", "\'", $content_nonhtml);

            // Prepare and check attachments array
            if ($attachments) {
                if (!is_array($attachments)) {
                    echo "Emailqueue inject error: attachments parameter must be an array.";
                    return false;
                }
                foreach ($attachments as $attachment) {
                    if (!is_array($attachment)) {
                        echo "Emailqueue inject error: Each attachment specified on the attachments array must be a hash array.";
                        return false;
                    }
                    if (!file_exists($attachment["path"])) {
                        echo "Emailqueue inject error: Can't open attached file for reading.";
                        return false;
                    }
                }
            }

            if ($custom_headers) {
                if (!is_array($custom_headers)) {
                    echo "Emailqueue inject error: custom headers parameter must be an array.";
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
						".($foreign_id_a ? $foreign_id_a : "null").",
						".($foreign_id_b ? $foreign_id_b : "null").",
						".($priority ? $priority : $this->default_priority).",
						".($is_immediate ? "1" : "0").",
						0,
						0,
						0,
						0,
						0,
						0,
						'".date("Y-n-j H:i:s", $this->timestamp_adjust(mktime(), $this->emailqueue_timezone))."',
						".($date_queued ? "'".date("Y-n-j H:i:s", $this->timestamp_adjust($date_queued, $this->emailqueue_timezone))."'" : "null").",
						null,
						".($is_html ? "1" : "0").",
						".($from ? "'".$from."'" : "null").",
						".($from_name ? "'".$from_name."'" : "null").",
						".($to ? "'".$to."'" : "null").",
						".($replyto ? "'".$replyto."'" : "null").",
						".($replyto_name ? "'".$replyto_name."'" : "null").",
						'".$subject."',
						'".$content."',
						'".$content_nonhtml."',
						'".$list_unsubscribe_url."',
                        ".($attachments ? "'".serialize($attachments)."'" : "null").",
                        ".($is_embed_images ? "1" : "0").",
                        ".($custom_headers ? "'".serialize($custom_headers)."'" : "null")."
					)
				"
			);
            
            if ($is_send_now) {
                $email_id = mysqli_insert_id($this->connectionid);
                if (!$result = mysqli_query($this->connectionid, "select * from emails where id = ".$email_id)) {
                    echo "Emailqueue inject error: Couldn't retrieve the recently inserted email for 'send now' delivery.";
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
            $result = mysqli_query($this->connectionid, "
                delete from
                    emails
            ");
            $this->db_disconnect();
            return $result ? true : false;
        }

        // Deprecated method, left for compatibility purposes. Will likely be removed on future versions.
        function destroy() {}
	}
	
?>
