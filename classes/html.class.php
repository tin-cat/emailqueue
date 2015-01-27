<?

	class html
	{

      var $title;
      var $menu_options;

      function head_simple()
      {
			return
			"
				<html>
				<header>
					<title>EMailqueue</title>
					<meta http-equiv=\"content-type\" content=\"text/html; charset=UTF-8\">
					<link rel=\"stylesheet\" type=\"text/css\" href=\"gfx/css/main.css\">
					<script language=javascript>
						function nwindow(mypage, w, h, scroll) {
							var myname = \"\";
							var winl = (screen.width - w) / 2;
							var wint = (screen.height - h) / 2;
							winprops = 'height='+h+',width='+w+',top='+wint+',left='+winl+',scrollbars='+scroll
							win = window.open(mypage, myname, winprops)
							if(parseInt(navigator.appVersion) >= 4)
								win.window.focus();
						}

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

		function head()
		{
			global $now;

			return
				$this->head_simple().
				"
					<div id=\"head\">
						<div class=\"logo\">Emailqueue</div>
						<div class=\"subtitle\">v".VERSION." / ".date("d.m.Y H'i\"s e", $now)." / <a href=\"".OFFICIAL_PAGE_URL."\" target=\"_newwindow\">official page</a></div>
					</div>
				";
		}

		function foot_simple()
		{
			return
				"
					</body>
					</html>
				";
		}

		function foot()
		{
			return
				$this->foot_simple();
		}
	}

?>
