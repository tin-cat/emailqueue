<?php

	namespace Emailqueue;
		
  	class dbsource_mysql extends dbsource
	{
		var $host;
		var $uid;
		var $pwd;
		var $database;
		var $connectionid;

		function __construct ($host, $uid, $pwd, $database = "", $avoidpersistence = false)
		{
			$this->dbsource ("mysql");
			$this->host = $host;
			$this->uid = $uid;
			$this->pwd = $pwd;
			$this->database	= $database;
			$this->avoidpersistence = $avoidpersistence;
		}

		function connect($select = true)
		{
			$this->connectionid = mysql_connect ($this->host, $this->uid, $this->pwd, $this->avoidpersistence);
			if($select)
				$this->select($this->database);
			return $this->connectionid;
		}

		function select($database)
		{
			mysql_select_db($database);
		}

		function disconnect ()
		{
			mysql_close ($this->connectionid);
		}

		function query ($sql)
		{
            $this->last_query = $sql;
            		
			$this->result = mysql_query ($sql, $this->connectionid);
			
			return $this->result;
		}
   
		function free ()
		{
			mysql_free_result ($this->result);
		}

		function isanyresult ()
		{
			return mysql_affected_rows ($this->connectionid);
		}

		function fetchrow ()
		{
			$this->row = mysql_fetch_assoc ($this->result);
			return $this->row;
		}

		function getfield ($field)
		{
			return $this->row[$field];
		}

		function countrows ()
		{
			return mysql_num_rows ($this->result);
		}
		
		function seek_row($numrow)
		{
            return mysql_data_seek($this->result, $numrow);
		}
   
		function getinsertid ()
		{
			return mysql_insert_id ($this->connectionid);
		}
		
		function get_errno()
		{
		  return mysql_errno($this->connectionid);
		}
		
		function get_err()
		{
		  return mysql_error($this->connectionid);
		}
		
		function dump_error()
		{
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
            return mysql_real_escape_string($string, $this->connectionid);
        }
        
	}

?>
