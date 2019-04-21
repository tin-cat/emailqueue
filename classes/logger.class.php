<?php

	namespace Emailqueue;

	class logger {
		var $filename_delivery;
		var $filename_incidences;
		
		function logger() {
			$this->filename_delivery = dirname(__FILE__).LOGS_DIR."/".$this->get_log_filename("delivery");
			$this->filename_incidences = dirname(__FILE__).LOGS_DIR."/".$this->get_log_filename("incidences");
			
			/*
			// Check for writable files if they already exists
			if(file_exists($this->filename_delivery) && !is_writable($this->filename_delivery))
			{
				echo "Error: ".$this->filename_delivery." is not writable.";
				die;
			}
			
			if(file_exists($this->filename_incidences) && !is_writable($this->filename_incidences))
			{
				echo "Error: ".$this->filename_incidences." is not writable.";
				die;
			}
			*/
			
			/*
			// Try to open log files for writing just to check (also creates them with 0 filesize if don't exist)
			if(!$handle = fopen($this->filename_delivery, "a"))
			{
			  	echo "Error: ".$this->filename_delivery." can't be opened for writing.";
			  	die;
			}
			else
			{
				fclose($handle);
			}
			
			if(!$handle = fopen($this->filename_incidences, "a"))
			{
			  	echo "Error: ".$this->filename_incidences." can't be opened for writing.";
			  	die;
			}
			else
			{
				fclose($handle);
			}
			*/
		}
	  
		function add_log_delivery($data) {
			/*
			$content = $this->get_logline($data);
			$handle = fopen($this->filename_delivery, "a");
			if(fwrite($handle, $content) === FALSE)
			{
			  	echo "Error: can't write to ".$this->filename_delivery;
			  	die;
			}
			fclose($handle);
			*/
		}
	
		function add_log_incidence($data) {
			/*
			$content = $this->get_logline($data);
			$handle = fopen($this->filename_incidences, "a");
			if(fwrite($handle, $content) === FALSE)
			{
			  	echo "Error: can't write to ".$this->filename_incidences;
			  	die;
			}
			fclose($handle);
			*/
		}
	
		function get_logline($data) {
		  	$retr .= date(LOGS_DATA_DATEFORMAT)."|";
		  	for($i=0; $i<sizeof($data); $i++)
		  		$data[$i] = str_replace("|", " ", $data[$i]);
			$retr .= implode("|", $data);
			$retr .= "\r\n";
			return $retr;
		}
	
		function get_log_filename($preffix) {
			global $now;
			return $preffix."_".date(LOGS_FILENAME_DATEFORMAT, $now).".log";
		}
	}

?>
