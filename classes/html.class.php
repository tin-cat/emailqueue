<?php

class html {

	var $title;
	var $menu_options;

	function head_simple() {
		return
			"
				<html>
				<header>
					<title>EMailqueue</title>
					<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">
					<link rel=\"stylesheet\" type=\"text/css\" href=\"gfx/css/main.css\">
					<link rel=\"shortcut icon\" href=\"favicon.ico\" type=\"image/x-icon\">
					<link rel=\"icon\" href=\"favicon.ico\" type=\"image/x-icon\">
					<script>
						function confirmation(message, urlifok) {
							var result=confirm(message);
							if(result)
								document.location = urlifok;
						}
					</script>
				</header>
				<body>
			";
	}

	function head() {
		global $now;

		return
			$this->head_simple().
			"
				<div id=\"head\">
				<img src=\"gfx/img/logo_small.png\" class=\"logo\" title=\"Emailqueue\" />
				<div class=\"title\">Emailqueue v".VERSION."<br>".date("d.m.Y H'i\"s e", $now)."<br><a href=\"".OFFICIAL_PAGE_URL."\" target=\"_newwindow\">official page</a></div>
				</div>
			";
	}

	function foot_simple() {
		return
		"
			</body>
			</html>
		";
	}

	function foot() {
		return $this->foot_simple();
	}
}