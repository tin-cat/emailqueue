<?php

	/*
		Basic version with minimum functionalities
	*/

	namespace Emailqueue;

	class dbsource
	{
		var $type;
		var $connectionid;
		var $result;
		var $row;

		var $is_debug = true;

		function dbsource($type)
		{
			$this->type	= $type;
		}
		
		function query($query)
		{
			$this->is_cached = false;
			$this->last_query = $query;
			return true;
		}
		
		function isanyresult()
		{
			if(!$this->is_cached)
				return $this->run_isanyresult();
			else
			{
				if(is_array($this->rows))
					return true;
				else
					return false;
			}
		}
		
		function countrows()
		{
			if(!$this->is_cached)
				return $this->run_countrows();
			else
				return count($this->rows);
		}
		
		function seek_row($numrow)
		{
			if(!$this->is_cached)
				return $this->run_seek_row($numrow);				
			else
			{
				if(is_array($this->rows))
				{
					// Ugly and slow way to seek a position into an array when the results are cached!
					reset($this->rows);
					for($i=0; $i<$numrow; $i++)
						next($this->rows);
				}
			}
		}
		
		function fetchrow()
		{
			if(!$this->is_cached)
				return $this->run_fetchrow();
			else
			{
				if(is_array($this->rows))
				{
					$this->row = current($this->rows);
					next($this->rows);
					return $this->row;
				}
				else
					return false;
			}		
		}
		
		function free()
		{
			if(!$this->is_cached)
				return $this->run_free();
			else
				unset($this->rows);
		}

	}

?>