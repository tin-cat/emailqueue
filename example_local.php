<html>
<head><title>EMailqueue - PHP Inject class test</title></head>
<body>
<h1>EMailqueue - PHP Inject class test</h1>
<h2>An example on how to use the emailqueue_inject PHP class to inject email messages into Emailqueue when the Emailqueue installation is accessible in the same server as your code.</h2>
<hr>
<?php

	define("EMAILQUEUE_DIR", "./"); // Set this to your Emailqueue's installation directory.

    include_once EMAILQUEUE_DIR."config/application.config.inc.php"; // Include emailqueue configuration.
    include_once EMAILQUEUE_DIR."config/db.config.inc.php"; // Include Emailqueue's database connection configuration.
    include_once EMAILQUEUE_DIR."scripts/emailqueue_inject.class.php"; // Include Emailqueue's emailqueue_inject class.
    
    $emailqueue_inject = new Emailqueue\emailqueue_inject(EMAILQUEUE_DB_HOST, EMAILQUEUE_DB_UID, EMAILQUEUE_DB_PWD, EMAILQUEUE_DB_DATABASE); // Creates an emailqueue_inject object. Needs the database connection information.
    
	// Use a try ... catch statement to capture errors
	try {
		// Call the emailqueue_inject::inject method to inject an email
		$result = $emailqueue_inject->inject([
			"foreign_id_a" => false, // Optional, an id number for your internal records. e.g. Your internal id of the user who has sent this email.
			"foreign_id_b" => false, // Optional, a secondary id number for your internal records.
			"priority" => 10, // The priority of this email in relation to others: The lower the priority, the sooner it will be sent. e.g. An email with priority 10 will be sent first even if one thousand emails with priority 11 have been injected before. Defaults to 10
			"is_immediate" => true, // Set it to true to queue this email to be delivered as soon as possible. (doesn't overrides priority setting). Defaults to true.
			"is_send_now" => false, // Set it to true to make this email be sent right now, without waiting for the next delivery call. This effectively gets rid of the queueing capabilities of emailqueue and can delay the execution of your script a little while the SMTP connection is done. Use it in those cases where you don't want your users to wait not even a minute to receive your message. Defaults to false.
			"date_queued" => false, // If specified, this message will be sent only when the given timestamp has been reached. Leave it to false to send the message as soon as possible. (doesn't overrides priority setting)
			"is_html" => true, // Whether the given "content" parameter contains HTML or not. Defaults to true.	
			"from" => "from@email.com", // The sender email address
			"from_name" => "From name", // The sender name
			"to" => "lorenzo@tin.cat", // The addressee email address
			"replyto" => "replyto@email.com", // The email address where replies to this message will be sent by default
			"replyto_name" => "Replyto email", // The name where replies to this message will be sent by default
			"subject" => "Test email from emailqueue", // The email subject
			"content" => "<html><body>Test <b><i>HTML</i></b> message with some emoji 🚀", // The email content. Can contain HTML (set is_html parameter to true if so).
			"content_nonhtml" => false, // The plain text-only content for clients not supporting HTML emails (quite rare nowadays). If set to false, a text-only version of the given content will be automatically generated.
			"list_unsubscribe_url" => false, // Optional. Specify the URL where users can unsubscribe from your mailing list. Some email clients will show this URL as an option to the user, and it's likely to be considered by many SPAM filters as a good signal, so it's really recommended.
			"attachments" => [ // Optional. An array of hash arrays specifying the files you want to attach to your email. See example.php for an specific description on how to build this array.
				[
					"path" => __DIR__."/frontend/gfx/img/logo_small.png", // Required. PHP must have permissions enough to read this file.
					"fileName" => "logo_small.png", // Optional. Emailqueue will extract the filename from the path if not specified.
					"encoding" => "base64", // Optional. Defaults to "base64"
					"type" => "image/png" // Optional. Emailqueue will try to determine the type
				],
				[
					"path" => __DIR__."/frontend/gfx/img/item.gif"
				]
			],
			"is_embed_images" => true, // When set to true, Emailqueue will find all the <img ... /> tags in your provided HTML code on the "content" parameter and convert them into embedded images that are attached to the email itself instead of being referenced by URL. This might cause email clients to show the email straightaway without the user having to accept manually to load the images. Setting this option to true will greatly increase the bandwidth usage of your SMTP server, since each message will contain hard copies of all embedded messages. 10k emails with 300Kbs worth of images each means around 3Gb. of data to be transferred!
			"custom_headers" => false // Optional. A hash array of additional headers where each key is the header name and each value is its value.
		]);
	} catch (Exception $e) {
		echo "Emailqueue error: ".$e->getMessage()."<br>";
	}
    
    if($result)
        echo "Message correctly injected.<br>";
    else
        echo "Error while queing message.<br>";

?>
</body>
</html>