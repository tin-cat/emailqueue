<html>
<head><title>EMailqueue - Test</title></head>
<body>
<h1>EMailqueue - Test</h1>
<hr>
<?php

	if (isset($_POST["action"]))
		$action = $_POST["action"];
	else
		$action = "form";

	switch ($action) {
		case "send":
			send();
			break;
		case "form":
			form();
			break;
	}

	function form() {
		echo "
			<form method=POST action=test.php>
				<input type=hidden name=action value=send>
				<p>
					<b>API Key</b>
					<input type=text name=key size=32>
				</p>
				<p>
					<b>To email address</b>
					<input type=text name=to size=60>
				</p>
				<p>
					<b>From email address</b>
					<input type=text name=from size=60 value=\"me@domain.com\">
				</p>
				<p>
					<b>Subject</b>
					<input type=text name=subject size=80 value=\"Test subject\">
				</p>
				<p>
					<b>Message</b><br>
					<textarea name=message cols=80 rows=10>Test message</textarea>
				</p>
				<p>
					<input type=submit value=\"Send\" />
				</p>
			</form>
		";
	}

	function send() {
		if (!$key = $_POST["key"]) {
			echo "API key not specified";
			return;
		}
		if (!$to = $_POST["to"]) {
			echo "To email address not specified";
			return;
		}
		if (!$from = $_POST["from"]) {
			echo "From email address not specified";
			return;
		}
		if (!$subject = $_POST["subject"]) {
			echo "Subject not specified";
			return;
		}
		if (!$message = $_POST["message"]) {
			echo "Message not specified";
			return;
		}

		$result = emailqueueApiCall(
			"http://127.0.0.1/api/",
			$key,
			[
				[
					"from" => $from,
					"to" => $to,
					"subject" => $subject,
					"content" => $message
				]
			]
		);

		if ($result["result"])
			echo "Email queued into Emailqueue succesfully";
		else
			echo "Error queueing message into Emailqueue: ".$result["description"];
	}

    function emailqueueApiCall($endpoint, $key, $messages = false) {
		$curl = curl_init();

		$request = [
			"key" => $key,
			"messages" => $messages
		];

		curl_setopt($curl, CURLOPT_URL, $endpoint);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, ["q" => json_encode($request)]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		curl_close($curl);
		return json_decode($result, true);
	}

?>
</body>
</html>