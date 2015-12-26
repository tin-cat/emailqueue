<html>
<head><title>EMailqueue - PHP Inject class test</title></head>
<body>
EMailqeue - PHP Inject class test
<hr>
<?

    define("EMAILQUEUE_DIR", "./"); // Setup where emailqueue resides

    include EMAILQUEUE_DIR."config/application.config.inc.php"; // Includes emailqueue configuration
    include EMAILQUEUE_DIR."config/db.config.inc.php"; // Includes database connection configuration
    include EMAILQUEUE_DIR."scripts/emailqueue_inject.class.php"; // Includes emailqueue_inject class
    
    $emailqueue_inject = new emailqueue_inject(DB_HOST, DB_UID, DB_PWD, DB_DATABASE); // Creates an emailqueue_inject object. Needs the database connection information.
    
    $result = $emailqueue_inject->inject( // Injects an email to the queue
        null, // foreign_id_a
        null, // foreign_id_b
        null, // priority
        true, // is_inmediate
        null, // date_queued
        true, // is_html
        "from@email.com", // from
        "From name", // from_name
        "lorenzo@litmind.com", // to
        "replyto@email.com", // replyto
        "Reply to name", // replyto_name
        "Test subject", // subject
        "<html><body>Test <b><i>HTML</i></b> message. Here's an image embedded in the HTML itself: <img src=\"http://icons.iconarchive.com/icons/graphicloads/100-flat-2/256/email-icon.png\"></body></html>", // content
        false, // content_non_html: Optional, the content to show when the user is viewing the email with a client not capable of HTML (quite rare nowadays). If set to false, the given content HTML (above) will be automatically converted to a non-HTML version.
        false, // list_unsubscribe_url: Optional, URL where users can unsubscribe from the newletter (if it's a newletter). Use it to improve mailbox placement.
        // An optional array of files to be attached. Each file must be in turn a hash array specifying at least the "path" key.
        // Nice idea: Always attach an VCF contact card so users can simply click on it to add you as a contact, thus causing the email client to always consider your messages as no-spam.
        array(
            array(
                "path" => __DIR__."/frontend/gfx/img/logo_small.png", // Required. PHP must have permissions enough to read this file.
                "fileName" => "logo_small.png", // Optional. Emailqueue will extract the filename from the path if not specified.
                "encoding" => "base64", // Optional. Defaults to "base64"
                "type" => "image/png" // Optional. Emailqueue will try to determine the type
            ),
            array(
                "path" => __DIR__."/frontend/gfx/img/item.gif"
            )
        ),
        true // is_embed_images: Whether to convert any images found on the given HMTL to attachments, so the email is completely self-contained, containing all the images it needs to be rendered correctly. Might cause some email clients to always show the images on a message instead of giving the user the option to download them. Beware: Activating this option will make your emails a lot bigger, increasing the bandwidth usage dramatically.
    );
    
    if($result)
        echo "Message correctly queued.<br>";
    else
        echo "Error while queing message.<br>";

?>
</body>
</html>