<html>
<head><title>EMailqueue - PHP Inject class test</title></head>
<body>
EMailqueue - PHP Inject class test
<hr>
<?php

    include_once "config/application.config.inc.php"; // Includes emailqueue configuration
    include_once "config/db.config.inc.php"; // Includes database connection configuration
    include_once "scripts/emailqueue_inject.class.php"; // Includes emailqueue_inject class
    
    $emailqueue_inject = new Emailqueue\emailqueue_inject(EMAILQUEUE_DB_HOST, EMAILQUEUE_DB_UID, EMAILQUEUE_DB_PWD, EMAILQUEUE_DB_DATABASE); // Creates an emailqueue_inject object. Needs the database connection information.
    
    $result = $emailqueue_inject->inject([
        "foreign_id_a" => false,
        "foreign_id_b" => false,
        "priority" => 10,
        "is_immediate" => true,
        "date_queued" => false,
        "is_html" => true,
        "from" => "from@email.com",
        "from_name" => "From name",
        "to" => "lorenzo@tin.cat",
        "replyto" => "replyto@email.com",
        "replyto_name" => "Replyto email",
        "subject" => "Test email from emailqueue",
        "content" => "<html><body>Test <b><i>HTML</i></b> message.",
        "content_nonhtml" => false,
        "list_unsubscribe_url" => false,
        "attachments" => [
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
        "is_embed_images" => true,
        "custom_headers" => false,
        "is_send_now" => false
    ]);
    
    if($result)
        echo "Message correctly injected.<br>";
    else
        echo "Error while queing message.<br>";

?>
</body>
</html>