<?php

    namespace Emailqueue;

	class manager {
		function run() {
            global $utils;
            
            $aa = $utils->getglobal("aa");
            
            switch($aa) {
                case "view":
                    $retr = $this->view();
                    break;
                
                case "view_iframe_body":
                    $retr = $this->view_iframe_body();
                    break;
				
				case "block":
					$retr = $this->block();
					break;
				
				case "unblock":
					$retr = $this->unblock();
					break;
				
				case "cancel":
					$retr = $this->cancel();
					break;
				
				case "requeue":
					$retr = $this->requeue();
					break;
            }
            
            return $retr;
		}
        
        function view() {
            global $db;
            global $utils;
            
            $email_id = $utils->getglobal("email_id");
            
            $result = $db->query("select * from emails where id = ".$email_id);
			
			if (!$result || !$db->isanyresult())
				return "Error: Message ".$email_id." does not exists.";
			
            $message = $db->fetchrow();

            // Build attachments info
            if (!$message["attachments"])
                $attachmentsInfo = "No attachments";
            else {
                $attachments = unserialize($message["attachments"]);
                foreach ($attachments as $attachment)
                    $attachmentsInfo .= $attachment["path"]."<br>";
            }
            
            $retr =
            "
				<div class=block><a href=\"javascript:history.go(-1);\" class=button>&laquo; back</a></div>
				<div class=block>
					<div class=block_title>Message #".$email_id."</div>					
		  			
                        <div class=pairs>
                            <div class=pair>
                                <div class=key>Foreign id A</div>
                                <div class=value>".(!$message["foreign_id_a"] ? "none" : $message["foreign_id_a"])."</div>
                            </div>
                            <div class=pair>
                                <div class=key>Foreign id B</div>
                                <div class=value>".(!$message["foreign_id_b"] ? "none" : $message["foreign_id_b"])."</div>
                            </div>
                            <div class=pair>
                                <div class=key>Priority</div>
                                <div class=value>".$message["priority"]."</div>
                            </div>
                            <div class=pair>
                                <div class=key>Queued for inmediate sending</div>
                                <div class=value>".($message["is_immediate"] ? "Yes" : "No")."</div>
                            </div>
                            <div class=pair>
                                <div class=key>Sent</div>
                                <div class=value>".($message["is_sent"] ? "Yes" : "No")."</div>
                            </div>
                            <div class=pair>
                                <div class=key>Cancelled</div>
                                <div class=value>".($message["is_cancelled"] ? "Yes" : "No")."</div>
                            </div>
                            <div class=pair>
                                <div class=key>Blocked</div>
                                <div class=value>".($message["is_blocked"] ? "Yes" : "No")."</div>
                            </div>
                            <div class=pair>
                                <div class=key>Being sent now</div>
                                <div class=value>".($message["is_sendingnow"] ? "Yes" : "No")."</div>
                            </div>
                            <div class=pair>
                                <div class=key>No. of times sent</div>
                                <div class=value>".($message["send_count"] ? $message["send_count"]." times" : "not sent")."</div>
                            </div>
                        </div>
                        <div class=pairs>
                            <div class=pair>
                                <div class=key>Injected on</div>
                                <div class=value>".$utils->date_specialformat(strtotime($message["date_injected"]))."</div>
                            </div>
                            <div class=pair>
                                <div class=key>Queued for</div>
                                <div class=value>".
                                (
                                    $message["date_queued"]
                                    ?
                                    $utils->date_specialformat(strtotime($message["date_queued"]))
                                    :
                                    "none".($message["is_immediate"] ? ", queued for inmediate sending" : ", and not queued for inmediate sending")                                
                                ).
                                "</div>
                            </div>
                            <div class=pair>
                                <div class=key>Sent on</div>
                                <div class=value>".
                                (
                                    $message["date_sent"]
                                    ?
                                    $utils->date_specialformat(strtotime($message["date_sent"]))
                                    :
                                    "not sent"                                
                                ).
                                "</div>
                            </div>
                            <div class=pair>
                                <div class=key>HTML format</div>
                                <div class=value>".($message["is_html"] ? "Yes" : "No")."</div>
                            </div>
                            <div class=pair>
                                <div class=key>From</div>
                                <div class=value>".$message["from"].($message["from_name"] ? " [".$message["from_name"]."]" : "")."</div>
                            </div>
							<div class=pair>
                                <div class=key>To</div>
                                <div class=value>".$message["to"]."</div>
                            </div>
							<div class=pair>
                                <div class=key>Reply to</div>
                                <div class=value>".
								(
									$message["replyto"]
									?
									$message["replyto"].($message["replyto_name"] ? " [".$message["replyto_name"]."]" : "")
									:
									"none"
								).
								"</div>
                            </div>
							<div class=pair>
                                <div class=key>Subject</div>
                                <div class=value>".$message["subject"]."</div>
                            </div>
                            <div class=pair>
                                <div class=key>Attachments</div>
                                <div class=value>".$attachmentsInfo."</div>
                            </div>
                            <div class=pair>
                                <div class=key>Embed images</div>
                                <div class=value>".($message["is_embed_images"] ? "Yes" : "No")."</div>
                            </div>

                        </div>
                </div>
                <iframe class=\"message_preview\" src=\"?a=manager&aa=view_iframe_body&email_id=".$message["id"]."\" style=\"background: #fff;\"></iframe>
            ";
            
            return $retr;
        }
        
        function view_iframe_body() {
            global $db;
            global $utils;
            
            $email_id = $utils->getglobal("email_id");
            
            $db->query("select * from emails where id = ".$email_id);
            $message = $db->fetchrow();
            
            return $message["content"];	
        }
		
		function block() {
            global $db;
            global $utils;
            
            $email_id = $utils->getglobal("email_id");
            
            $result = $db->query("select * from emails where id = ".$email_id);
			
			if (!$result || !$db->isanyresult())
				return "Error: Message ".$email_id." does not exists.";
			
            $message = $db->fetchrow();
			
			$db->query("update emails set is_blocked = 1 where id = ".$email_id); 
			
			$utils->redirect_javascript("?a=home");
		}
		
		function unblock() {
            global $db;
            global $utils;
            
            $email_id = $utils->getglobal("email_id");
            
            $result = $db->query("select * from emails where id = ".$email_id);
			
			if (!$result || !$db->isanyresult())
				return "Error: Message ".$email_id." does not exists.";
			
            $message = $db->fetchrow();
			
			$db->query("update emails set is_blocked = 0 where id = ".$email_id); 
			
			$utils->redirect_javascript("?a=home");
		}
		
		function cancel() {
            global $db;
            global $utils;
            
            $email_id = $utils->getglobal("email_id");
            
            $result = $db->query("select * from emails where id = ".$email_id);
			
			if (!$result || !$db->isanyresult())
				return "Error: Message ".$email_id." does not exists.";
			
            $message = $db->fetchrow();
			
			$db->query("update emails set is_cancelled = 1 where id = ".$email_id); 
			
			$utils->redirect_javascript("?a=home");
		}
		
		function requeue() {
            global $db;
            global $utils;
            
            $email_id = $utils->getglobal("email_id");
            
            $result = $db->query("select * from emails where id = ".$email_id);
			
			if (!$result || !$db->isanyresult())
				return "Error: Message ".$email_id." does not exists.";
			
            $message = $db->fetchrow();
			
			$db->query("update emails set is_cancelled = 0, is_sent = 0 where id = ".$email_id); 
			
			$utils->redirect_javascript("?a=home");
		}

	}

?>
