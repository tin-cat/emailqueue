<html>
<head><title>EMailqueue - API example</title></head>
<body>
<h1>EMailqueue - API example</h1>
<h2>An example on how inject emails to Emailqueue by calling Emailqueue's API. This is the method to use when Emailqueue is installed in another server, or in a dockerized environment.</h2>
<hr>
<?php

	$result = emailqueueApiCall(
		"127.0.0.1:8081/api",
		"asfKkj·3=m2345k",
		[
			"from" => "me@domain.com",
			"to" => "him@domain.com",
			"subject" => "Just testing",
			"content" => "This is just an email to test Emailqueue"
		]
	);

	echo "<b>Api call result:</b><br><pre>".print_r($result, true)."</pre>";

    function emailqueueApiCall($endpoint, $key, $message = false) {
		$curl = curl_init();

		$request = ["key" => $key];

		if (is_array($message))
			$request["messages"] = $message;
		else
			$request["message"] = $message;

		curl_setopt($curl, CURLOPT_URL, $endpoint);
		curl_setopt($curl, CURLOPT_POST, 1);
		curl_setopt($curl, CURLOPT_POSTFIELDS, ["q" => $request]);
		curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
		$result = curl_exec($curl);
		curl_close($curl);
		return $result;
	}

?>
</body>
</html>