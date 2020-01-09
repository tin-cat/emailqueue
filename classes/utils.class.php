<?php

	namespace Emailqueue;
	
	class utils {
		function getglobal($var) {
			if(isset($GLOBALS[$var]))
				return $GLOBALS[$var];
			else
			if(isset($_POST[$var]))
				return $_POST[$var];
			else
			if(isset($_GET[$var]))
				return $_GET[$var];
			else
				return false;
		}

		function secondstohumantime($seconds) {
			$minutes = floor($seconds / 60);

			if ($minutes > 0) {
				$seconds = $seconds - ($minutes * 60);

				$hours = floor($minutes / 60);

				if ($hours > 0) {
					$minutes = $minutes - ($hours * 60);

					$days = floor($hours / 24);

					if ($days > 0) {
						$hours = $hours - ($days * 24);
						return $days." day".($days > 1 ? "s" : "").", ".$this->addzeros($hours, 2).":".$this->addzeros($minutes, 2).".".$this->addzeros($seconds, 2);
					}
					else
						return $this->addzeros($hours, 2).":".$this->addzeros($minutes, 2).".".$this->addzeros($seconds, 2);
				}
				else
					return $this->addzeros($minutes, 2)." min. ".$this->addzeros($seconds, 2)." sec.";
			}
			else
				return $this->addzeros($seconds, 2)." sec.";
		}

		function addzeros($string, $totalchars) {
			if (strlen($string) < $totalchars)
				return str_repeat("0", ($totalchars-strlen($string))).$string;
			else
				return $string;
		}

		function cuttext($string, $cutat, $finalstring = "") {
			$string = strip_tags($string);

			if (strlen($string) < $cutat)
				return $string;

			if (!$nextspace = strpos($string, " ", $cutat))
				return substr($string, 0, $cutat).$finalstring;
			else
				return substr($string, 0, $nextspace).$finalstring;
		}

		function date_specialformat($time, $is_ago_remaining = true) {
			$retr = "<b>".date("H:i.s", $time)."</b>";
			// If it's today
			if(date("j/n/Y", $time) == date("j/n/Y"))	
				$retr .= " today";
			else
			if(date("j/n/Y", $time) == date("j/n/Y", time()-(24*60*60)))
				$retr .= " yesterday";
			else
			if(date("j/n/Y", $time) == date("j/n/Y", time()+(24*60*60)))
				$retr .= " tomorrow";
			else
				$retr .= " ".date("j/n/y", $time);

			if ($is_ago_remaining) {
				if($time < time())
					$retr .= " / ".$this->secondstohumantime(time() - $time)." ago";
				else
					$retr .= " / ".$this->secondstohumantime($time - time())." remaining";
			}

			return $retr;
		}

		function redirect_javascript($url) {
			echo "<script language=javascript>document.location='".$url."';</script>";
		}


	}
?>