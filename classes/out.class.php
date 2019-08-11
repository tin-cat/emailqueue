<?php

	namespace Emailqueue;

	class output {
		var $data;

		function add($data) {
			$this->data .= $data;
		}

		function add_tobeggining($data) {
			$this->data = $data.$this->data;
		}

		function clear() {
			$this->data = "";
		}

		function dump() {
			echo $this->data;
		}

	}

?>