<?php

	namespace Emailqueue;

	class dbsource_mysqli extends dbsource
	{
		var $host;
		var $uid;
		var $pwd;
		var $database;
		var $connectionid;

		function __construct($host, $uid, $pwd, $database = "")
		{
			$this->dbsource ("mysqli");
			$this->host		= $host;
			$this->uid		= $uid;
			$this->pwd		= $pwd;
			$this->database	= $database;
		}

		function connect($select = true)
		{
			$this->connectionid = mysqli_connect ($this->host, $this->uid, $this->pwd);
			if($select)
				$this->select($this->database);
			return $this->connectionid;
		}

		function select($database)
		{
			mysqli_select_db($this->connectionid, $database);
		}

		function disconnect ()
		{
			mysqli_close ($this->connectionid);
		}

		function query($query)
		{
						if(!parent::query($query))
				return false;
								
			if(!$this->result = mysqli_query($this->connectionid, $query))
								echo $this->dump_error();
			
			return $this->result;
		}

		function run_free()
		{
			mysqli_free_result ($this->result);
		}

		function run_isanyresult()
		{			
			return mysqli_affected_rows($this->connectionid);
		}

		function run_fetchrow()
		{
			$this->row = mysqli_fetch_assoc ($this->result);
			return $this->row;
		}

		function getfield($field)
		{
			return $this->row[$field];
		}

		function run_countrows()
		{
			return mysqli_num_rows ($this->result);
		}
		
		function run_seek_row($numrow)
		{
						return mysqli_data_seek($this->result, $numrow);
		}

		function getinsertid ()
		{
			return mysqli_insert_id ($this->connectionid);
		}
		
		function get_errno()
		{
			return mysqli_errno($this->connectionid);
		}
		
		function get_err()
		{
			return mysqli_error($this->connectionid);
		}
		
		function dump_error()
		{
			if(!$this->is_debug)
				return false;

			$bt = debug_backtrace();
						return
				"<div style=\"margin: 5px; padding: 5px; background: #fe0; color: #444;\">".
					"MySQL Error: (".$this->get_errno().")".$this->get_err()."<br>".
					"Query: \"".$this->last_query."\"<br>".
					($bt[1] ? "Backtrace file #1: ".$bt[1]["file"]." (line ".$bt[1]["line"].")<br>" : null).
					($bt[2] ? "Backtrace file #2: ".$bt[2]["file"]." (line ".$bt[2]["line"].")<br>" : null).
					($bt[3] ? "Backtrace file #3: ".$bt[3]["file"]." (line ".$bt[3]["line"].")<br>" : null).
					($bt[4] ? "Backtrace file #3: ".$bt[4]["file"]." (line ".$bt[4]["line"].")<br>" : null).
					($bt[5] ? "Backtrace file #3: ".$bt[5]["file"]." (line ".$bt[5]["line"].")<br>" : null).
				"</div>";
		}
		
		function safestring($string)
				{
						return mysqli_real_escape_string($this->connectionid, $string);
				}
				
	}

?>
