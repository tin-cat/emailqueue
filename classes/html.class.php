<?php

	namespace Emailqueue;

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
						<!-- <script src=\"https://code.jquery.com/jquery-3.4.1.slim.min.js\" integrity=\"sha256-pasqAKBDmFT4eHoN2ndd6lN370kFiGUFyTiUHWhU7k8=\" crossorigin=\"anonymous\"></script> -->
					</header>
					<body>
				";
		}

		function head() {
			return
				$this->head_simple().
				"
					<div id=\"head\">
						<img src=\"gfx/img/logo_small.png\" class=\"logo\" title=\"Emailqueue\" />
						<div class=\"title\">
							<div>Emailqueue v".VERSION." by <a href=\"https://tin.cat\" target=\"_newwindow\">Tin.cat</a> / <a href=\"".OFFICIAL_PAGE_URL."\" target=\"_newwindow\">Github</a></div>
							<div>Server time <span id=\"serverTime\"></span></div>
							<div><span id=\"serverRemainingSeconds\"></span> seconds until delivery</div>
						</div>
					</div>
					<script>
						var serverTimestamp = ".time().";
						var startTimestamp = Math.round((new Date()).getTime() / 1000);
						function updateClock() {
							var nowTimestamp = Math.round((new Date()).getTime() / 1000);
							var elapsedSeconds = nowTimestamp - startTimestamp;
							var serverNowTimestamp = serverTimestamp + elapsedSeconds;

							var serverDateNow = new Date(serverNowTimestamp * 1000);
							var serverSeconds = ('0' + serverDateNow.getSeconds()).substr(-2);
							var serverRemainingSeconds = 60 - serverSeconds;

							document.getElementById('serverRemainingSeconds').innerHTML = serverRemainingSeconds;

							var formattedTime = serverDateNow.getHours() + ':' + ('0' + serverDateNow.getMinutes()).substr(-2) + ':' + ('0' + serverDateNow.getSeconds()).substr(-2);
							document.getElementById('serverTime').innerHTML = formattedTime;

							setTimeout(updateClock, 1000);
						}
						updateClock();
					</script>
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

?>