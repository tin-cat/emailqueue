<?php

class dbsource_oracle extends dbsource
{
	var $uid;
	var $pwd;
	var $schema;
	
	var $statement;
	var $row;
	var $errors;
	
	function dbsource_oracle ($uid, $pwd, $schema)
	{
		$this->dbsource ("oracle");
		$this->uid		= $uid;
		$this->pwd		= $pwd;
		$this->schema	= $schema;
	}
	
	function connect ()
	{
		$this->connectionid = OCILogon($this->uid, $this->pwd, $this->schema);
		return $this->connectionid;
	}
	
	function disconnect ()
	{
		return OCILogOff ($this->connectionid);
	}
	
	function query ($sql)
	{
		$this->statement = @OCIParse ($this->connectionid, $sql);
		@OCIExecute ($this->statement, OCI_DEFAULT);
		$this->errors = OCIError($this->statement);
	}
	
	function checkerrors ()
	{
		if ($this->errors["code"])
			return "[Código ".$this->errors["code"]."] ".$this->errors["message"];
		return false;
	}
	
	function fetchrow ()
	{
		OCIFetchInto($this->statement, &$results, OCI_ASSOC+OCI_RETURN_NULLS);
		$this->row = $results;
		return $results;
	}
	
	function getfield ($field)
	{
		return $this->row[$field];
	}
	
	function countrows ()
	{
		return OCIRowCount ($this->statement);
	}
	
	function getfield_date ($string)
	{
		return strtotime ($this->row[$string]);
	}
}