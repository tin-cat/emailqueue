<html>
<head><title>EMailqueue - API example</title></head>
<body>
<h1>EMailqueue - API example</h1>
<h2>An example on how inject emails to Emailqueue by calling Emailqueue's API. This is the method to use when Emailqueue is installed in another server, or in a dockerized environment.</h2>
<hr>
<?php

	$result = emailqueueApiCall(
		"http://127.0.0.1/api/",
		"asfKkj3=m2345k",
		[
			[
				"from" => "me@domain.com",
				"to" => "him@domain.com",
				"subject" => "Just testing",
				"content" => "This is just an email to test Emailqueue"
			]
		]
	);

	echo "<b>Api call result:</b><br><pre>".print_r($result, true)."</pre>";

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