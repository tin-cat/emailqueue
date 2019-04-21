<?php

	namespace Emailqueue;

	class dbsource_mysql_pdo extends dbsource
	{
		var $host;
		var $uid;
		var $pwd;
		var $database;
		var $charset;
		var $is_persistent;
		var $is_emulate_prepares;
		var $errmode;
		var $pdo;
		var $stmt;

		function __construct ($host, $uid, $pwd, $database = "", $charset = "utf8", $is_persistent = true, $is_emulate_prepares = false, $errmode = PDO::ERRMODE_EXCEPTION)
		{
			$this->dbsource ("mysql_pdo");
			$this->host = $host;
			$this->uid = $uid;
			$this->pwd = $pwd;
			$this->database = $database;
			$this->charset = $charset;
			$this->is_persistent = $is_persistent;
			$this->is_emulate_prepares = $is_emulate_prepares;
			$this->errmode = $errmode;
		}

		function connect($select = true)
		{
			$this->pdo = new PDO(
				"mysql:".
				"host=".$this->host.
				($select && $this->database ? ";dbname=".$this->database : "").
				($this->charset ? ";charset=".$this->charset : ""),
				$this->uid,
				$this->pwd,
				array(
					PDO::ATTR_PERSISTENT => $this->is_persistent,
					PDO::ATTR_EMULATE_PREPARES => $this->is_emulate_prepares,
					PDO::ATTR_ERRMODE => $this->errmode
				)
			);

			if($this->pdo)
				return true;
			else
				return false;
		}

		function select($database)
		{
			$this->pdo->query("use ".$database);
		}

		function disconnect ()
		{
			echo "!"; die;
			$this->pdo = null;
		}

		function query($query)
		{
            if(!parent::query($query))
				return false;

			try
			{
				$this->stmt = $this->pdo->query($query);
			}
			catch(PDOException $ex)
			{
				$this->dump_error($ex);
				return false;
			}

			return true;
		}

		function run_free()
		{
			$this->stmt = null;
		}

		function run_isanyresult()
		{			
			if($this->stmt->rowCount() < 1)
				return false;
			else
				return true;
		}

		function run_fetchrow()
		{
			$this->row = $this->stmt->fetch(PDO::FETCH_ASSOC);
			return $this->row;
		}

		function getfield($field)
		{
			return $this->row[$field];
		}

		function run_countrows()
		{
			return $this->stmt->rowCount();
		}
		
		function run_seek_row($numrow)
		{
			return $this->stmt->fetch(PDO::FETCH_ASSOC, PDO::FETCH_ORI_ABS, $numrow);
		}

		function getinsertid ()
		{
			return $this->pdo->lastInsertId();
		}
		
		function get_error($ex)
		{
			if(!$this->is_debug)
				return false;

            return
				"MySQL PDO Error: (".$ex->getMessage().")<br><br>".
				"Query: \"".$this->last_query."\"";
		}

		function dump_error($ex)
		{
			echo $this->get_error($ex);
		}
		
		function safestring($string)
        {
			$string = $this->pdo->quote($string);

			if(substr($string, 0, 1) == "'" && substr($string, -1, 1) == "'")
				return substr($string, 1, -1);
			else
				return $string;
        }
        
	}

?>
